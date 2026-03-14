<?php

namespace App\Notifications;

use App\Models\HarvestDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class HarvestDeliveryMatchedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected HarvestDelivery $delivery
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $delivery = $this->delivery;
        $planting = $delivery->plotPlanting;
        $harvest  = $delivery->harvest;

        $variety = $planting?->grapeVariety?->name ?? $planting?->name ?? '—';
        $plot    = $planting?->plot?->name ?? '—';
        $winery  = $harvest?->winery?->name ?? '—';
        $showUrl = route('viticulturist.harvests.show', [
            'planting' => $delivery->plot_planting_id,
            'vintage'  => $delivery->vintage_year,
        ]);

        if (app()->environment('production')) {
            $showUrl = str_replace('http://', 'https://', $showUrl);
        }

        return (new MailMessage)
            ->subject('Entrega confirmada — ' . $variety . ' · ' . $delivery->vintage_year)
            ->greeting('Hola ' . ($notifiable->name ?: ''))
            ->line('La bodega **' . $winery . '** ha confirmado la recepción de tu entrega de uva. Las cantidades coinciden.')
            ->line(new HtmlString(
                '<div style="background-color:#f0fdf4;border:1px solid #bbf7d0;padding:16px;border-radius:8px;margin:16px 0;">
                    <p style="margin:0 0 8px 0;"><strong>Variedad:</strong> ' . e($variety) . '</p>
                    <p style="margin:0 0 8px 0;"><strong>Parcela:</strong> ' . e($plot) . '</p>
                    <p style="margin:0 0 8px 0;"><strong>Añada:</strong> ' . e($delivery->vintage_year) . '</p>
                    <p style="margin:0 0 8px 0;"><strong>Kg declarados:</strong> ' . number_format((float) $delivery->delivered_kg, 0) . ' kg</p>
                    <p style="margin:0 0 8px 0;"><strong>Kg recibidos por bodega:</strong> ' . ($harvest ? number_format((float) $harvest->total_weight, 0) . ' kg' : '—') . '</p>
                    <p style="margin:0;color:#166534;font-weight:bold;">✓ Cantidades confirmadas sin diferencia significativa</p>
                 </div>'
            ))
            ->action('Ver detalle de cosecha', $showUrl)
            ->salutation("Saludos,\nAgro365");
    }

    public function toArray(object $notifiable): array
    {
        $delivery = $this->delivery;
        $variety  = $delivery->plotPlanting?->grapeVariety?->name ?? $delivery->plotPlanting?->name ?? '—';
        $winery   = $delivery->harvest?->winery?->name ?? '—';

        return [
            'type'        => 'delivery_matched',
            'icon'        => 'check-circle',
            'color'       => 'green',
            'title'       => 'Entrega confirmada por la bodega',
            'body'        => "La bodega {$winery} ha confirmado tu entrega de {$variety} ({$delivery->vintage_year}).",
            'link'        => route('viticulturist.harvests.show', [
                'planting' => $delivery->plot_planting_id,
                'vintage'  => $delivery->vintage_year,
            ]),
            'delivery_id' => $delivery->id,
        ];
    }
}
