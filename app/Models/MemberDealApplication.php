<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberDealApplication extends Model
{
    protected $fillable = ['member_deal_id', 'user_id', 'note', 'status', 'applied_at'];

    protected function casts(): array
    {
        return ['applied_at' => 'datetime'];
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(MemberDeal::class, 'member_deal_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
