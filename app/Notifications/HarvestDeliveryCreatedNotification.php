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

class HarvestDeliveryCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable, RespectsPreferences;

    public function __construct(
        protected HarvestDelivery $delivery
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
        $delivery       = $this->delivery;
        $planting       = $delivery->plotPlanting;
        $viticulturist  = $delivery->viticulturist;

        $variety  = $planting?->grapeVariety?->name ?? $planting?->name ?? '—';
        $plot     = $planting?->plot?->name ?? '—';
        $showUrl  = AppLink::url(route('winery.grape-reception.index'), 'agro365://home');

        if (app()->environment('production')) {
            $showUrl = str_replace('http://', 'https://', $showUrl);
        }

        return (new MailMessage)
            ->subject('Nueva entrega declarada — ' . $variety . ' · ' . $delivery->vintage_year)
            ->greeting('Hola ' . ($notifiable->name ?: ''))
            ->line('El viticultor **' . ($viticulturist?->name ?? '—') . '** ha registrado una nueva entrega de uva.')
            ->line(new HtmlString(
                '<div style="background-color:#eff6ff;border:1px solid #bfdbfe;padding:16px;border-radius:8px;margin:16px 0;">
                    <p style="margin:0 0 8px 0;"><strong>Viticultor:</strong> ' . e($viticulturist?->name ?? '—') . '</p>
                    <p style="margin:0 0 8px 0;"><strong>Variedad:</strong> ' . e($variety) . '</p>
                    <p style="margin:0 0 8px 0;"><strong>Parcela:</strong> ' . e($plot) . '</p>
                    <p style="margin:0 0 8px 0;"><strong>Añada:</strong> ' . e($delivery->vintage_year) . '</p>
                    <p style="margin:0 0 8px 0;"><strong>Kg declarados:</strong> ' . number_format((float) $delivery->delivered_kg, 0) . ' kg</p>
                    <p style="margin:0 0 8px 0;"><strong>Fecha de entrega:</strong> ' . e($delivery->delivery_date) . '</p>
                    ' . ($delivery->ticket_number ? '<p style="margin:0 0 8px 0;"><strong>Albarán:</strong> ' . e($delivery->ticket_number) . '</p>' : '') . '
                 </div>'
            ))
            ->action('Ir a recepciones de bodega', $showUrl)
            ->line('Registra la recepción en tu panel para confirmar o gestionar la diferencia de peso.')
            ->salutation("Saludos,\nAgro365");
    }

    public function toArray(object $notifiable): array
    {
        return [
            'delivery_id'      => $this->delivery->id,
            'viticulturist_id' => $this->delivery->viticulturist_id,
            'delivered_kg'     => $this->delivery->delivered_kg,
            'vintage_year'     => $this->delivery->vintage_year,
        ];
    }
}
