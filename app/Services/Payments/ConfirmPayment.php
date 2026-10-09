<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Mail\MemberFirstAccess;
use App\Mail\MemberWelcome;
use App\Mail\MemberPaymentConfirmed;
use App\Models\Membership;
use App\Models\MembershipApplication;
use App\Models\MembershipCard;
use App\Models\ApplicationStatusHistory;
use App\Models\Payment;
use App\Models\PaymentAttempt;
use App\Models\User;
use App\Models\WelcomePackage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ConfirmPayment
{
    public function handle(PaymentGatewayInterface $gateway, array $event): void
    {
        validator($event, [
            'event_id' => ['required', 'string', 'max:190'],
            'payment_reference' => ['required', 'string', 'max:190'],
            'status' => ['required', 'in:paid,failed,cancelled,refunded,processing'],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'provider_reference' => ['nullable', 'string', 'max:190'],
        ])->validate();

        $firstAccess = null;
        $activatedMembershipId = null;
        $confirmedPaymentId = null;
        $amountMismatch = false;
        $closedInvoice = false;
        DB::transaction(function () use ($gateway, $event, &$firstAccess, &$activatedMembershipId, &$confirmedPaymentId, &$amountMismatch, &$closedInvoice): void {
            if (PaymentAttempt::where('provider_event_id', $event['event_id'])->exists()) {
                return;
            }

            $payment = Payment::with('invoice.application.package')
                ->where('provider', $gateway->name())
                ->where('provider_reference', $event['payment_reference'])
                ->lockForUpdate()->first();

            if (! $payment) {
                throw ValidationException::withMessages(['payment' => 'The payment reference is not recognized.']);
            }

            if (number_format((float) $payment->amount, 2, '.', '') !== number_format((float) $event['amount'], 2, '.', '') || $payment->currency !== strtoupper($event['currency'])) {
                PaymentAttempt::create(['payment_id' => $payment->id, 'provider_event_id' => $event['event_id'], 'status' => 'rejected', 'message' => 'Amount or currency mismatch.', 'payload' => $this->safePayload($event), 'received_at' => now()]);
                $amountMismatch = true;
                return;
            }

            PaymentAttempt::create([
                'payment_id' => $payment->id,
                'provider_event_id' => $event['event_id'],
                'status' => $event['status'],
                'message' => null,
                'payload' => $this->safePayload($event),
                'received_at' => now(),
            ]);

            if ($payment->status === 'paid' && $event['status'] === 'refunded') {
                $payment->update(['status' => 'refunded', 'metadata' => ['gateway_transaction_reference' => $event['provider_reference'] ?? null]]);
                $payment->invoice->update(['status' => 'refunded']);
                $application = $payment->invoice->application;
                $membership = $application->membership;
                if ($membership) {
                    $membership->update(['status' => 'suspended']);
                    $membership->user()->update(['membership_active' => false]);
                    $membership->card()->update(['status' => 'suspended']);
                    if ($card = $membership->card()->first()) app(\App\Services\RecordAuditEvent::class)->handle('member_card.status_changed', $card, ['status' => 'suspended']);
                }
                if ($application->status === ApplicationStatus::MembershipActivated) {
                    $application->update(['status' => ApplicationStatus::Suspended]);
                    ApplicationStatusHistory::create(['membership_application_id' => $application->id, 'from_status' => ApplicationStatus::MembershipActivated, 'to_status' => ApplicationStatus::Suspended, 'note' => 'Membership suspended after verified refund.', 'occurred_at' => now()]);
                }
                app(\App\Services\RecordAuditEvent::class)->handle('payment.refunded', $payment, ['amount' => $payment->amount, 'currency' => $payment->currency]);
                if ($membership) app(\App\Services\RecordAuditEvent::class)->handle('membership.suspended', $membership, ['reason' => 'verified_refund']);
                return;
            }

            if ($payment->status === 'paid') {
                return;
            }

            if ($event['status'] !== 'paid') {
                $payment->update(['status' => $event['status'], 'provider_reference' => $event['payment_reference']]);
                return;
            }

            if (in_array($payment->invoice->status, ['cancelled', 'refunded'], true)) {
                PaymentAttempt::where('provider_event_id', $event['event_id'])->update(['status' => 'rejected', 'message' => 'Invoice is no longer open for payment.']);
                $closedInvoice = true;
                return;
            }

            $application = $payment->invoice->application;
            if ($payment->invoice->kind !== 'renewal' && $application->status !== ApplicationStatus::AwaitingPayment) {
                throw ValidationException::withMessages(['payment' => 'This application is not awaiting payment.']);
            }

            $payment->update(['status' => 'paid', 'metadata' => ['gateway_transaction_reference' => $event['provider_reference'] ?? null], 'confirmed_at' => now()]);
            $confirmedPaymentId = $payment->id;
            $payment->invoice->update(['status' => 'paid']);
            if ($payment->invoice->kind === 'renewal') {
                $membership = $payment->invoice->membership ?? $application->membership;
                if (! $membership) throw ValidationException::withMessages(['payment' => 'The membership renewal record could not be found.']);
                $membership->loadMissing('package');
                $termBase = $membership->expires_at && ! $membership->expires_at->isBefore(today()) ? $membership->expires_at->copy() : today();
                $membership->update(['status' => 'active', 'expires_at' => $termBase->addMonths((int) $membership->package->renewal_months)]);
                $membership->user()->update(['membership_active' => true]);
                $membership->card()->update(['status' => 'active']);
                if ($application->status === ApplicationStatus::Suspended) {
                    $application->update(['status' => ApplicationStatus::MembershipActivated]);
                    ApplicationStatusHistory::create(['membership_application_id' => $application->id, 'from_status' => ApplicationStatus::Suspended, 'to_status' => ApplicationStatus::MembershipActivated, 'note' => 'Membership renewed after verified payment.', 'occurred_at' => now()]);
                    app(\App\Services\RecordAuditEvent::class)->handle('application.status_changed', $application, ['from' => 'suspended', 'to' => 'membership_activated']);
                }
                app(\App\Services\RecordAuditEvent::class)->handle('payment.confirmed', $payment, ['invoice_id' => $payment->invoice_id, 'amount' => $payment->amount, 'currency' => $payment->currency]);
                app(\App\Services\RecordAuditEvent::class)->handle('membership.renewed', $membership, ['expires_at' => $membership->fresh()->expires_at?->toDateString()]);
                if ($card = $membership->card()->first()) app(\App\Services\RecordAuditEvent::class)->handle('member_card.status_changed', $card, ['status' => 'active']);
                return;
            }
            $application->update(['status' => ApplicationStatus::Paid]);
            ApplicationStatusHistory::create(['membership_application_id' => $application->id, 'from_status' => ApplicationStatus::AwaitingPayment, 'to_status' => ApplicationStatus::Paid, 'note' => 'Payment verified by gateway.', 'occurred_at' => now()]);
            app(\App\Services\RecordAuditEvent::class)->handle('payment.confirmed', $payment, [
                'invoice_id' => $payment->invoice_id, 'amount' => $payment->amount, 'currency' => $payment->currency,
            ], null);

            $user = User::firstOrCreate(['email' => $application->email], [
                'name' => $application->full_name,
                'password' => Str::random(64),
                'role' => UserRole::Member,
                'membership_package_id' => $application->membership_package_id,
                'membership_active' => true,
                'email_verified_at' => now(),
            ]);

            if (! $user->hasRole(UserRole::Member) || $user->membership_package_id !== $application->membership_package_id) {
                throw ValidationException::withMessages(['payment' => 'An account already exists for this email and needs administrator review.']);
            }

            $user->forceFill(['membership_active' => true])->save();
            if ($user->wasRecentlyCreated) {
                $token = Str::random(64);
                $expiresAt = now()->addHours(48);
                $user->forceFill(['first_access_token_hash' => hash('sha256', $token), 'first_access_expires_at' => $expiresAt])->save();
                $firstAccess = [$user->id, URL::temporarySignedRoute('member.first-access.create', $expiresAt, ['email' => $user->email, 'token' => $token]), $expiresAt];
            }
            $user->profile()->firstOrCreate([], [
                'company' => $application->company,
                'job_title' => $application->job_title,
                'industry' => $application->industry,
                'location' => $application->location,
                'biography' => $application->biography,
                'is_listed' => true,
            ]);

            $membership = Membership::firstOrCreate(['membership_application_id' => $application->id], [
                'user_id' => $user->id,
                'membership_package_id' => $application->membership_package_id,
                'number' => app(\App\Services\GenerateMembershipNumber::class)->handle(),
                'status' => 'active',
                'started_at' => today(),
                'expires_at' => today()->addMonths((int) $application->package->renewal_months),
            ]);
            $activatedMembershipId = $membership->id;
            MembershipCard::firstOrCreate(['membership_id' => $membership->id], ['identifier' => (string) Str::uuid(), 'status' => 'active']);
            WelcomePackage::firstOrCreate(['membership_id' => $membership->id], ['status' => 'pending']);
            $application->update(['status' => ApplicationStatus::MembershipActivated]);
            ApplicationStatusHistory::create(['membership_application_id' => $application->id, 'from_status' => ApplicationStatus::Paid, 'to_status' => ApplicationStatus::MembershipActivated, 'note' => 'Membership activated after verified payment.', 'occurred_at' => now()]);
            app(\App\Services\RecordAuditEvent::class)->handle('membership.activated', $membership, [
                'membership_number' => $membership->number, 'package_id' => $membership->membership_package_id,
            ]);
            app(\App\Services\RecordAuditEvent::class)->handle('member_card.issued', $membership->card, ['status' => 'active']);
        });

        if ($amountMismatch) {
            throw ValidationException::withMessages(['payment' => 'The confirmed amount or currency does not match the invoice.']);
        }
        if ($closedInvoice) {
            throw ValidationException::withMessages(['payment' => 'This invoice is no longer open for payment.']);
        }

        if ($firstAccess) {
            [$userId, $setupUrl, $expiresAt] = $firstAccess;
            $member = User::with('membershipPackage')->find($userId);
            try {
                Mail::to($member->email)->send(new MemberFirstAccess($member, $setupUrl, $expiresAt->format('j F Y, H:i T')));
            } catch (\Throwable $exception) {
                Log::error('Unable to send the new member first-access link.', ['user_id' => $member->id, 'exception' => $exception::class]);
            }
        }
        if ($confirmedPaymentId) {
            $confirmedPayment = Payment::with('invoice.application.membership')->findOrFail($confirmedPaymentId);
            $recipientEmail = $confirmedPayment->invoice->membership?->user?->email ?? $confirmedPayment->invoice->application->email;
            try {
                Mail::to($recipientEmail)->send(new MemberPaymentConfirmed($confirmedPayment));
            } catch (\Throwable $exception) {
                Log::error('Unable to send the payment confirmation email.', ['payment_id' => $confirmedPaymentId, 'exception' => $exception::class]);
            }
            if ($activatedMembershipId) {
                $membership = Membership::with(['user', 'package', 'application', 'card'])->findOrFail($activatedMembershipId);
                try {
                    $supportEmail = \App\Models\SiteSetting::value('public_contact_email') ?: (string) config('services.contact.to_address', '');
                    Mail::to($membership->user->email)->send(new MemberWelcome($membership, route('member.dashboard'), $supportEmail));
                } catch (\Throwable $exception) {
                    Log::error('Unable to send the new member welcome email.', ['membership_id' => $membership->id, 'exception' => $exception::class]);
                }
            }
        }
    }

    private function safePayload(array $event): array
    {
        return collect($event)->only(['event_id', 'payment_reference', 'provider_reference', 'status', 'amount', 'currency'])->all();
    }
}
