<?php

namespace App\Notifications;

use App\Models\User;
use App\Notifications\Concerns\RespectsPreferences;
use App\Support\AppLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WineryJoinRequestedNotification extends Notification implements ShouldQueue
{
    use Queueable, RespectsPreferences;

    public function __construct(
        protected User $viticulturist,
    ) {}

    public function notificationCategory(): string
    {
        return 'winery_join';
    }

    public function via(object $notifiable): array
    {
        return $this->filterChannelsByPreferences($notifiable, ['mail', 'database']);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = AppLink::url(route('winery.viticulturists.index'), 'agro365://home');

        if (app()->environment('production')) {
            $url = str_replace('http://', 'https://', $url);
        }

        return (new MailMessage)
            ->subject(__('Nueva solicitud de vinculación — ').$this->viticulturist->name)
            ->greeting(__('Hola :name', ['name' => $notifiable->name ?: '']))
            ->line(__('El viticultor **').$this->viticulturist->name.'** ha solicitado vincularse a tu bodega.')
            ->line(__('Puedes aprobar o rechazar la solicitud desde el panel de viticultores.'))
            ->action(__('Ver solicitudes'), $url)
            ->salutation(__('Saludos,\nAgro365'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'viticulturist_id' => $this->viticulturist->id,
            'viticulturist_name' => $this->viticulturist->name,
        ];
    }
}
