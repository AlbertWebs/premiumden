<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberProfile extends Model
{
    protected $fillable = ['company', 'job_title', 'industry', 'business_category', 'location', 'interests', 'biography', 'is_listed', 'photo_path'];

    protected function casts(): array
    {
        return ['interests' => 'array', 'is_listed' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
