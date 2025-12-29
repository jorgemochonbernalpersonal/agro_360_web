<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Plot;

class RemoteSensingAlertNotification extends Notification
{
    use Queueable;

    private Plot $plot;
    private float $currentNdvi;
    private float $threshold;

    /**
     * Create a new notification instance.
     */
    public function __construct(Plot $plot, float $currentNdvi, float $threshold)
    {
        $this->plot = $plot;
        $this->currentNdvi = $currentNdvi;
        $this->threshold = $threshold;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];
        
        if ($this->plot->alert_email_enabled) {
            $channels[] = 'mail';
        }
        
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('⚠️ Alerta NDVI: ' . $this->plot->name)
                    ->greeting('Hola ' . $notifiable->name . ',')
                    ->line("Hemos detectado un valor bajo de vigor (NDVI) en tu parcela **{$this->plot->name}**.")
                    ->line("Valor actual: **{$this->currentNdvi}**")
                    ->line("Tu umbral de alerta: **{$this->threshold}**")
                    ->action('Ver detalle en Teledetección', url('/viticulturist/remote-sensing?selectedPlotId='.$this->plot->id))
                    ->line('Te recomendamos revisar la parcela para identificar posibles problemas.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Alerta de Vigor (NDVI)',
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
