<?php

namespace App\Http\Controllers\Member;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class BillingController
{
    public function index(Request $request): View
    {
        $membership = $request->user()->membership;

        $invoices = Invoice::query()
            ->when($membership, fn ($query) => $query->where(fn ($invoices) => $invoices
                ->where('membership_id', $membership->id)
                ->orWhere('membership_application_id', $membership->membership_application_id)),
                fn ($query) => $query->whereRaw('1 = 0'))
            ->with(['items', 'payments'])
            ->orderByDesc('issued_at')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Invoice $invoice) => $invoice->setAttribute(
                'member_invoice_url',
                URL::temporarySignedRoute('application.invoice.show', now()->addDays(30), ['reference' => $invoice->reference]),
            ));

        return view('member.billing.index', compact('membership', 'invoices'));
    }
}
