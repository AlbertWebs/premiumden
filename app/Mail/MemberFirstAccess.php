<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MemberFirstAccess extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $member, public string $setupUrl, public string $expiresAt) {}

    public function envelope(): Envelope { return new Envelope(subject: 'Your Premium Business Den membership is active'); }
    public function content(): Content { return new Content(view: 'emails.member-first-access'); }
}
