<?php

namespace App\Notifications;

use App\Models\HarvestDelivery;
use App\Notifications\Concerns\RespectsPreferences;
use App\Support\AppLink;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class HarvestDeliveryDeletedNotification extends Notification implements ShouldQueue
{
    use Queueable, RespectsPreferences;

    public function __construct(
        protected string $viticulturistName,
        protected string $variety,
        protected string $plot,
        protected int    $vintageYear,
        protected float  $declaredKg,
        protected string $wineryReceptionUrl
    ) {}

    public function notificationCategory(): string
    {
        return 'harvest_delivery';
    }

    public function via(object $notifiable): array
    {
        return $this->filterChannelsByPreferences($notifiable, ['mail', 'database']);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Declaración de entrega eliminada — ') . $this->variety . ' · ' . $this->vintageYear)
            ->greeting(__('Hola :name', ['name' => $notifiable->name ?: '']))
            ->line(__('El viticultor **') . $this->viticulturistName . '** ha eliminado su declaración de entrega de uva. Tu recepción sigue registrada, pero ya no está vinculada a ninguna declaración.')
            ->line(new HtmlString(
                '<div style="background-color:#fef2f2;border:1px solid #fecaca;padding:16px;border-radius:8px;margin:16px 0;">
                    <p style="margin:0 0 8px 0;"><strong>Variedad:</strong> ' . e($this->variety) . '</p>
                    <p style="margin:0 0 8px 0;"><strong>Parcela:</strong> ' . e($this->plot) . '</p>
                    <p style="margin:0 0 8px 0;"><strong>Añada:</strong> ' . e($this->vintageYear) . '</p>
                    <p style="margin:0;"><strong>Kg que declaraba el viticultor:</strong> ' . number_format($this->declaredKg, 0) . ' kg</p>
                 </div>'
            ))
            ->action(__('Ver recepciones'), AppLink::url($this->wineryReceptionUrl, 'agro365://home'))
            ->line(__('Tu recepción permanece activa. Puedes contactar con el viticultor si necesitas aclaración.'))
            ->salutation(__('Saludos,\nAgro365'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'viticulturist_name' => $this->viticulturistName,
            'variety'            => $this->variety,
            'plot'               => $this->plot,
            'vintage_year'       => $this->vintageYear,
            'declared_kg'        => $this->declaredKg,
        ];
    }
}
