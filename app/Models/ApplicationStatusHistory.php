<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationStatusHistory extends Model
{
    protected $fillable = ['membership_application_id', 'from_status', 'to_status', 'actor_id', 'note', 'occurred_at'];

    protected function casts(): array
    {
        return ['from_status' => ApplicationStatus::class, 'to_status' => ApplicationStatus::class, 'occurred_at' => 'datetime'];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(MembershipApplication::class, 'membership_application_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
