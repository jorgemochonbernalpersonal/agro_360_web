<?php

namespace App\Notifications;

use App\Models\OfficialReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ReportFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public OfficialReport $report,
        public string $errorMessage
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
            'error_message' => $this->errorMessage,
            'message' => '❌ Error al generar el informe',
            'action_url' => route('viticulturist.official-reports.create'),
            'action_text' => 'Reintentar',
        ];
    }
}
