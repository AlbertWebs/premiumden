<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleReviewHistory extends Model
{
    protected $fillable = ['article_id', 'user_id', 'action', 'note'];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
