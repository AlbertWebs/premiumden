<?php

namespace App\Services;

use App\Models\Article;
use App\Models\ArticleTag;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SaveArticle
{
    public function handle(array $data, User $actor, ?Article $article = null): Article
    {
        $featuredUpload = $data['featured_image'] ?? null;
        $ogUpload = $data['og_image'] ?? null;
        unset($data['featured_image'], $data['og_image']);
        $previousFeatured = $article?->featured_image_path;
        $previousOg = $article?->og_image_path;
        $newFeatured = $featuredUpload?->storePublicly('articles', 'public');
        $newOg = $ogUpload?->storePublicly('articles', 'public');
        if ($newFeatured) { $data['featured_image_path'] = $newFeatured; }
        if ($newOg) { $data['og_image_path'] = $newOg; }

        try {
            $saved = DB::transaction(function () use ($data, $actor, $article): Article {
            $tagNames = collect(preg_split('/[,\n]+/', $data['tags'] ?? '') ?: [])
                ->map(fn (string $tag) => trim($tag))
                ->filter()->unique(fn (string $tag) => Str::lower($tag))->take(12)->values();
            unset($data['tags']);

            $data['author_id'] = $actor->hasRole('author') ? $actor->id : ($data['author_id'] ?? $article?->author_id ?? $actor->id);
            $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['title'], $article?->id);
            $data['body'] = app(ArticleBodyFormatter::class)->sanitize($data['body']);

            if ($article) {
                $article->update($data);
            } else {
                $article = Article::create($data + ['status' => 'draft']);
            }

            $tagIds = $tagNames->filter(fn (string $name) => filled(Str::slug($name)))
                ->map(fn (string $name) => ArticleTag::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name])->id);
            $article->tags()->sync($tagIds);

            return $article->refresh();
            });
        } catch (\Throwable $exception) {
            foreach ([$newFeatured, $newOg] as $path) { if ($path) { Storage::disk('public')->delete($path); } }
            throw $exception;
        }

        foreach ([[$previousFeatured, $newFeatured], [$previousOg, $newOg]] as [$previous, $replacement]) {
            if ($previous && $replacement && $previous !== $replacement) { Storage::disk('public')->delete($previous); }
        }

        return $saved;
    }

    private function uniqueSlug(string $value, ?int $ignoreId): string
    {
        $base = Str::slug($value) ?: 'article';
        $slug = $base;
        $suffix = 2;

        while (Article::query()->where('slug', $slug)->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
