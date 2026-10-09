<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MembershipApplication extends Model
{
    protected $fillable = ['reference', 'membership_package_id', 'status', 'full_name', 'email', 'phone', 'company', 'job_title', 'industry', 'location', 'biography', 'company_description', 'employee_count', 'consent_at', 'submitted_at', 'document_upload_token_hash', 'document_upload_expires_at', 'draft_token_hash', 'draft_expires_at'];

    protected function casts(): array
    {
        return ['status' => ApplicationStatus::class, 'consent_at' => 'datetime', 'submitted_at' => 'datetime', 'document_upload_expires_at' => 'datetime', 'draft_expires_at' => 'datetime'];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(MembershipPackage::class, 'membership_package_id');
    }

    public function references(): HasMany
    {
        return $this->hasMany(ApplicationReference::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(ApplicationStatusHistory::class);
    }

    public function internalNotes(): HasMany
    {
        return $this->hasMany(ApplicationInternalNote::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ApplicantDocument::class, 'membership_application_id');
    }

    public function invoice(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function membership(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Membership::class);
    }
}
