<?php

namespace App\Notifications;

use App\Models\Plot;
use App\Notifications\Concerns\RespectsPreferences;
use App\Support\AppLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RemoteSensingAlertNotification extends Notification
{
    use Queueable, RespectsPreferences;

    private Plot $plot;
    private float $currentNdvi;
    private float $threshold;
    private bool  $emailEnabled;

    public function __construct(Plot $plot, float $currentNdvi, float $threshold, bool $emailEnabled = false)
    {
        $this->plot         = $plot;
        $this->currentNdvi  = $currentNdvi;
        $this->threshold    = $threshold;
        $this->emailEnabled = $emailEnabled;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function notificationCategory(): string
    {
        return 'remote_sensing';
    }

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($this->emailEnabled) {
            $channels[] = 'mail';
        }

        return $this->filterChannelsByPreferences($notifiable, $channels);
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('⚠️ Alerta NDVI: ' . $this->plot->name)
                    ->greeting(__('Hola ') . $notifiable->name . ',')
                    ->line("Hemos detectado un valor bajo de vigor (NDVI) en tu parcela **{$this->plot->name}**.")
                    ->line("Valor actual: **{$this->currentNdvi}**")
                    ->line("Tu umbral de alerta: **{$this->threshold}**")
                    ->action(__('Ver detalle en Teledetección'), AppLink::url(url('/viticulturist/remote-sensing?selectedPlotId='.$this->plot->id), 'agro365://plots/' . $this->plot->id))
                    ->line(__('Te recomendamos revisar la parcela para identificar posibles problemas.'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => __('Alerta de Vigor (NDVI)'),
            'message' => "La parcela {$this->plot->name} tiene un NDVI de {$this->currentNdvi} (bajo el umbral {$this->threshold})",
            'plot_id' => $this->plot->id,
            'ndvi' => $this->currentNdvi,
            'action_url' => '/viticulturist/remote-sensing?selectedPlotId='.$this->plot->id,
            'type' => 'alert',
            'icon' => '📉',
            'color' => 'text-red-500',
        ];
    }
}
