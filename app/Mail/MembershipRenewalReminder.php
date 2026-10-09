<?php

namespace App\Mail;

use App\Models\Membership;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MembershipRenewalReminder extends Mailable
{
    use Queueable, SerializesModels;
    public function __construct(public Membership $membership, public string $supportEmail) {}
    public function envelope(): Envelope { return new Envelope(subject: 'Your Premium Business Den membership renewal'); }
    public function content(): Content { return new Content(view: 'emails.membership-renewal-reminder'); }
}
