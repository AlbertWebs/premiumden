<?php

namespace App\Mail;

use App\Models\ApplicationReference;
use App\Models\MembershipApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MemberNominationReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public MembershipApplication $application, public ApplicationReference $reference, public string $acceptUrl, public string $declineUrl) {}
    public function envelope(): Envelope { return new Envelope(subject: 'Action requested: respond to a member nomination'); }
    public function content(): Content { return new Content(view: 'emails.member-nomination-received'); }
}
