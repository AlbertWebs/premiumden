<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WelcomePackage extends Model
{
    protected $fillable = ['membership_id', 'status', 'prepared_at', 'dispatched_at', 'delivered_at', 'tracking_reference', 'notes'];
    protected function casts(): array { return ['prepared_at' => 'datetime', 'dispatched_at' => 'datetime', 'delivered_at' => 'datetime']; }
    public function membership(): BelongsTo { return $this->belongsTo(Membership::class); }
}
