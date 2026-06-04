<?php

namespace App\Notifications;

use App\Models\User;
use App\Notifications\Concerns\RespectsPreferences;
use App\Support\AppLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NotebookAccessRequestedNotification extends Notification implements ShouldQueue
{
    use Queueable, RespectsPreferences;

    public function __construct(
        protected User $winery,
    ) {}

    public function notificationCategory(): string
    {
        return 'notebook_access';
    }

    public function via(object $notifiable): array
    {
        return $this->filterChannelsByPreferences($notifiable, ['mail', 'database']);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = AppLink::url(route('viticulturist.winery-access.index'), 'agro365://home');

        if (app()->environment('production')) {
            $url = str_replace('http://', 'https://', $url);
        }

        return (new MailMessage)
            ->subject(__('Nueva solicitud de acceso al cuaderno — ').$this->winery->name)
            ->greeting(__('Hola :name', ['name' => $notifiable->name ?: '']))
            ->line(__('La bodega **').$this->winery->name.'** ha solicitado acceso a tu cuaderno de campo digital.')
            ->line(__('Puedes aprobar o rechazar esta solicitud desde tu panel de control. Recuerda que tú tienes el control total: puedes revocar el acceso en cualquier momento.'))
            ->action(__('Ver solicitudes de acceso'), $url)
            ->line(__('Si no conoces esta bodega, simplemente rechaza la solicitud.'))
            ->salutation(__('Saludos,\nAgro365'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'winery_id' => $this->winery->id,
            'winery_name' => $this->winery->name,
        ];
    }
}
