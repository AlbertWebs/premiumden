<?php

namespace App\Models;

use App\Enums\ArticleStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Article extends Model
{
    protected $fillable = ['title', 'slug', 'excerpt', 'body', 'featured_image_path', 'author_id', 'category_id', 'status', 'published_at', 'seo_title', 'seo_description', 'og_image_path'];

    protected function casts(): array
    {
        return ['status' => ArticleStatus::class, 'published_at' => 'datetime'];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class, 'category_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ArticleTag::class, 'article_tag', 'article_id', 'tag_id')->withTimestamps();
    }

    public function reviewHistory(): HasMany
    {
        return $this->hasMany(ArticleReviewHistory::class);
    }
}
