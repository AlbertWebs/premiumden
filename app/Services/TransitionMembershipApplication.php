<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Mail\ApplicantDocumentsRequested;
use App\Mail\ApplicantInvoiceIssued;
use App\Mail\ApplicantApplicationUpdate;
use App\Models\ApplicationStatusHistory;
use App\Models\MembershipApplication;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TransitionMembershipApplication
{
    private const RISK_STAGES = [ApplicationStatus::Submitted, ApplicationStatus::UnderReview, ApplicationStatus::Vetting];

    private const MEMBERSHIP_STAGES = [ApplicationStatus::Approved, ApplicationStatus::DocumentsRequired, ApplicationStatus::DocumentsReceived, ApplicationStatus::InvoiceIssued, ApplicationStatus::AwaitingPayment];

    private const TRANSITIONS = [
        'submitted' => ['under_review', 'cancelled'],
        'under_review' => ['vetting', 'rejected', 'cancelled'],
        'vetting' => ['approved', 'rejected', 'cancelled'],
        'approved' => ['documents_required', 'cancelled'],
        'documents_required' => ['documents_received', 'cancelled'],
        'documents_received' => ['invoice_issued', 'cancelled'],
        'invoice_issued' => ['awaiting_payment', 'cancelled'],
        'awaiting_payment' => ['cancelled'],
        'membership_activated' => ['suspended'],
        'suspended' => ['membership_activated', 'cancelled'],
    ];

    public function availableFor(MembershipApplication $application, User $actor): array
    {
        $isSuperAdmin = $actor->hasRole('super_administrator');
        $canReviewRisk = $isSuperAdmin || $actor->hasRole('risk_team');
        $canManageMembership = $isSuperAdmin || $actor->hasRole('membership_administrator');
        $current = $application->status;

        if (! $current || (in_array($current, self::RISK_STAGES, true) && ! $canReviewRisk)
            || (in_array($current, self::MEMBERSHIP_STAGES, true) && ! $canManageMembership)) {
            return [];
        }

        if (in_array($current, [ApplicationStatus::MembershipActivated, ApplicationStatus::Suspended], true) && ! $canManageMembership && ! $isSuperAdmin) {
            return [];
        }

        $allowed = self::TRANSITIONS[$current->value] ?? [];

        if ($current === ApplicationStatus::DocumentsRequired && ! $this->documentsAreApproved($application)) {
            $allowed = array_values(array_filter($allowed, fn (string $value) => $value !== ApplicationStatus::DocumentsReceived->value));
        }

        return array_values(array_filter(ApplicationStatus::cases(), fn (ApplicationStatus $status) => in_array($status->value, $allowed, true)));
    }

    public function handle(MembershipApplication $application, User $actor, ApplicationStatus $next, ?string $note = null): void
    {
        $uploadUrl = null;
        $expiresAt = null;
        $invoiceIdToSend = null;

        DB::transaction(function () use ($application, $actor, $next, $note, &$uploadUrl, &$expiresAt, &$invoiceIdToSend): void {
            $locked = MembershipApplication::query()->lockForUpdate()->findOrFail($application->id);
            $current = $locked->status;
            $this->authorizeTransition($locked, $actor, $next);

            if ($next === ApplicationStatus::InvoiceIssued) {
                $locked->loadMissing('package');
                if ($locked->package->price === null) {
                    throw ValidationException::withMessages(['status' => 'Set a membership package price before issuing an invoice.']);
                }
                $invoice = Invoice::firstOrCreate(['membership_application_id' => $locked->id], [
                    'reference' => 'PBD-INV-'.strtoupper(Str::random(12)),
                    'status' => 'pending', 'currency' => 'KES', 'subtotal' => $locked->package->price,
                    'total' => $locked->package->price, 'issued_at' => now(),
                    'due_at' => now()->addDays((int) config('billing.invoice_due_days', 14)),
                ]);
                if ($invoice->wasRecentlyCreated) {
                    $invoice->items()->create(['description' => $locked->package->name.' membership · '.$locked->package->renewal_months.' months', 'quantity' => 1, 'unit_amount' => $locked->package->price, 'amount' => $locked->package->price]);
                    app(RecordAuditEvent::class)->handle('invoice.issued', $invoice, ['total' => $invoice->total, 'currency' => $invoice->currency], $actor->id);
                }
                $invoiceIdToSend = $invoice->id;
            }

            if ($next === ApplicationStatus::AwaitingPayment) {
                $provider = (string) config('services.payment.driver', 'disabled');
                if ($provider === '' || $provider === 'disabled') {
                    throw ValidationException::withMessages(['status' => 'Configure a payment gateway before opening payment collection.']);
                }
                $invoice = $locked->invoice()->firstOrFail();
                Payment::firstOrCreate(['invoice_id' => $invoice->id, 'provider' => $provider], [
                    'provider_reference' => 'PBD-PAY-'.strtoupper(Str::random(18)),
                    'status' => 'pending', 'currency' => $invoice->currency, 'amount' => $invoice->total,
                ]);
                $invoice->update(['status' => 'awaiting_payment']);
            }

            if ($next === ApplicationStatus::Cancelled) {
                $locked->invoice()->update(['status' => 'cancelled']);
                $locked->invoice?->payments()->update(['status' => 'cancelled']);
            }

            if (in_array($next, [ApplicationStatus::Suspended, ApplicationStatus::MembershipActivated], true)) {
                $membership = $locked->membership;
                if ($membership) {
                    $active = $next === ApplicationStatus::MembershipActivated;
                    $membership->update(['status' => $active ? 'active' : 'suspended']);
                    $membership->user()->update(['membership_active' => $active]);
                    $membership->card()->update(['status' => $active ? 'active' : 'suspended']);
                    app(RecordAuditEvent::class)->handle($active ? 'membership.reactivated' : 'membership.suspended', $membership, ['status' => $active ? 'active' : 'suspended'], $actor->id);
                    if ($card = $membership->card()->first()) app(RecordAuditEvent::class)->handle('member_card.status_changed', $card, ['status' => $active ? 'active' : 'suspended'], $actor->id);
                }
            }

            $updates = ['status' => $next];
            if ($next === ApplicationStatus::DocumentsRequired) {
                $token = Str::random(64);
                $expiresAt = now()->addDays(max(1, config('membership.document_upload_validity_days', 14)));
                $updates['document_upload_token_hash'] = hash('sha256', $token);
                $updates['document_upload_expires_at'] = $expiresAt;
                $uploadUrl = URL::temporarySignedRoute('application.documents.create', $expiresAt, ['reference' => $locked->reference, 'token' => $token]);
            }
            $locked->update($updates);
            ApplicationStatusHistory::create([
                'membership_application_id' => $locked->id,
                'from_status' => $current,
                'to_status' => $next,
                'actor_id' => $actor->id,
                'note' => $note ? 'Application status updated.' : null,
                'occurred_at' => now(),
            ]);
            app(RecordAuditEvent::class)->handle('application.status_changed', $locked, [
                'from' => $current?->value, 'to' => $next->value,
            ], $actor->id);

            if (filled($note)) {
                $locked->internalNotes()->create(['user_id' => $actor->id, 'body' => $note]);
            }
        });

        if ($uploadUrl && $expiresAt) {
            $this->sendDocumentRequest($application, $uploadUrl, $expiresAt);
        }
        if ($invoiceIdToSend) {
            $invoice = Invoice::with(['application.package', 'items', 'payments'])->findOrFail($invoiceIdToSend);
            try {
                $invoiceUrl = URL::temporarySignedRoute('application.invoice.show', now()->addDays(30), ['reference' => $invoice->reference]);
                Mail::to($invoice->application->email)->send(new ApplicantInvoiceIssued($invoice, $invoiceUrl));
            } catch (\Throwable $exception) {
                Log::error('Unable to send the applicant invoice notice.', ['invoice_id' => $invoice->id, 'exception' => $exception::class]);
            }
        }
        if (! in_array($next, [ApplicationStatus::DocumentsRequired, ApplicationStatus::InvoiceIssued], true)) {
            $application->refresh();
            $message = match ($next) {
                ApplicationStatus::Approved => 'Your application has been approved. The membership team will contact you with the secure document submission step.',
                ApplicationStatus::Rejected => 'We are unable to proceed with your membership application at this time. Thank you for your interest in Premium Business Den.',
                ApplicationStatus::Cancelled => 'Your membership application has been cancelled. Contact the membership team if you believe this is an error.',
                ApplicationStatus::Suspended => 'Your membership access has been suspended. Please contact the membership team for assistance.',
                ApplicationStatus::UnderReview, ApplicationStatus::Vetting => 'Your application is being reviewed. We will contact you if we need further information.',
                ApplicationStatus::DocumentsReceived => 'Your documents have been received and are being reviewed.',
                ApplicationStatus::AwaitingPayment => 'Your application is ready for payment. The invoice contains the payment instructions.',
                default => 'Your application status has been updated.',
            };
            try {
                Mail::to($application->email)->send(new ApplicantApplicationUpdate($application, $message, URL::temporarySignedRoute('application.status', now()->addDays(90), ['reference' => $application->reference])));
            } catch (\Throwable $exception) {
                Log::error('Unable to send the applicant status update.', ['application_id' => $application->id, 'exception' => $exception::class]);
            }
        }
    }

    public function resendDocumentRequest(MembershipApplication $application, User $actor): void
    {
        if (! $actor->hasRole('super_administrator') && ! $actor->hasRole('membership_administrator')) {
            throw new AuthorizationException('Only membership administrators can reissue secure document links.');
        }

        $expiresAt = null;
        $uploadUrl = null;
        DB::transaction(function () use ($application, $actor, &$expiresAt, &$uploadUrl): void {
            $locked = MembershipApplication::query()->lockForUpdate()->findOrFail($application->id);
            if ($locked->status !== ApplicationStatus::DocumentsRequired) {
                throw ValidationException::withMessages(['status' => 'A secure upload link is only available while documents are required.']);
            }

            $token = Str::random(64);
            $expiresAt = now()->addDays(max(1, config('membership.document_upload_validity_days', 14)));
            $locked->update(['document_upload_token_hash' => hash('sha256', $token), 'document_upload_expires_at' => $expiresAt]);
            $uploadUrl = URL::temporarySignedRoute('application.documents.create', $expiresAt, ['reference' => $locked->reference, 'token' => $token]);
            $locked->internalNotes()->create(['user_id' => $actor->id, 'body' => 'A new time-limited document upload link was issued to the applicant.']);
        });

        if ($uploadUrl && $expiresAt) {
            $this->sendDocumentRequest($application, $uploadUrl, $expiresAt);
        }
    }

    private function sendDocumentRequest(MembershipApplication $application, string $uploadUrl, \Illuminate\Support\Carbon $expiresAt): void
    {
        $application->load('package');
        try {
            Mail::to($application->email)->send(new ApplicantDocumentsRequested($application, $uploadUrl, $expiresAt->format('j F Y, H:i T')));
        } catch (\Throwable $exception) {
            Log::error('Unable to send the applicant document request.', ['application_id' => $application->id, 'exception' => $exception::class]);
        }
    }

    private function authorizeTransition(MembershipApplication $application, User $actor, ApplicationStatus $next): void
    {
        $current = $application->status;
        if (! $current || ! in_array($next->value, self::TRANSITIONS[$current->value] ?? [], true)) {
            throw ValidationException::withMessages(['status' => 'That application status change is not allowed.']);
        }

        $isSuperAdmin = $actor->hasRole('super_administrator');
        if (in_array($current, self::RISK_STAGES, true) && ! $isSuperAdmin && ! $actor->hasRole('risk_team')) {
            throw new AuthorizationException('Only the Risk Team can manage application review.');
        }

        if ((in_array($current, self::MEMBERSHIP_STAGES, true) || in_array($current, [ApplicationStatus::MembershipActivated, ApplicationStatus::Suspended], true))
            && ! $isSuperAdmin && ! $actor->hasRole('membership_administrator')) {
            throw new AuthorizationException('Only membership administrators can manage this application stage.');
        }

        if ($current === ApplicationStatus::DocumentsRequired && $next === ApplicationStatus::DocumentsReceived && ! $this->documentsAreApproved($application)) {
            throw ValidationException::withMessages(['status' => 'Both required documents must be approved before this application can proceed.']);
        }
    }

    private function documentsAreApproved(MembershipApplication $application): bool
    {
        return $application->documents()->whereIn('document_type', ['photo', 'identity'])->where('status', 'approved')->count() === 2;
    }
}
