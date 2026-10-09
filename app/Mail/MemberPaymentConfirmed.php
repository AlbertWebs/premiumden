<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MemberPaymentConfirmed extends Mailable
{
    use Queueable, SerializesModels;
    public function __construct(public Payment $payment) {}
    public function envelope(): Envelope { return new Envelope(subject: 'Payment received by Premium Business Den'); }
    public function content(): Content { return new Content(view: 'emails.payment-confirmed'); }
}
