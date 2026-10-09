<?php

namespace App\Http\Controllers\Public;

use App\Models\Invoice;
use Illuminate\View\View;
use Illuminate\Support\Facades\URL;

class ApplicantInvoiceController
{
    public function show(string $reference): View
    {
        $invoice = Invoice::with(['application.package', 'items', 'payments'])->where('reference', $reference)->firstOrFail();
        $paymentUrl = URL::temporarySignedRoute('application.invoice.pay', now()->addDays(30), ['reference' => $reference]);
        return view('public.invoice', compact('invoice', 'paymentUrl'));
    }
}
