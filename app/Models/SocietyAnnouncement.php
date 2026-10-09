<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SocietyAnnouncement extends Model
{
    protected $fillable = ['title', 'body', 'target', 'package_ids', 'status', 'published_at', 'created_by'];
    protected function casts(): array { return ['package_ids' => 'array', 'published_at' => 'datetime']; }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function selectedMembers(): BelongsToMany { return $this->belongsToMany(User::class, 'announcement_recipients', 'society_announcement_id'); }
}
