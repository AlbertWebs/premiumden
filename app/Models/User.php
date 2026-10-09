<?php

namespace App\Models;

use App\Enums\MembershipTier;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'membership_package_id', 'membership_active', 'email_message_notifications', 'email_society_updates'];

    protected $hidden = ['password', 'remember_token', 'first_access_token_hash'];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'first_access_expires_at' => 'datetime', 'password' => 'hashed', 'role' => UserRole::class, 'membership_active' => 'boolean', 'email_message_notifications' => 'boolean', 'email_society_updates' => 'boolean'];
    }

    public function membershipPackage()
    {
        return $this->belongsTo(MembershipPackage::class);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(MemberProfile::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'author_id');
    }

    public function membership(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Membership::class);
    }

    public function hasRole(UserRole|string $role): bool
    {
        return $this->role?->value === ($role instanceof UserRole ? $role->value : $role);
    }

    public function hasActiveMembership(): bool
    {
        if (! $this->hasRole(UserRole::Member) || ! $this->membership_active || ! $this->membershipPackage?->is_active) return false;
        $membership = $this->membership;
        return ! $membership || ($membership->status === 'active' && (! $membership->expires_at || ! $membership->expires_at->isBefore(today())));
    }

    public function scopeCurrentMember(Builder $query): Builder
    {
        return $query->where('role', UserRole::Member->value)->where('membership_active', true)
            ->where(function (Builder $membershipQuery): void {
                $membershipQuery->whereDoesntHave('membership')->orWhereHas('membership', fn (Builder $membership) => $membership->where('status', 'active')
                    ->where(fn (Builder $expiry) => $expiry->whereNull('expires_at')->orWhereDate('expires_at', '>=', today())));
            });
    }

    public function canViewMember(User $member): bool
    {
        if (! $this->hasActiveMembership() || ! $member->hasActiveMembership()) {
            return false;
        }

        return $this->membershipPackage->is_active
            && $this->membershipPackage->networking_level >= $member->membershipPackage->networking_level;
    }

    public function canMessageMember(User $member): bool
    {
        return $this->id !== $member->id && $this->hasActiveMembership() && $this->canViewMember($member);
    }
}
