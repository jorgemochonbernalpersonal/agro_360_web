<?php

namespace App\Notifications;

use App\Models\HarvestDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class HarvestDeliveryResolvedNotification extends Notification
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

        $variety  = $planting?->grapeVariety?->name ?? $planting?->name ?? '—';
        $plot     = $planting?->plot?->name ?? '—';
        $winery   = $harvest?->winery?->name ?? '—';
        $showUrl  = route('viticulturist.harvests.show', [
            'planting' => $delivery->plot_planting_id,
            'vintage'  => $delivery->vintage_year,
        ]);

        if (app()->environment('production')) {
            $showUrl = str_replace('http://', 'https://', $showUrl);
        }

        return (new MailMessage)
            ->subject('Reclamación resuelta — ' . $variety . ' · ' . $delivery->vintage_year)
            ->greeting('Hola ' . ($notifiable->name ?: ''))
            ->line('La bodega **' . $winery . '** ha respondido a tu reclamación sobre la diferencia en la entrega de uva.')
            ->line(new HtmlString(
                '<div style="background-color:#f0fdf4;border:1px solid #bbf7d0;padding:16px;border-radius:8px;margin:16px 0;">
                    <p style="margin:0 0 8px 0;"><strong>Variedad:</strong> ' . e($variety) . '</p>
                    <p style="margin:0 0 8px 0;"><strong>Parcela:</strong> ' . e($plot) . '</p>
                    <p style="margin:0 0 8px 0;"><strong>Añada:</strong> ' . e($delivery->vintage_year) . '</p>
                    <p style="margin:0 0 8px 0;"><strong>Kg declarados:</strong> ' . number_format((float) $delivery->delivered_kg, 0) . ' kg</p>
                    <p style="margin:0 0 8px 0;"><strong>Kg recibidos por bodega:</strong> ' . ($harvest ? number_format((float) $harvest->total_weight, 0) . ' kg' : '—') . '</p>
                    <p style="margin:0 0 8px 0;"><strong>Diferencia:</strong> ' . number_format((float) $delivery->discrepancy_kg, 0) . ' kg (' . $delivery->discrepancyPercentage() . '%)</p>
                    <hr style="border:none;border-top:1px solid #bbf7d0;margin:12px 0;">
                    <p style="margin:0 0 4px 0;"><strong>Respuesta de la bodega:</strong></p>
                    <p style="margin:0;white-space:pre-wrap;">' . nl2br(e($delivery->dispute_resolution_note)) . '</p>
                 </div>'
            ))
            ->action('Ver detalle de cosecha', $showUrl)
            ->line('Si tienes dudas adicionales, contacta directamente con la bodega.')
            ->salutation("Saludos,\nAgro365");
    }

    public function toArray(object $notifiable): array
    {
        $delivery = $this->delivery;
        $variety  = $delivery->plotPlanting?->grapeVariety?->name ?? $delivery->plotPlanting?->name ?? '—';
        $winery   = $delivery->harvest?->winery?->name ?? '—';

        return [
            'type'        => 'delivery_resolved',
            'icon'        => 'shield-check',
            'color'       => 'blue',
            'title'       => 'Reclamación resuelta',
            'body'        => "La bodega {$winery} ha respondido a tu reclamación sobre la entrega de {$variety} ({$delivery->vintage_year}).",
            'link'        => route('viticulturist.harvests.show', [
                'planting' => $delivery->plot_planting_id,
                'vintage'  => $delivery->vintage_year,
            ]),
            'delivery_id' => $delivery->id,
        ];
    }
}
