<?php

namespace App\Models;

use App\Enums\ApplicantDocumentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicantDocument extends Model
{
    protected $fillable = ['document_type', 'identity_type', 'original_name', 'storage_path', 'mime_type', 'size_bytes', 'status', 'reviewed_by', 'review_note', 'reviewed_at'];

    protected function casts(): array
    {
        return ['status' => ApplicantDocumentStatus::class, 'reviewed_at' => 'datetime'];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(MembershipApplication::class, 'membership_application_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
