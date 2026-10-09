<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactEnquiry extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'subject', 'message', 'status', 'handled_by', 'contacted_at', 'resolved_at'];
    protected function casts(): array { return ['contacted_at' => 'datetime', 'resolved_at' => 'datetime']; }
    public function handler(): BelongsTo { return $this->belongsTo(User::class, 'handled_by'); }
}
