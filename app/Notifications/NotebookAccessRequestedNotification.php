<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NotebookAccessRequestedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected User $winery,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('viticulturist.winery-access.index');

        if (app()->environment('production')) {
            $url = str_replace('http://', 'https://', $url);
        }

        return (new MailMessage)
            ->subject('Nueva solicitud de acceso al cuaderno — ' . $this->winery->name)
            ->greeting('Hola ' . ($notifiable->name ?: ''))
            ->line('La bodega **' . $this->winery->name . '** ha solicitado acceso a tu cuaderno de campo digital.')
            ->line('Puedes aprobar o rechazar esta solicitud desde tu panel de control. Recuerda que tú tienes el control total: puedes revocar el acceso en cualquier momento.')
            ->action('Ver solicitudes de acceso', $url)
            ->line('Si no conoces esta bodega, simplemente rechaza la solicitud.')
            ->salutation("Saludos,\nAgro365");
    }

    public function toArray(object $notifiable): array
    {
        return [
            'winery_id'   => $this->winery->id,
            'winery_name' => $this->winery->name,
        ];
    }
}
