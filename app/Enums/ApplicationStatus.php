<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case Vetting = 'vetting';
    case Approved = 'approved';
    case DocumentsRequired = 'documents_required';
    case DocumentsReceived = 'documents_received';
    case InvoiceIssued = 'invoice_issued';
    case AwaitingPayment = 'awaiting_payment';
    case Paid = 'paid';
    case MembershipActivated = 'membership_activated';
    case Rejected = 'rejected';
    case Suspended = 'suspended';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return str($this->value)->replace('_', ' ')->title()->toString();
    }
}
