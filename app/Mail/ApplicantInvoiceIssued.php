<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicantInvoiceIssued extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Invoice $invoice, public string $invoiceUrl) {}
    public function envelope(): Envelope { return new Envelope(subject: 'Your Premium Business Den membership invoice'); }
    public function content(): Content { return new Content(view: 'emails.invoice-issued'); }
}
