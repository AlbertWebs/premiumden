@extends(($memberView ?? false) ? 'layouts.member' : 'layouts.public')
@section('title', 'Events & gatherings')
@section('meta_description', 'Society gatherings and upcoming Premium Business Den events.')
@section('content')
@unless($memberView ?? false)<section class="page-hero"><p class="eyebrow">The society calendar</p><h1>Events &amp; <em>gatherings.</em></h1><p>Upcoming moments for conversation, business and shared learning.</p></section>@endunless
<section class="event-index">
    @if($events->isEmpty())
        <div class="event-empty"><p class="eyebrow">Coming up</p><h2>New dates will be shared here.</h2><p>There are no published upcoming events at the moment.</p></div>
    @else
        <div class="event-grid">
            @foreach($events as $event)
                <article class="event-card">
                    @if($event->featured_image_path)<img class="event-card-image" src="{{ Storage::disk('public')->url($event->featured_image_path) }}" alt="">@endif
                    <p class="eyebrow">{{ $event->starts_at->format('l · j F Y') }}</p>
                    <h2><a href="{{ $memberView ? route('member.events.show', $event->slug) : route('events.show', $event->slug) }}">{{ $event->title }}</a></h2>
                    <p>{{ \Illuminate\Support\Str::limit($event->description, 170) }}</p>
                    <div class="event-meta"><span>{{ $event->starts_at->format('H:i') }}{{ $event->ends_at ? ' – '.$event->ends_at->format('H:i') : '' }}</span><span>{{ $event->location }}</span></div>
                    <a class="text-link" href="{{ $memberView ? route('member.events.show', $event->slug) : route('events.show', $event->slug) }}">Event details <span aria-hidden="true">→</span></a>
                </article>
            @endforeach
        </div>
        {{ $events->links() }}
    @endif
</section>
@if($memberView ?? false)<section class="page-hero"><p class="eyebrow">Member space / Events</p><h1>Events &amp; <em>gatherings.</em></h1><p>Upcoming moments for conversation, business and shared learning.</p></section>@endif
@endsection
