@extends('layouts.member')
@section('title', 'My membership')
@section('content')
<section class="member-record-page">
    <div class="member-record-summary">
        <div>
            <p class="eyebrow">Membership level</p>
            <h2>{{ $member->membershipPackage->name }}</h2>
            <p>{{ $member->membershipPackage->description }}</p>
        </div>
        <span class="status-pill">{{ $membership?->status ? str($membership->status)->title() : 'Active account' }}</span>
    </div>
    <dl class="member-record-details">
        <div><dt>Membership number</dt><dd>{{ $membership?->number ?? 'Issued after application activation' }}</dd></div>
        <div><dt>Current standing</dt><dd>{{ $member->membership_active ? 'Active' : 'Inactive' }}</dd></div>
        <div><dt>Started</dt><dd>{{ $membership?->started_at?->format('j F Y') ?? 'On activation' }}</dd></div>
        <div><dt>Renewal date</dt><dd>{{ $membership?->expires_at?->format('j F Y') ?? 'Not yet set' }}</dd></div>
        <div><dt>Term</dt><dd>{{ $member->membershipPackage->renewal_months }} months</dd></div>
    </dl>
    <div class="member-record-benefits">
        <p class="eyebrow">Member benefits</p>
        <ul class="feature-list">@foreach(($member->membershipPackage->benefits ?? []) as $benefit)<li>{{ $benefit }}</li>@endforeach</ul>
    </div>
    <a class="text-link" href="{{ route('member.card.show') }}">View your digital card <span aria-hidden="true">→</span></a>
</section>
<section class="page-hero"><p class="eyebrow">Member space / Membership</p><h1>Your <em>membership.</em></h1><p>Your current package, term and membership standing.</p></section>
@endsection
