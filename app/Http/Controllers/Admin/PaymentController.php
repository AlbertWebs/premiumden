<?php

namespace App\Http\Controllers\Admin;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController
{
    public function index(Request $request): View
    {
        $payments = Payment::query()->with(['invoice.application', 'attempts'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('search'), function ($query) use ($request): void {
                $term = '%'.$request->string('search')->trim().'%';
                $query->where(fn ($nested) => $nested->where('provider_reference', 'like', $term)
                    ->orWhereHas('invoice', fn ($invoice) => $invoice->where('reference', 'like', $term)
                        ->orWhereHas('application', fn ($application) => $application->where('full_name', 'like', $term)->orWhere('email', 'like', $term))));
            })->latest()->paginate(25)->withQueryString();
        return view('admin.payments.index', compact('payments'));
    }
}
