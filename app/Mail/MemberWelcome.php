<?php

namespace App\Mail;

use App\Models\Membership;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MemberWelcome extends Mailable
{
    use Queueable, SerializesModels;
    public function __construct(public Membership $membership, public string $portalUrl, public string $supportEmail) {}
    public function envelope(): Envelope { return new Envelope(subject: 'Welcome to Premium Business Den'); }
    public function content(): Content { return new Content(view: 'emails.member-welcome'); }
}
