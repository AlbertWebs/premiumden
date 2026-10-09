<?php

namespace App\Mail;

use App\Models\MembershipApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationDraftSaved extends Mailable
{
    use Queueable, SerializesModels;
    public function __construct(public MembershipApplication $application, public string $resumeUrl, public string $expiresAt) {}
    public function envelope(): Envelope { return new Envelope(subject: 'Your Premium Business Den application draft'); }
    public function content(): Content { return new Content(view: 'emails.application-draft-saved'); }
}
