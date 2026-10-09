<?php

namespace App\Http\Controllers\Public;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\User;
use App\Models\Event;
use App\Models\SocietyActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController
{
    public function index(Request $request): View
    {
        $articles = Article::query()->where('status', ArticleStatus::Published)->where('published_at', '<=', now())
            ->with(['author', 'category', 'tags'])
            ->when($request->filled('author'), fn (Builder $query) => $query->where('author_id', $request->integer('author')))
            ->when($request->filled('search'), fn (Builder $query) => $query->where(fn (Builder $nested) => $nested->where('title', 'like', '%'.$request->string('search')->trim().'%')->orWhere('excerpt', 'like', '%'.$request->string('search')->trim().'%')))
            ->latest('published_at')->paginate(12)->withQueryString();

        return view('public.articles.index', compact('articles'));
    }

    public function show(Article $article): View
    {
        abort_unless($article->status === ArticleStatus::Published && $article->published_at?->isPast(), 404);
        $article->load(['author', 'category', 'tags']);

        return view('public.articles.show', compact('article'));
    }

    public function authors(): View
    {
        $authors = User::query()->whereHas('articles', fn (Builder $query) => $query->where('status', ArticleStatus::Published)->where('published_at', '<=', now()))
            ->withCount(['articles as published_articles_count' => fn (Builder $query) => $query->where('status', ArticleStatus::Published)->where('published_at', '<=', now())])
            ->orderBy('name')->get();

        return view('public.articles.authors', compact('authors'));
    }

    public function sitemap(): \Illuminate\Http\Response
    {
        $articles = Article::query()->where('status', ArticleStatus::Published)->where('published_at', '<=', now())->orderBy('published_at')->get(['slug', 'updated_at']);
        $events = Event::query()->where('status', 'published')->where('visibility', 'public')->where('published_at', '<=', now())->orderBy('starts_at')->get(['slug', 'updated_at']);
        $activities = SocietyActivity::query()->where('status', 'published')->where('visibility', 'public')->where('published_at', '<=', now())->orderBy('published_at')->get(['slug', 'updated_at']);

        return response()->view('public.sitemap', compact('articles', 'events', 'activities'))->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
