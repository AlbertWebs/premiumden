<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MembershipCard extends Model
{
    protected $fillable = ['membership_id', 'identifier', 'status', 'physical_card_reference'];
    public function membership(): BelongsTo { return $this->belongsTo(Membership::class); }
}
