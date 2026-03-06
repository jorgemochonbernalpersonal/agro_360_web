<?php

namespace App\Mail;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public string  $senderName = ''
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->invoice->invoice_number
            ? "Factura {$this->invoice->invoice_number}"
            : "Albarán {$this->invoice->delivery_note_code}";

        if ($this->senderName) {
            $subject .= " de {$this->senderName}";
        }

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.invoice');
    }

    public function attachments(): array
    {
        $attachments = [];

        // Adjuntar albarán siempre (es el documento base)
        if ($this->invoice->delivery_note_code) {
            $pdfAlbaran = Pdf::loadView('reports.delivery-note', [
                'invoice' => $this->invoice,
                'user'    => $this->invoice->user->load('profile.province'),
            ])
            ->setPaper('A4', 'portrait')
            ->setOption('defaultFont', 'DejaVu Sans')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false);

            $code     = str_replace(['/', '\\', ' '], '-', $this->invoice->delivery_note_code);
            $attachments[] = Attachment::fromData(fn () => $pdfAlbaran->output(), "albaran_{$code}.pdf")
                ->withMime('application/pdf');
        }

        // Adjuntar factura si ya está emitida
        if ($this->invoice->invoice_number) {
            $pdfFactura = Pdf::loadView('reports.invoice', [
                'invoice' => $this->invoice,
                'user'    => $this->invoice->user->load('profile.province'),
            ])
            ->setPaper('A4', 'portrait')
            ->setOption('defaultFont', 'DejaVu Sans')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false);

            $num = str_replace(['/', '\\', ' '], '-', $this->invoice->invoice_number);
            $attachments[] = Attachment::fromData(fn () => $pdfFactura->output(), "factura_{$num}.pdf")
                ->withMime('application/pdf');
        }

        return $attachments;
    }
}
