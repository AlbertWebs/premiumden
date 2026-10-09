@extends(auth()->user()?->hasActiveMembership() ? 'layouts.member' : 'layouts.public')
@section('title', 'Authors')
@section('meta_description', 'Meet the authors contributing to Premium Business Den.')
@section('content')
@unless(auth()->user()?->hasActiveMembership())<section class="directory-heading"><p class="eyebrow">The Den / Authors</p><h1>People behind<br><em>the perspective.</em></h1><p>Meet contributors whose published work appears in the Den.</p></section>@endunless
<section class="authors-index">@if($authors->isEmpty())<div class="directory-empty"><span class="empty-emblem">P<span>·</span>D</span><h2>Authors will be introduced here.</h2><p>Author profiles appear when their first article is published.</p></div>@else<div class="authors-grid">@foreach($authors as $author)<article class="author-card"><div class="member-avatar">{{ collect(explode(' ', $author->name))->map(fn($part) => mb_substr($part, 0, 1))->take(2)->implode('') }}</div><h2>{{ $author->name }}</h2><p>{{ $author->published_articles_count }} published {{ \Illuminate\Support\Str::plural('article', $author->published_articles_count) }}</p><a class="text-link" href="{{ route('articles.index', ['author' => $author->id]) }}">Read their work <span aria-hidden="true">→</span></a></article>@endforeach</div>@endif</section>
@if(auth()->user()?->hasActiveMembership())<section class="directory-heading"><p class="eyebrow">Member space / Authors</p><h1>People behind<br><em>the perspective.</em></h1><p>Meet contributors whose published work appears in the Den.</p></section>@endif
@endsection
