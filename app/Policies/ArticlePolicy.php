<?php

namespace App\Policies;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    public function update(User $user, Article $article): bool
    {
        if ($user->hasRole('super_administrator') || $user->hasRole('content_administrator')) {
            return true;
        }

        return $user->hasRole('author') && $article->author_id === $user->id && $article->status === ArticleStatus::Draft;
    }

    public function submit(User $user, Article $article): bool
    {
        return $user->hasRole('author') && $article->author_id === $user->id && $article->status === ArticleStatus::Draft;
    }
}
