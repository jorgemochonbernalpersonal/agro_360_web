<?php

namespace App\Notifications;

use App\Models\User;
use App\Notifications\Concerns\RespectsPreferences;
use App\Support\AppLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifica al VITICULTOR cuando el supervisor lo asigna a una bodega.
 */
class ViticulturistAssignedToWineryNotification extends Notification implements ShouldQueue
{
    use Queueable, RespectsPreferences;

    public function __construct(
        protected User $winery,
        protected User $supervisor
    ) {}

    public function notificationCategory(): string
    {
        return 'do_requests';
    }

    public function via(object $notifiable): array
    {
        return $this->filterChannelsByPreferences($notifiable, ['mail', 'database']);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = AppLink::url(route('viticulturist.denomination.index'), 'agro365://home');

        if (app()->environment('production')) {
            $url = str_replace('http://', 'https://', $url);
        }

        return (new MailMessage)
            ->subject(__('Has sido asignado a la bodega ').$this->winery->name)
            ->greeting(__('Hola :name', ['name' => $notifiable->name ?: '']))
            ->line(__('La Denominación de Origen **').$this->supervisor->name.'** te ha asignado a la bodega **'.$this->winery->name.'**.')
            ->line(__('Podrás colaborar con esta bodega desde tu panel de Agro365.'))
            ->action(__('Ver mi denominación'), $url)
            ->salutation(__('Saludos,\nAgro365'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'winery_id' => $this->winery->id,
            'winery_name' => $this->winery->name,
            'supervisor_id' => $this->supervisor->id,
            'supervisor_name' => $this->supervisor->name,
            'icon' => '🏭',
            'message' => __('Asignado a ').$this->winery->name.' por '.$this->supervisor->name,
            'action_url' => route('viticulturist.denomination.index'),
            'action_text' => __('Ver mi DO'),
        ];
    }
}
