<?php

namespace App\Notifications;

use App\Models\OfficialReport;
use App\Notifications\Concerns\RespectsPreferences;
use App\Support\AppLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ReportFailedNotification extends Notification implements ShouldQueue
{
    use Queueable, RespectsPreferences;

    public function __construct(
        public OfficialReport $report,
        public string $errorMessage
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function notificationCategory(): string
    {
        return 'report';
    }

    public function via($notifiable): array
    {
        return $this->filterChannelsByPreferences($notifiable, ['database']);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'report_id' => $this->report->id,
            'report_type' => $this->report->report_type,
            'report_type_name' => $this->report->report_type_name ?? 'Informe Oficial',
            'report_icon' => $this->report->report_icon ?? '📄',
            'error_message' => $this->errorMessage,
            'message' => '❌ Error al generar el informe',
            'action_url' => AppLink::url(route('viticulturist.official-reports.create'), 'agro365://home'),
            'action_text' => __('Reintentar'),
        ];
    }
}
