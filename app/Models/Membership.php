<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Membership extends Model
{
    protected $fillable = ['user_id', 'membership_application_id', 'membership_package_id', 'number', 'status', 'started_at', 'expires_at'];
    protected function casts(): array { return ['started_at' => 'date', 'expires_at' => 'date']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function application(): BelongsTo { return $this->belongsTo(MembershipApplication::class, 'membership_application_id'); }
    public function package(): BelongsTo { return $this->belongsTo(MembershipPackage::class, 'membership_package_id'); }
    public function card(): HasOne { return $this->hasOne(MembershipCard::class); }
    public function welcomePackage(): HasOne { return $this->hasOne(WelcomePackage::class); }
}
