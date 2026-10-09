<?php

namespace App\Enums;

enum ApplicantDocumentStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case ReplacementRequested = 'replacement_requested';

    public function label(): string
    {
        return str($this->value)->replace('_', ' ')->title()->toString();
    }
}
