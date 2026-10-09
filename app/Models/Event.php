<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    protected $fillable = ['title', 'slug', 'description', 'featured_image_path', 'location', 'starts_at', 'ends_at', 'visibility', 'package_ids', 'registration_url', 'registration_information', 'status', 'published_at', 'created_by'];
    protected function casts(): array { return ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'package_ids' => 'array', 'published_at' => 'datetime']; }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
