@extends(auth()->user()?->hasActiveMembership() ? 'layouts.member' : 'layouts.public')
@section('title', $activity->title)
@section('meta_description', $activity->excerpt ?: $activity->title)
@section('content')
<article class="article-reading">
    <a class="back-to-directory" href="{{ route('activities.index') }}">← Society activity</a>
    <p class="eyebrow">{{ $activity->category }} · {{ $activity->published_at->format('j F Y') }}</p>
    <h1>{{ $activity->title }}</h1>
    @if($activity->excerpt)<p class="article-deck">{{ $activity->excerpt }}</p>@endif
    @if($activity->featured_image_path)<img class="article-hero-image" src="{{ Storage::disk('public')->url($activity->featured_image_path) }}" alt="">@endif
    <div class="article-body">{{ $activity->body }}</div>
</article>
@endsection
