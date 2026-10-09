<?php

namespace App\Mail;

use App\Models\MembershipApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicantApplicationUpdate extends Mailable
{
    use Queueable, SerializesModels;
    public function __construct(public MembershipApplication $application, public string $updateText, public string $statusUrl) {}
    public function envelope(): Envelope
    {
        $status = $this->application->status?->label() ?? 'Updated';

        return new Envelope(subject: $status.' · Premium Business Den application update');
    }
    public function content(): Content { return new Content(view: 'emails.application-update'); }
}
