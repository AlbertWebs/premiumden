<?php

namespace App\Enums;

enum ArticleStatus: string
{
    case Draft = 'draft';
    case PendingReview = 'pending_review';
    case Published = 'published';
    case Archived = 'archived';

    public function label(): string
    {
        return str($this->value)->replace('_', ' ')->title()->toString();
    }
}
