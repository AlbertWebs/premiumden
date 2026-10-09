<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberDealDocument extends Model
{
    protected $fillable = ['member_deal_id', 'original_name', 'path', 'mime_type', 'size_bytes'];

    public function deal(): BelongsTo
    {
        return $this->belongsTo(MemberDeal::class, 'member_deal_id');
    }
}
