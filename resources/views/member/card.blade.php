@extends('layouts.member')
@section('title', 'Digital membership card')
@section('content')
<section class="section-wrap member-card-page">
    @if($membership)<article class="digital-membership-card" aria-label="Premium Business Den digital membership card">
        <div class="digital-card-top"><span class="eyebrow eyebrow-light">Premium Business Den</span><span class="card-status"><i></i> {{ ucfirst($membership->status) }}</span></div>
        <div class="digital-card-member"><p class="eyebrow eyebrow-light">Member</p><h2>{{ $membership->user->name }}</h2><p>{{ $membership->package->name }} membership</p></div>
        <div class="digital-card-bottom"><div><small>Membership number</small><strong>{{ $membership->number }}</strong></div><div><small>Valid through</small><strong>{{ $membership->expires_at?->format('F Y') ?? 'Membership term' }}</strong></div><div class="card-seal" aria-hidden="true">P<span>·</span>D</div></div>
    </article>@else<div class="event-empty member-card-pending"><p class="eyebrow">Card issuance</p><h2>Your digital card will appear after membership activation.</h2><p>The membership team issues your number and digital card after payment verification.</p><a class="text-link" href="{{ route('member.membership.show') }}">View membership details <span aria-hidden="true">→</span></a></div>@endif
    <p class="digital-card-note">This digital card represents your membership. Physical card and NFC services are managed separately.</p>
</section>
<section class="page-hero"><p class="eyebrow">Your membership / Digital card</p><h1>Digital member <em>card.</em></h1><p class="member-card-intro">Keep your society membership details close. Your card identifier is unique to your membership.</p></section>
@endsection
