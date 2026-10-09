<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = ['membership_application_id', 'membership_id', 'kind', 'reference', 'status', 'currency', 'subtotal', 'total', 'issued_at', 'due_at'];

    protected function casts(): array
    {
        return ['subtotal' => 'decimal:2', 'total' => 'decimal:2', 'issued_at' => 'datetime', 'due_at' => 'datetime'];
    }

    public function application(): BelongsTo { return $this->belongsTo(MembershipApplication::class, 'membership_application_id'); }
    public function items(): HasMany { return $this->hasMany(InvoiceItem::class); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
    public function membership(): BelongsTo { return $this->belongsTo(Membership::class); }
}
