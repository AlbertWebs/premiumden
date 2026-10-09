@extends('layouts.public')
@section('title', 'About the Den')
@section('content')
@php
    $aboutParagraphs = collect(preg_split('/\r\n\r\n|\r\r|\n\n/', $siteSettings->get('about_body') ?? ''))->map(fn ($paragraph) => trim($paragraph))->filter()->values();
    $aboutLead = $aboutParagraphs->first() ?: 'Premium Business Den is a private business society created to bring professionals and business leaders into meaningful connection.';
@endphp
<section class="about-hero">
    <div class="about-hero-inner">
        <div class="about-hero-copy"><p class="eyebrow"><span class="eyebrow-line"></span>About Premium Business Den</p><h1>{!! nl2br(e($siteSettings->get('about_heading') ?: "A society for\nshared ambition.")) !!}</h1></div>
        <aside class="about-hero-note"><span>Our purpose</span><p>{{ $aboutLead }}</p><small>Nairobi <i>·</i> Kenya</small></aside>
    </div>
</section>
@if(!$siteSettings->get('about_body') || $aboutParagraphs->count() > 1)
<section class="about-perspective">
    <div class="about-perspective-inner">
        <div class="about-section-label"><p class="eyebrow"><span class="eyebrow-line"></span>Our point of view</p><span>01 <i></i> 03</span></div>
        <div class="about-narrative"><h2>Relationships are<br>part of the <em>work.</em></h2><div class="about-copy">
            @if($siteSettings->get('about_body'))
                @foreach($aboutParagraphs->slice(1) as $paragraph)<p>{{ $paragraph }}</p>@endforeach
            @else
                <p>Strong businesses grow through trust, exchange and the people who challenge us to think further. The Den makes space for those connections in a community grounded in professionalism, discretion and mutual respect.</p>
                <p>Our membership is considered. The application journey includes member references and a review process designed to protect the quality and privacy of the society.</p>
            @endif
        </div></div>
    </div>
</section>
@endif
<section class="about-principles" aria-label="The Den's guiding principles"><div class="about-principles-inner"><p class="eyebrow">What brings us together</p><div class="about-principles-grid"><article><span>01</span><h2>Trust</h2><p>Build relationships with confidence and mutual respect.</p></article><article><span>02</span><h2>Exchange</h2><p>Share perspectives, experience and opportunity.</p></article><article><span>03</span><h2>Discretion</h2><p>Make room for considered, professional connection.</p></article></div></div></section>
<section class="closing-cta"><p class="eyebrow eyebrow-light">Interested in joining?</p><h2>Start with a<br><em>conversation.</em></h2><a class="button button-gold" href="{{ route('how-to-join') }}">Understand the process <span aria-hidden="true">→</span></a></section>
@endsection
