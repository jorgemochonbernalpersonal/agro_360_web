<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\WineryJoinRequest;
use App\Notifications\Concerns\RespectsPreferences;
use App\Support\AppLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WineryJoinRespondedNotification extends Notification implements ShouldQueue
{
    use Queueable, RespectsPreferences;

    public function __construct(
        protected User   $winery,
        protected string $status, // approved | rejected
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
        $url = AppLink::url(route('viticulturist.winery-requests.index'), 'agro365://home');

        if (app()->environment('production')) {
            $url = str_replace('http://', 'https://', $url);
        }

        $approved = $this->status === WineryJoinRequest::STATUS_APPROVED;

        $subject = $approved
            ? __('Solicitud de vinculación aprobada — ') . $this->winery->name
            : __('Solicitud de vinculación rechazada — ') . $this->winery->name;

        $body = $approved
            ? __('La bodega **') . $this->winery->name . __('** ha aprobado tu solicitud. Ya estás vinculado a su bodega.')
            : __('La bodega **') . $this->winery->name . __('** ha rechazado tu solicitud de vinculación.');

        return (new MailMessage)
            ->subject($subject)
            ->greeting(__('Hola :name', ['name' => $notifiable->name ?: '']))
            ->line($body)
            ->action(__('Ver mis bodegas'), $url)
            ->salutation(__('Saludos,\nAgro365'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'winery_id'   => $this->winery->id,
            'winery_name' => $this->winery->name,
            'status'      => $this->status,
        ];
    }
}
