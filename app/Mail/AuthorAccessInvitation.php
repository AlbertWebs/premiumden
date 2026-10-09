<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AuthorAccessInvitation extends Mailable
{
    use Queueable, SerializesModels;
    public function __construct(public User $author, public string $setupUrl, public string $expiresAt) {}
    public function envelope(): Envelope { return new Envelope(subject: 'Your Premium Business Den author access'); }
    public function content(): Content { return new Content(view: 'emails.author-invitation'); }
}
