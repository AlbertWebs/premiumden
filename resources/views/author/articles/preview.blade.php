@extends('layouts.author')
@section('title', 'Preview · '.$article->title)
@section('content')
<div class="admin-page-heading"><div><p class="eyebrow">Private preview / {{ $article->category?->name ?? 'The Den' }}</p><h1>{{ $article->title }}</h1><p>This draft is visible only to you. It is not published.</p></div><a class="button" href="{{ route('author.articles.edit', $article) }}">Return to editor <span aria-hidden="true">←</span></a></div>
<article class="article-reading author-preview-reading">@if($article->excerpt)<p class="article-deck">{{ $article->excerpt }}</p>@endif<div class="article-author">By {{ $article->author->name }}</div>@if($article->featured_image_path)<img class="article-featured-image" src="{{ Storage::disk('public')->url($article->featured_image_path) }}" alt="">@endif<div class="article-body">{!! app(\App\Services\ArticleBodyFormatter::class)->render($article->body) !!}</div></article>
@endsection
