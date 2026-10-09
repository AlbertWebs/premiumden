<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ArticleStatus;
use App\Http\Requests\SaveArticleRequest;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\User;
use App\Services\ReviewArticle;
use App\Services\SaveArticle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController
{
    public function index(Request $request): View
    {
        $articles = Article::query()->with(['author', 'category'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('search'), fn ($query) => $query->where('title', 'like', '%'.$request->string('search')->trim().'%'))
            ->latest()->paginate(20)->withQueryString();

        return view('admin.articles.index', ['articles' => $articles, 'statuses' => ArticleStatus::cases()]);
    }

    public function create(): View
    {
        return view('admin.articles.edit', ['article' => new Article, 'categories' => ArticleCategory::where('is_active', true)->orderBy('display_order')->get(), 'authors' => User::where('role', 'author')->orderBy('name')->get()]);
    }

    public function store(SaveArticleRequest $request, SaveArticle $save): RedirectResponse
    {
        $article = $save->handle($request->validated(), $request->user());

        return redirect()->route('admin.articles.edit', $article)->with('status', 'Article saved as a draft.');
    }

    public function edit(Article $article): View
    {
        $article->load('tags');

        return view('admin.articles.edit', ['article' => $article, 'categories' => ArticleCategory::where('is_active', true)->orderBy('display_order')->get(), 'authors' => User::where('role', 'author')->orderBy('name')->get()]);
    }

    public function update(SaveArticleRequest $request, Article $article, SaveArticle $save): RedirectResponse
    {
        $request->user()->can('update', $article) || abort(403);
        $save->handle($request->validated(), $request->user(), $article);

        return back()->with('status', 'Article changes saved.');
    }

    public function review(Request $request, Article $article, ReviewArticle $review): RedirectResponse
    {
        abort_unless($request->user()->hasRole('content_administrator') || $request->user()->hasRole('super_administrator'), 403);
        $data = $request->validate(['action' => ['required', 'in:publish,return,archive'], 'note' => ['nullable', 'string', 'max:3000']]);
        $review->handle($article, $request->user(), $data['action'], $data['note'] ?? null);

        return back()->with('status', 'Article review recorded.');
    }
}
