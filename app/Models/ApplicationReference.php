<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationReference extends Model
{
    protected $fillable = ['full_name', 'email', 'phone', 'member_user_id', 'membership_number', 'response_status', 'responded_at'];

    protected function casts(): array { return ['responded_at' => 'datetime']; }

    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_user_id');
    }
}
