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

class HarvestDeliveryDelinkedNotification extends Notification implements ShouldQueue
{
    use Queueable, RespectsPreferences;

    public function __construct(
        protected HarvestDelivery $delivery,
        protected float $oldKg,
        protected float $newKg
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
        $viticulturist = $delivery->viticulturist;

        $variety = $planting?->grapeVariety?->name ?? $planting?->name ?? '—';
        $plot = $planting?->plot?->name ?? '—';
        $showUrl = AppLink::url(route('winery.grape-reception.index'), 'agro365://home');

        if (app()->environment('production')) {
            $showUrl = str_replace('http://', 'https://', $showUrl);
        }

        return (new MailMessage)
            ->subject(__('Entrega modificada por el viticultor — ').$variety.' · '.$delivery->vintage_year)
            ->greeting(__('Hola :name', ['name' => $notifiable->name ?: '']))
            ->line(__('El viticultor **').($viticulturist?->name ?? '—').'** ha modificado los kg declarados en una entrega que ya estaba confirmada. La confirmación ha quedado **desvinculada** y la entrega vuelve a estado pendiente.')
            ->line(new HtmlString(
                '<div style="background-color:#fffbeb;border:1px solid #fde68a;padding:16px;border-radius:8px;margin:16px 0;">
                    <p style="margin:0 0 8px 0;"><strong>Variedad:</strong> '.e($variety).'</p>
                    <p style="margin:0 0 8px 0;"><strong>Parcela:</strong> '.e($plot).'</p>
                    <p style="margin:0 0 8px 0;"><strong>Añada:</strong> '.e($delivery->vintage_year).'</p>
                    <p style="margin:0 0 8px 0;"><strong>Kg anteriores:</strong> '.number_format($this->oldKg, 0).' kg</p>
                    <p style="margin:0;"><strong>Kg nuevos declarados:</strong> '.number_format($this->newKg, 0).' kg</p>
                 </div>'
            ))
            ->action(__('Ver recepciones'), $showUrl)
            ->line(__('Tu recepción sigue registrada. La declaración del viticultor queda pendiente de re-vinculación.'))
            ->salutation(__('Saludos,\nAgro365'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'delivery_id' => $this->delivery->id,
            'viticulturist_id' => $this->delivery->viticulturist_id,
            'old_kg' => $this->oldKg,
            'new_kg' => $this->newKg,
        ];
    }
}
