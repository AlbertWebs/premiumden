@extends('layouts.member')
@section('title', 'Membership billing')
@section('content')
<section class="billing-content section-wrap">
    <div class="billing-heading-row"><div><p class="eyebrow">YOUR ACCOUNT · MEMBERSHIP BILLING</p><h2>Invoices &amp; payments</h2></div><span>{{ $invoices->total() }} {{ str('record')->plural($invoices->total()) }}</span></div>
    @forelse($invoices as $invoice)
        <article class="billing-invoice-card">
            <header>
                <div><span class="billing-kind">{{ $invoice->kind === 'renewal' ? 'Membership renewal' : 'Membership invoice' }}</span><h3>{{ $invoice->reference }}</h3></div>
                <span class="status-pill">{{ str($invoice->status)->replace('_', ' ')->title() }}</span>
            </header>
            <div class="billing-dates"><span>Issued {{ $invoice->issued_at?->format('j F Y') ?? '—' }}</span>@if($invoice->due_at)<span>Due {{ $invoice->due_at->format('j F Y') }}</span>@endif</div>
            <div class="billing-items">
                @foreach($invoice->items as $item)
                    <div><span>{{ $item->description }} <small>× {{ $item->quantity }}</small></span><strong>{{ $invoice->currency }} {{ number_format((float) $item->amount, 2) }}</strong></div>
                @endforeach
            </div>
            <div class="billing-total"><span>Total</span><strong>{{ $invoice->currency }} {{ number_format((float) $invoice->total, 2) }}</strong></div>
            @if($invoice->payments->isNotEmpty())
                <div class="billing-payments"><p class="eyebrow">Payment activity</p>
                    @foreach($invoice->payments as $payment)
                        <p><span>{{ $payment->provider_reference ?: str($payment->provider)->replace('_', ' ')->title() }}</span><strong>{{ str($payment->status)->title() }}</strong>@if($payment->confirmed_at)<small>{{ $payment->confirmed_at->format('j M Y, H:i') }}</small>@endif</p>
                    @endforeach
                </div>
            @endif
            <a class="text-link" href="{{ $invoice->member_invoice_url }}">{{ in_array($invoice->status, ['pending', 'awaiting_payment'], true) ? 'View invoice and payment options' : 'View invoice' }} <span aria-hidden="true">→</span></a>
        </article>
    @empty
        <div class="directory-empty billing-empty"><span class="empty-emblem">P<span>·</span>D</span><h2>No billing records yet.</h2><p>Membership invoices and payment updates will appear here when they are issued.</p></div>
    @endforelse
    {{ $invoices->links() }}
</section>
<section class="page-hero"><p class="eyebrow">Your account / Billing</p><h1>Membership <em>billing.</em></h1><p>Review invoices and payment activity for your membership in one private place.</p></section>
@endsection
