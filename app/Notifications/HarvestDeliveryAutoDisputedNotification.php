<?php

namespace App\Notifications;

use App\Models\HarvestDelivery;
use App\Notifications\Concerns\RespectsPreferences;
use App\Support\AppLink;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class HarvestDeliveryAutoDisputedNotification extends Notification
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
        $delivery = $this->delivery;
        $planting = $delivery->plotPlanting;
        $harvest  = $delivery->harvest;

        $variety     = $planting?->grapeVariety?->name ?? $planting?->name ?? '—';
        $plot        = $planting?->plot?->name ?? '—';
        $winery      = $harvest?->winery?->name ?? '—';
        $discPct     = $delivery->discrepancyPercentage();
        $showUrl     = AppLink::url(
            route('viticulturist.harvests.show', [
                'planting' => $delivery->plot_planting_id,
                'vintage'  => $delivery->vintage_year,
            ]),
            "agro365://harvests/{$delivery->plot_planting_id}/{$delivery->vintage_year}"
        );

        if (app()->environment('production')) {
            $showUrl = str_replace('http://', 'https://', $showUrl);
        }

        return (new MailMessage)
            ->subject('Diferencia en entrega — ' . $variety . ' · ' . $delivery->vintage_year)
            ->greeting('Hola ' . ($notifiable->name ?: ''))
            ->line('La bodega **' . $winery . '** ha registrado una recepción con una diferencia significativa respecto a tu declaración de entrega.')
            ->line(new HtmlString(
                '<div style="background-color:#fffbeb;border:1px solid #fde68a;padding:16px;border-radius:8px;margin:16px 0;">
                    <p style="margin:0 0 8px 0;"><strong>Variedad:</strong> ' . e($variety) . '</p>
                    <p style="margin:0 0 8px 0;"><strong>Parcela:</strong> ' . e($plot) . '</p>
                    <p style="margin:0 0 8px 0;"><strong>Añada:</strong> ' . e($delivery->vintage_year) . '</p>
                    <p style="margin:0 0 8px 0;"><strong>Kg declarados por ti:</strong> ' . number_format((float) $delivery->delivered_kg, 0) . ' kg</p>
                    <p style="margin:0 0 8px 0;"><strong>Kg registrados por bodega:</strong> ' . ($harvest ? number_format((float) $harvest->total_weight, 0) . ' kg' : '—') . '</p>
                    <p style="margin:0;color:#92400e;font-weight:bold;">Diferencia: ' . number_format((float) $delivery->discrepancy_kg, 0) . ' kg' . ($discPct ? ' (' . $discPct . '%)' : '') . '</p>
                 </div>'
            ))
            ->action('Ver detalle y reclamar', $showUrl)
            ->line('Puedes revisar los datos y enviar una reclamación desde tu panel de vendimias si crees que hay un error.')
            ->salutation("Saludos,\nAgro365");
    }

    public function toArray(object $notifiable): array
    {
        $delivery = $this->delivery;
        $variety  = $delivery->plotPlanting?->grapeVariety?->name ?? $delivery->plotPlanting?->name ?? '—';
        $winery   = $delivery->harvest?->winery?->name ?? '—';

        return [
            'type'        => 'delivery_disputed',
            'icon'        => 'exclamation-triangle',
            'color'       => 'orange',
            'title'       => 'Diferencia detectada en entrega',
            'body'        => "La bodega {$winery} registró una diferencia de " . number_format((float) $delivery->discrepancy_kg, 0) . " kg en tu entrega de {$variety} ({$delivery->vintage_year}).",
            'link'        => route('viticulturist.harvests.show', [
                'planting' => $delivery->plot_planting_id,
                'vintage'  => $delivery->vintage_year,
            ]),
            'delivery_id' => $delivery->id,
        ];
    }
}
