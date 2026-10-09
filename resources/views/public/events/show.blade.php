@extends(($memberView ?? false) ? 'layouts.member' : 'layouts.public')
@section('title', $event->title)
@section('content')
<article class="event-reading">
    <a class="text-link" href="{{ $memberView ? route('member.events.index') : route('events.index') }}">← All events</a>
    <p class="eyebrow">{{ $event->starts_at->format('l · j F Y · H:i') }}</p>
    <h1>{{ $event->title }}</h1>
    <p class="event-location">{{ $event->location }}@if($event->ends_at) · Until {{ $event->ends_at->format('H:i') }}@endif</p>
    @if($event->featured_image_path)<img class="article-featured-image" src="{{ Storage::disk('public')->url($event->featured_image_path) }}" alt="">@endif
    <div class="event-description">{{ $event->description }}</div>
    @if($event->registration_information)
        <section class="event-registration"><p class="eyebrow">Registration information</p><p>{{ $event->registration_information }}</p>@if($event->registration_url)<a class="button" href="{{ $event->registration_url }}" target="_blank" rel="noopener noreferrer">Continue to registration <span aria-hidden="true">↗</span></a>@endif</section>
    @endif
</article>
@endsection
