<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceVerifiedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Invoice $invoice) {}

    public function envelope(): Envelope
    {
        $number = $this->invoice->invoice_number ?? $this->invoice->id;
        $subject = __('Factura verificada :number — :app', ['number' => $number, 'app' => config('app.name')]);

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'mail.invoice-verified');
    }
}
