<?php

namespace App\Mail;

use App\Models\OfficialReport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReportGenerationFailedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public OfficialReport $report,
        public string $errorMessage
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '❌ Error al Generar Informe - ' . $this->report->report_type_name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.reports.failed',
        );
    }
}
