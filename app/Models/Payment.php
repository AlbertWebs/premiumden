<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    protected $fillable = ['invoice_id', 'provider', 'provider_reference', 'status', 'currency', 'amount', 'confirmed_at', 'metadata'];
    protected function casts(): array { return ['amount' => 'decimal:2', 'confirmed_at' => 'datetime', 'metadata' => 'array']; }
    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
    public function attempts(): HasMany { return $this->hasMany(PaymentAttempt::class); }
}
