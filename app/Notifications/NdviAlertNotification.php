<?php

namespace App\Notifications;

use App\Models\Plot;
use App\Support\AppLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NdviAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Plot $plot,
        public float $currentNdvi,
        public float $threshold
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Alerta NDVI - {$this->plot->name}")
            ->warning()
            ->greeting('⚠️ Alerta de Salud del Viñedo')
            ->line("La parcela **{$this->plot->name}** presenta un valor NDVI por debajo del umbral establecido.")
            ->line("- NDVI actual: {$this->currentNdvi}")
            ->line("- Umbral configurado: {$this->threshold}")
            ->line('Un NDVI bajo puede indicar estrés hídrico, plagas o enfermedades.')
            ->action('Ver parcela', AppLink::url(route('plots.show', $this->plot), 'agro365://plots/' . $this->plot->id))
            ->line('Recomendamos inspeccionar la parcela lo antes posible.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'plot_id' => $this->plot->id,
            'plot_name' => $this->plot->name,
            'current_ndvi' => $this->currentNdvi,
            'threshold' => $this->threshold,
            'alert_type' => 'ndvi_low',
        ];
    }
}
