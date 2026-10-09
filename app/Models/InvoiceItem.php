<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = ['invoice_id', 'description', 'quantity', 'unit_amount', 'amount'];
    protected function casts(): array { return ['unit_amount' => 'decimal:2', 'amount' => 'decimal:2']; }
    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
}
