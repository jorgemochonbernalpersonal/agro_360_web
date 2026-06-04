<?php

namespace App\Notifications;

use App\Notifications\Concerns\RespectsPreferences;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExportReadyNotification extends Notification implements ShouldQueue
{
    use Queueable, RespectsPreferences;

    public function __construct(
        public string $filename,
        public string $exportType,
        public string $format
    ) {}

    public function notificationCategory(): string
    {
        return 'export';
    }

    public function via(object $notifiable): array
    {
        return $this->filterChannelsByPreferences($notifiable, ['mail', 'database']);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $typeLabels = [
            'activities' => __('Actividades'),
            'plots' => __('Parcelas'),
            'invoices' => __('Facturas'),
        ];

        $label = $typeLabels[$this->exportType] ?? $this->exportType;

        return (new MailMessage)
            ->subject(__('Exportación lista - :label', ['label' => $label]))
            ->greeting(__('📦 Tu exportación está lista'))
            ->line(__('La exportación de **:label** en formato **:format** se ha completado.', ['label' => $label, 'format' => $this->format]))
            ->action(__('Ir al panel'), url('/dashboard'))
            ->line(__('El archivo estará disponible durante 7 días.'));
    }

    public function toArray(object $notifiable): array
    {
        $typeLabels = [
            'activities' => __('Actividades'),
            'plots' => __('Parcelas'),
            'invoices' => __('Facturas'),
        ];

        return [
            'filename' => $this->filename,
            'export_type' => $this->exportType,
            'export_type_name' => $typeLabels[$this->exportType] ?? $this->exportType,
            'format' => $this->format,
            'message' => '📦 Tu exportación está lista para descargar',
            'download_url' => url('/storage/'.$this->filename),
        ];
    }
}
