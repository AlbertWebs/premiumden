<?php

namespace App\Http\Controllers\Author;

use App\Enums\ArticleStatus;
use App\Http\Requests\SaveArticleRequest;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\ArticleReviewHistory;
use App\Services\SaveArticle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ArticleController
{
    public function index(Request $request): View
    {
        $articles = $request->user()->articles()->with('category')->latest()->paginate(20);

        return view('author.articles.index', compact('articles'));
    }

    public function create(): View
    {
        return view('author.articles.edit', ['article' => new Article, 'categories' => ArticleCategory::where('is_active', true)->orderBy('display_order')->get()]);
    }

    public function store(SaveArticleRequest $request, SaveArticle $save): RedirectResponse
    {
        $article = $save->handle($request->validated(), $request->user());

        return redirect()->route('author.articles.edit', $article)->with('status', 'Draft saved.');
    }

    public function edit(Request $request, Article $article): View
    {
        abort_unless($article->author_id === $request->user()->id && $article->status === ArticleStatus::Draft, 403);
        $article->load('tags');

        return view('author.articles.edit', ['article' => $article, 'categories' => ArticleCategory::where('is_active', true)->orderBy('display_order')->get()]);
    }

    public function preview(Request $request, Article $article): View
    {
        abort_unless($request->user()->can('update', $article), 403);
        $article->load(['author', 'category', 'tags']);
        return view('author.articles.preview', compact('article'));
    }

    public function update(SaveArticleRequest $request, Article $article, SaveArticle $save): RedirectResponse
    {
        $request->user()->can('update', $article) || abort(403);
        $save->handle($request->validated(), $request->user(), $article);

        return back()->with('status', 'Draft saved.');
    }

    public function submit(Request $request, Article $article): RedirectResponse
    {
        abort_unless($request->user()->can('submit', $article), 403);
        DB::transaction(function () use ($article, $request): void {
            $article->update(['status' => ArticleStatus::PendingReview]);
            ArticleReviewHistory::create(['article_id' => $article->id, 'user_id' => $request->user()->id, 'action' => 'submitted']);
        });

        return redirect()->route('author.articles.index')->with('status', 'Article submitted for review.');
    }
}
