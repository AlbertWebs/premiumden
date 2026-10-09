@extends('layouts.public')
@section('title', $package->name.' membership')
@section('content')
@php
    $networkAccess = match ($package->slug) {
        'diamond' => 'Diamond members',
        'gold' => 'Gold and Diamond members',
        'platinum' => 'All membership levels',
        default => 'Member network access confirmed during review',
    };
@endphp
<section class="page-hero package-hero">
    <p class="eyebrow">Membership / {{ $package->name }}</p>
    <h1>{{ $package->name }}<br><em>membership.</em></h1>
    <p>{{ $package->description }}</p>
    <a class="button button-gold" href="{{ route('application.intro', ['package' => $package->slug]) }}">Apply for {{ $package->name }} <span aria-hidden="true">↗</span></a>
</section>

<section class="package-overview" aria-labelledby="package-overview-title">
    <div class="package-overview-copy">
        <p class="eyebrow">A place in the Den</p>
        <h2 id="package-overview-title">Built around<br><em>connection.</em></h2>
        <p>{{ $package->description }}</p>
        <p>Membership brings together ambitious business leaders for thoughtful connections, shared insight and meaningful opportunities. Every application is considered by the membership team, and membership begins after approval and completion of the required steps.</p>
    </div>
    <dl class="package-facts">
        <div><dt>Membership level</dt><dd>{{ $package->name }}</dd></div>
        <div><dt>Membership term</dt><dd>{{ $package->renewal_months }} months</dd></div>
        <div><dt>Member network</dt><dd>{{ $networkAccess }}</dd></div>
        <div><dt>Membership fee</dt><dd>
            @if($package->price !== null)
                KES {{ number_format((float) $package->price, 2) }}
            @else
                Confirmed during application review
            @endif
        </dd></div>
    </dl>
</section>

<section class="package-benefits" aria-labelledby="package-benefits-title">
    <div><p class="eyebrow">Included with {{ $package->name }}</p><h2 id="package-benefits-title">Make room for<br><em>what’s next.</em></h2></div>
    @if(count($package->benefits ?? []))
        <ul class="feature-list">@foreach($package->benefits as $benefit)<li>{{ $benefit }}</li>@endforeach</ul>
    @else
        <p>Benefits for this membership level will be shared by the membership team during your application review.</p>
    @endif
</section>

<section class="package-application" aria-labelledby="package-application-title">
    <p class="eyebrow">How to join</p>
    <h2 id="package-application-title">A clear path<br>to membership.</h2>
    <ol class="package-steps">
        <li><span>01</span><div><h3>Share your details</h3><p>Tell us about yourself, your work and the connections you hope to build.</p></div></li>
        <li><span>02</span><div><h3>Introduce two members</h3><p>Provide contact details for two current members who know you and your work.</p></div></li>
        <li><span>03</span><div><h3>Application review</h3><p>The Risk Team reviews each application. The membership team will contact you about next steps.</p></div></li>
        <li><span>04</span><div><h3>Complete your membership</h3><p>Approved applicants provide the requested documents and complete payment before membership is activated.</p></div></li>
    </ol>
    <p class="package-approval-note">Applying for {{ $package->name }} does not guarantee acceptance. Membership is subject to approval, document verification and payment.</p>
    <a class="button button-gold" href="{{ route('application.intro', ['package' => $package->slug]) }}">Begin your {{ $package->name }} application <span aria-hidden="true">↗</span></a>
</section>
<section class="page-body compact package-more"><a class="text-link" href="{{ route('membership') }}">Compare all membership levels <span aria-hidden="true">→</span></a><a class="text-link" href="{{ route('how-to-join') }}">Read the full application guide <span aria-hidden="true">→</span></a></section>
@endsection
