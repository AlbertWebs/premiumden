<?php

namespace App\Http\Controllers\Admin;

use App\Mail\ApplicantInvoiceIssued;
use App\Models\Invoice;
use App\Models\Membership;
use App\Models\Payment;
use App\Services\RecordAuditEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class MembershipRenewalController
{
    public function store(Request $request, Membership $membership, RecordAuditEvent $audit): RedirectResponse
    {
        $membership->load(['user', 'package', 'application']);
        abort_unless($membership->user->hasRole('member'), 404);
        if (! $membership->package->price || ! $membership->package->is_active) {
            throw ValidationException::withMessages(['membership' => 'Set an active package price before issuing a renewal invoice.']);
        }
        $provider = (string) config('services.payment.driver', 'disabled');
        if ($provider === '' || $provider === 'disabled') {
            throw ValidationException::withMessages(['membership' => 'Configure a payment gateway before issuing a renewal invoice.']);
        }
        if (Invoice::where('membership_id', $membership->id)->where('kind', 'renewal')->whereHas('payments', fn ($query) => $query->whereIn('status', ['pending', 'processing']))->exists()) {
            throw ValidationException::withMessages(['membership' => 'A renewal invoice is already awaiting payment.']);
        }
        $invoice = DB::transaction(function () use ($membership, $provider): Invoice {
            $locked = Membership::query()->lockForUpdate()->findOrFail($membership->id);
            if (Invoice::where('membership_id', $locked->id)->where('kind', 'renewal')->whereHas('payments', fn ($query) => $query->whereIn('status', ['pending', 'processing']))->exists()) {
                throw ValidationException::withMessages(['membership' => 'A renewal invoice is already awaiting payment.']);
            }
            Invoice::where('membership_id', $locked->id)->where('kind', 'renewal')->where('status', 'awaiting_payment')
                ->whereHas('payments', fn ($query) => $query->whereIn('status', ['failed', 'cancelled']))->update(['status' => 'cancelled']);
            $invoice = Invoice::create([
                'membership_application_id' => $locked->membership_application_id,
                'membership_id' => $locked->id, 'kind' => 'renewal',
                'reference' => 'PBD-INV-'.Str::upper(Str::random(12)), 'status' => 'awaiting_payment',
                'currency' => 'KES', 'subtotal' => $locked->package->price, 'total' => $locked->package->price,
                'issued_at' => now(), 'due_at' => now()->addDays((int) config('billing.invoice_due_days', 14)),
            ]);
            $invoice->items()->create(['description' => $locked->package->name.' membership renewal · '.$locked->package->renewal_months.' months', 'quantity' => 1, 'unit_amount' => $invoice->total, 'amount' => $invoice->total]);
            Payment::create([
                'invoice_id' => $invoice->id, 'provider' => $provider,
                'provider_reference' => 'PBD-PAY-'.Str::upper(Str::random(18)), 'status' => 'pending',
                'currency' => $invoice->currency, 'amount' => $invoice->total,
            ]);
            return $invoice;
        });
        $audit->handle('membership.renewal_invoice_issued', $invoice, ['membership_id' => $membership->id, 'total' => $invoice->total, 'currency' => $invoice->currency], $request->user()->id);
        try {
            $url = URL::temporarySignedRoute('application.invoice.show', now()->addDays(30), ['reference' => $invoice->reference]);
            Mail::to($membership->user->email)->send(new ApplicantInvoiceIssued($invoice->load(['application.package', 'items', 'payments']), $url));
        } catch (\Throwable $exception) {
            Log::error('Unable to send the membership renewal invoice.', ['membership_id' => $membership->id, 'exception' => $exception::class]);
        }
        return back()->with('status', 'Renewal invoice created and sent to the member.');
    }
}
