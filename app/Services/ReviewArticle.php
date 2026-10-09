<?php

namespace App\Services;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\ArticleReviewHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReviewArticle
{
    public function handle(Article $article, User $reviewer, string $action, ?string $note = null): void
    {
        $canPublishDraft = $action === 'publish' && $article->status === ArticleStatus::Draft;
        if ($article->status !== ArticleStatus::PendingReview && ! $canPublishDraft && $action !== 'archive') {
            throw ValidationException::withMessages(['action' => 'This article is not awaiting review.']);
        }

        $status = match ($action) {
            'publish' => ArticleStatus::Published,
            'return' => ArticleStatus::Draft,
            'archive' => ArticleStatus::Archived,
            default => throw ValidationException::withMessages(['action' => 'Choose a valid review action.']),
        };

        DB::transaction(function () use ($article, $reviewer, $action, $note, $status): void {
            $article->update(['status' => $status, 'published_at' => $status === ArticleStatus::Published ? now() : $article->published_at]);
            ArticleReviewHistory::create(['article_id' => $article->id, 'user_id' => $reviewer->id, 'action' => $action, 'note' => $note]);
            app(RecordAuditEvent::class)->handle('article.reviewed', $article, ['action' => $action, 'status' => $status->value], $reviewer->id);
        });
    }
}
