<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocietyActivity extends Model
{
    protected $fillable = ['title', 'slug', 'category', 'excerpt', 'body', 'visibility', 'status', 'featured_image_path', 'published_at', 'created_by'];
    protected function casts(): array { return ['published_at' => 'datetime']; }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
