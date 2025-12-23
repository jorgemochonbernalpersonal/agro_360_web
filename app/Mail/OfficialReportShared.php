<?php

namespace App\Mail;

use App\Models\OfficialReport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class OfficialReportShared extends Mailable
{
    use Queueable, SerializesModels;

    public $report;
    public $customMessage; // Renombrado de $message para evitar conflicto con variable reservada de Laravel
    public $senderName;

    /**
     * Create a new message instance.
     */
    public function __construct(OfficialReport $report, string $customMessage, string $senderName)
    {
        $this->report = $report;
        $this->customMessage = $customMessage;
        $this->senderName = $senderName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '📄 ' . $this->senderName . ' te ha compartido un informe oficial',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.official-report-shared',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        // Adjuntar el PDF
        if ($this->report->pdf_path && $this->report->pdfExists()) {
            // Si el path es relativo (usando Storage), usar fromStorage
            if (!str_starts_with($this->report->pdf_path, storage_path())) {
                return [
                    Attachment::fromStorageDisk('local', $this->report->pdf_path)
                        ->as($this->report->pdf_filename)
                        ->withMime('application/pdf'),
                ];
            } else {
                // Si es path absoluto, usar fromPath
                return [
                    Attachment::fromPath($this->report->pdf_path)
                        ->as($this->report->pdf_filename)
                        ->withMime('application/pdf'),
                ];
            }
        }
        
        return [];
    }
}
