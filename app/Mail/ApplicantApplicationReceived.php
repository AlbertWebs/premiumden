<?php

namespace App\Mail;

use App\Models\MembershipApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicantApplicationReceived extends Mailable
{
    use Queueable, SerializesModels;
    public function __construct(public MembershipApplication $application, public string $statusUrl) {}
    public function envelope(): Envelope { return new Envelope(subject: 'We received your Premium Business Den application'); }
    public function content(): Content { return new Content(view: 'emails.application-received'); }
}
