<?php

namespace App\Models;

use App\Enums\MembershipTier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MembershipPackage extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'price', 'benefits', 'is_active', 'display_order', 'renewal_months', 'networking_level'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'benefits' => 'array', 'is_active' => 'boolean'];
    }

    public function tier(): MembershipTier
    {
        return MembershipTier::from($this->slug);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
