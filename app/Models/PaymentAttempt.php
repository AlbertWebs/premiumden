<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentAttempt extends Model
{
    protected $fillable = ['payment_id', 'provider_event_id', 'status', 'message', 'payload', 'received_at'];
    protected function casts(): array { return ['payload' => 'array', 'received_at' => 'datetime']; }
    public function payment(): BelongsTo { return $this->belongsTo(Payment::class); }
}
