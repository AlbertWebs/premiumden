<?php

namespace App\Mail;

use App\Models\MembershipApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ApplicantDocumentsRequested extends Mailable
{
    use Queueable;

    public function __construct(public MembershipApplication $application, public string $uploadUrl, public string $expiresAt) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Next step for your Premium Business Den application');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.documents-requested');
    }
}
