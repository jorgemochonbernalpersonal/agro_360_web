<?php

namespace App\Notifications;

use App\Models\HarvestDelivery;
use App\Notifications\Concerns\RespectsPreferences;
use App\Support\AppLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class HarvestDeliveryDisputedNotification extends Notification implements ShouldQueue
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
        $viticulturist = $delivery->viticulturist;
        $planting = $delivery->plotPlanting;
        $harvest = $delivery->harvest;

        $variety = $planting?->grapeVariety->name ?? $planting->name ?? '—';
        $plot = $planting?->plot->name ?? '—';
        $showUrl = AppLink::url(
            $harvest
                ? route('winery.grape-reception.show', $harvest->id)
                : route('winery.grape-reception.index'),
            'agro365://home'
        );

        if (app()->environment('production')) {
            $showUrl = str_replace('http://', 'https://', $showUrl);
        }

        return (new MailMessage)
            ->subject(__('Reclamación de entrega — ').$viticulturist->name.' · '.$variety)
            ->greeting(__('Hola :name', ['name' => $notifiable->name ?: '']))
            ->line(__('El viticultor **').($viticulturist->name ?? '—').'** ha enviado una reclamación sobre una diferencia en la entrega de uva.')
            ->line(new HtmlString(
                '<div style="background-color:#fefce8;border:1px solid #fde68a;padding:16px;border-radius:8px;margin:16px 0;">
                    <p style="margin:0 0 8px 0;"><strong>Variedad:</strong> '.e($variety).'</p>
                    <p style="margin:0 0 8px 0;"><strong>Parcela:</strong> '.e($plot).'</p>
                    <p style="margin:0 0 8px 0;"><strong>Añada:</strong> '.e($delivery->vintage_year).'</p>
                    <p style="margin:0 0 8px 0;"><strong>Kg declarados por el viticultor:</strong> '.number_format((float) $delivery->delivered_kg, 0).' kg</p>
                    <p style="margin:0 0 8px 0;"><strong>Kg registrados por bodega:</strong> '.($harvest ? number_format((float) $harvest->total_weight, 0).' kg' : '—').'</p>
                    <p style="margin:0 0 8px 0;"><strong>Diferencia:</strong> '.number_format((float) $delivery->discrepancy_kg, 0).' kg ('.$delivery->discrepancyPercentage().'%)</p>
                    <hr style="border:none;border-top:1px solid #fde68a;margin:12px 0;">
                    <p style="margin:0 0 4px 0;"><strong>Nota del viticultor:</strong></p>
                    <p style="margin:0;white-space:pre-wrap;">'.nl2br(e($delivery->dispute_note)).'</p>
                 </div>'
            ))
            ->action(__('Ver recepción'), $showUrl)
            ->line(__('Por favor, revisa los datos y contacta con el viticultor si es necesario.'))
            ->salutation(__('Saludos,\nAgro365'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'delivery_id' => $this->delivery->id,
            'viticulturist_id' => $this->delivery->viticulturist_id,
            'harvest_id' => $this->delivery->harvest_id,
            'discrepancy_kg' => $this->delivery->discrepancy_kg,
        ];
    }
}
