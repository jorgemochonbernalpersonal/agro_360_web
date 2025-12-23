<?php

namespace App\Notifications;

use App\Models\OfficialReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReportGeneratedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public OfficialReport $report
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'report_id' => $this->report->id,
            'report_type' => $this->report->report_type,
            'report_type_name' => $this->report->report_type_name,
            'report_icon' => $this->report->report_icon,
            'verification_code' => $this->report->verification_code,
            'pdf_exists' => $this->report->pdfExists(),
            'period' => $this->report->period_start->format('d/m/Y') . ' - ' . $this->report->period_end->format('d/m/Y'),
            'message' => '✅ Tu informe ha sido generado exitosamente',
            'action_url' => route('viticulturist.official-reports.index'),
            'action_text' => 'Ver Informes',
            'download_url' => route('viticulturist.official-reports.download', $this->report),
        ];
    }
}
