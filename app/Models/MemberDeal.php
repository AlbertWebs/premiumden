<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MemberDeal extends Model
{
    protected $fillable = ['title', 'slug', 'partner', 'category', 'summary', 'details', 'member_value', 'application_instructions', 'minimum_package_id', 'starts_at', 'closes_at', 'is_active', 'display_order', 'created_by'];

    protected function casts(): array
    {
        return ['starts_at' => 'date', 'closes_at' => 'date', 'is_active' => 'boolean'];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(MemberDealApplication::class);
    }

    public function targetPackage(): BelongsTo
    {
        return $this->belongsTo(MembershipPackage::class, 'minimum_package_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(MemberDealDocument::class);
    }
}
