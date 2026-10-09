<?php

namespace App\Http\Controllers\Public;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Invoice;
use App\Models\PaymentAttempt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ApplicantInvoicePaymentController
{
    public function __invoke(Request $request, string $reference, PaymentGatewayInterface $gateway): RedirectResponse
    {
        $validated = $request->validate(['phone' => ['required', 'string', 'max:40']]);
        $invoice = Invoice::with('payments')->where('reference', $reference)->firstOrFail();
        if (! in_array($invoice->status, ['pending', 'awaiting_payment'], true)) {
            throw ValidationException::withMessages(['payment' => 'This invoice is not open for payment.']);
        }
        $payment = $invoice->payments()->where('provider', $gateway->name())->whereIn('status', ['pending', 'processing', 'failed'])->latest()->first();
        if (! $payment) throw ValidationException::withMessages(['payment' => 'There is no open payment request for this invoice. Contact the membership team.']);
        if ($payment->status === 'failed') $payment->update(['status' => 'pending', 'metadata' => null]);

        try {
            $result = $gateway->initiate($payment, $validated['phone']);
        } catch (\Throwable $exception) {
            Log::warning('Invoice payment initiation failed.', ['invoice_id' => $invoice->id, 'provider' => $gateway->name(), 'exception' => $exception::class]);
            throw $exception;
        }

        $payment->update([
            'status' => $result['status'] ?? 'processing',
            'metadata' => array_merge($payment->metadata ?? [], $result['metadata'] ?? []),
        ]);
        PaymentAttempt::create([
            'payment_id' => $payment->id,
            'status' => $result['status'] ?? 'processing',
            'message' => $result['message'] ?? 'Payment request submitted to provider.',
            'payload' => ['provider' => $gateway->name(), 'checkout_started' => true],
            'received_at' => now(),
        ]);

        return back()->with('status', $result['message'] ?? 'Payment prompt sent. Follow the instructions on your phone to complete payment.');
    }
}
