@extends('layouts.public')
@section('title', 'A place among peers')
@section('content')
@if(auth()->check() && auth()->user()->hasActiveMembership())
@php($member = auth()->user())
<section class="member-home-hero">
    <p class="eyebrow">Your member space</p>
    <h1>Welcome back,<br><em>{{ str($member->name)->before(' ')->toString() }}.</em></h1>
    <p>Your {{ $member->membershipPackage?->name }} membership brings you closer to the people, ideas and opportunities shaping the Den.</p>
    <a class="button button-gold" href="{{ route('member.dashboard') }}">Open your dashboard <span aria-hidden="true">→</span></a>
</section>
<section class="member-home-links" aria-label="Member shortcuts">
    <a href="{{ route('member.directory.index') }}"><span>01</span><div><h2>Meet the network</h2><p>Find members and start a conversation.</p></div><b aria-hidden="true">↗</b></a>
    <a href="{{ route('member.events.index') }}"><span>02</span><div><h2>What's on</h2><p>See upcoming member gatherings.</p></div><b aria-hidden="true">↗</b></a>
    <a href="{{ route('member.messages.index') }}"><span>03</span><div><h2>Your conversations</h2><p>Continue a message with a fellow member.</p></div><b aria-hidden="true">↗</b></a>
    <a href="{{ route('member.profile.edit') }}"><span>04</span><div><h2>Your profile</h2><p>Keep your member details up to date.</p></div><b aria-hidden="true">↗</b></a>
</section>
@else
<section class="hero">
    <div class="hero-copy">
        <p class="eyebrow eyebrow-light"><span class="eyebrow-line"></span> A private business society</p>
        <h1>{!! nl2br(e($siteSettings->get('home_hero_title') ?: "Where business\nfinds belonging.")) !!}</h1>
        <p class="hero-intro">{{ $siteSettings->get('home_hero_intro') ?: 'Premium Business Den brings together ambitious professionals and business leaders in a considered space for connection, perspective and opportunity.' }}</p>
        <div class="hero-actions"><a class="button button-gold" href="{{ route('application.intro') }}">Become a member <span aria-hidden="true">↗</span></a><a class="quiet-link" href="{{ route('about') }}">Discover the society <span aria-hidden="true">→</span></a></div>
    </div>
    <div class="hero-art" aria-hidden="true">
        <div class="art-arc art-arc-one"></div><div class="art-arc art-arc-two"></div><div class="art-disc"><svg class="art-lion" viewBox="219 35 75 95" focusable="false"><defs><filter id="hero-lion-cutout" color-interpolation-filters="sRGB"><feConvolveMatrix in="SourceGraphic" order="3" kernelMatrix="0 -0.45 0 -0.45 2.8 -0.45 0 -0.45 0" edgeMode="duplicate" preserveAlpha="true" result="lion-sharp" /><feColorMatrix in="lion-sharp" type="matrix" values="0 0 0 0 .96 0 0 0 0 .76 0 0 0 0 .28 -1.15 0 0 0 1" /></filter></defs><image href="{{ asset('images/pbd-crest.png') }}" x="0" y="0" width="509" height="428" filter="url(#hero-lion-cutout)" /></svg></div>
        <div class="art-caption"><span>01 — 03</span><span>Private by nature.<br>Connected by ambition.</span></div>
    </div>
    <div class="hero-foot"><span>Nairobi · Kenya</span><span>Independent minds. Shared momentum.</span><span>Scroll to explore ↓</span></div>
</section>

<section class="intro-section section-wrap">
    <div class="intro-kicker"><p class="eyebrow"><span class="eyebrow-line"></span>A different kind of business community</p><span class="intro-index" aria-hidden="true">01 <i></i> 04</span></div>
    <div class="intro-grid"><h2>{!! nl2br(e($siteSettings->get('home_intro_heading') ?: "Good business is\nbuilt together.")) !!}</h2><div class="intro-body"><p>{{ $siteSettings->get('home_intro_body') ?: 'Premium Business Den is a private society shaped around the people behind business. A place to exchange ideas, build trusted relationships and be part of a community that values discretion and meaningful connection.' }}</p><a class="text-link" href="{{ route('about') }}">Get to know the Den <span aria-hidden="true">↗</span></a></div></div>
</section>

<section class="why-join-section">
    <div class="why-join-inner">
        <article class="why-join-feature">
            <p class="eyebrow"><span class="eyebrow-line"></span>Why join</p>
            <h2>{!! nl2br(e($siteSettings->get('why_join_heading') ?: "Make space for\nnew perspective.")) !!}</h2>
            <p class="why-join-copy">{{ $siteSettings->get('why_join_body') ?: 'Connect with peers, exchange experience and take part in a private business community shaped around trust and professional respect.' }}</p>
            <a class="text-link" href="{{ route('membership') }}">Explore membership <span aria-hidden="true">↗</span></a>
            <span class="why-join-number" aria-hidden="true">01</span>
        </article>
        <article class="community-feature">
            <div class="community-orbit" aria-hidden="true"><span>PBD</span></div>
            <p class="eyebrow eyebrow-light">The community</p>
            <h2>{!! nl2br(e($siteSettings->get('community_heading') ?: "Business grows\nin good company.")) !!}</h2>
            <p>{{ $siteSettings->get('community_body') ?: 'The Den creates room for conversations, introductions and shared learning among members.' }}</p>
            <span class="community-index">A society built around connection</span>
        </article>
    </div>
</section>

<section class="tiers-section">
    <div class="section-wrap">
        <div class="section-heading"><div><p class="eyebrow">Membership</p><h2>Find your place<br>in the <em>Den.</em></h2></div><p>Three membership levels. One shared standard of connection, discretion and ambition.</p></div>
        <div class="package-grid">@forelse($packages as $package)<x-package-card :package="$package" />@empty<p class="muted">Membership details are being prepared.</p>@endforelse</div>
        <div class="section-end"><span>01 — 03 / Membership levels</span><a class="text-link" href="{{ route('membership') }}">Explore membership <span aria-hidden="true">→</span></a></div>
    </div>
</section>

<section class="process-section section-wrap">
    <div class="section-heading"><div><p class="eyebrow">The way in</p><h2>Thoughtful by<br><em>design.</em></h2></div><p>Membership begins with people who know you, followed by a considered review. Each step reflects the community we are building together.</p></div>
    <div class="process-grid"><article><span>01</span><h3>Be introduced</h3><p>Identify two current members who know you and your work.</p></article><article><span>02</span><h3>Apply</h3><p>Share your professional story and choose a membership level.</p></article><article><span>03</span><h3>Be considered</h3><p>The Risk Team reviews each application with care and discretion.</p></article><article><span>04</span><h3>Join the Den</h3><p>Approved applicants complete membership setup and are welcomed in.</p></article></div>
    <a class="text-link" href="{{ route('how-to-join') }}">See how membership works <span aria-hidden="true">→</span></a>
</section>

@if($upcomingEvents->isNotEmpty())<section class="latest-insights gatherings-section"><div class="latest-insights-inner"><div class="section-heading"><div><p class="eyebrow">Gatherings</p><h2>Time well<br><em>spent.</em></h2></div><a class="text-link" href="{{ route('events.index') }}">Society calendar <span aria-hidden="true">→</span></a></div><div class="event-grid">@foreach($upcomingEvents as $event)<article class="event-card"><p class="eyebrow">{{ $event->starts_at->format('j F Y · H:i') }}</p><h2><a href="{{ route('events.show', $event->slug) }}">{{ $event->title }}</a></h2><div class="event-meta"><span>{{ $event->location }}</span></div><a class="text-link" href="{{ route('events.show', $event->slug) }}">Event details <span aria-hidden="true">→</span></a></article>@endforeach</div></div></section>@endif

@if($latestActivities->isNotEmpty())<section class="latest-insights society-section"><div class="latest-insights-inner"><div class="section-heading"><div><p class="eyebrow">The society</p><h2>Recent <em>activity.</em></h2></div><a class="text-link" href="{{ route('activities.index') }}">All updates <span aria-hidden="true">→</span></a></div><div class="latest-article-grid">@foreach($latestActivities as $activity)<article class="latest-article"><p class="eyebrow">{{ $activity->category }} · {{ $activity->published_at->format('j M Y') }}</p><h3><a href="{{ route('activities.show', $activity->slug) }}">{{ $activity->title }}</a></h3><p>{{ $activity->excerpt }}</p></article>@endforeach</div></div></section>@endif

@php($faqs = collect(preg_split('/\r\n|\r|\n/', $siteSettings->get('faq_items') ?? ''))->map(fn($row) => array_map('trim', explode('|', $row, 2)))->filter(fn($row) => count($row) === 2 && filled($row[0]) && filled($row[1])))
@if($faqs->isNotEmpty())<section class="page-body homepage-faq"><p class="eyebrow">Frequently asked questions</p><h2>Questions, <em>answered.</em></h2>@foreach($faqs as [$question, $answer])<details><summary>{{ $question }}</summary><p>{{ $answer }}</p></details>@endforeach</section>@endif

<section class="closing-cta"><p class="eyebrow eyebrow-light">Your next chapter</p><h2>Make room for<br><em>what’s next.</em></h2><a class="button button-gold" href="{{ route('application.intro') }}">Begin your application <span aria-hidden="true">↗</span></a></section>
@endif

@if($latestArticles->isNotEmpty())
<section class="insights-section" aria-labelledby="home-insights-title">
    <div class="insights-inner">
        <div class="insights-heading">
            <div>
                <p class="eyebrow"><span class="eyebrow-line"></span>Perspectives from the Den</p>
                <h2 id="home-insights-title">Insights for<br><em>what’s next.</em></h2>
            </div>
            <a class="insights-all-link" href="{{ route('articles.index') }}">Explore all insights <span aria-hidden="true">↗</span></a>
        </div>
        <div class="insights-grid">
            @foreach($latestArticles as $article)
            <article class="insight-card">
                @if($article->featured_image_path)
                    <div class="insight-art insight-art-image" aria-hidden="true"><img src="{{ Storage::disk('public')->url($article->featured_image_path) }}" alt="" loading="lazy" decoding="async"></div>
                @else
                    <div class="insight-art insight-art-{{ $loop->iteration }}" aria-hidden="true"><span>{{ sprintf('%02d', $loop->iteration) }}</span><i></i></div>
                @endif
                <div class="insight-card-content">
                    <p class="insight-meta">{{ $article->category?->name ?? 'The Den' }} <span aria-hidden="true">·</span> {{ $article->published_at->format('j M Y') }}</p>
                    <h3><a href="{{ route('articles.show', $article->slug) }}">{{ $article->title }}</a></h3>
                    <p class="insight-excerpt">{{ $article->excerpt }}</p>
                    <div class="insight-card-footer"><span>By {{ $article->author->name }}</span><a href="{{ route('articles.show', $article->slug) }}" aria-label="Read {{ $article->title }}">Read insight <span aria-hidden="true">↗</span></a></div>
                </div>
            </article>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection
