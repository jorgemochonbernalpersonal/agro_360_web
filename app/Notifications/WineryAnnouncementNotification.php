<?php

namespace App\Notifications;

use App\Models\WineryAnnouncement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WineryAnnouncementNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public WineryAnnouncement $announcement) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $winery = $this->announcement->winery;
        $typeLabel = $this->announcement->typeLabel();

        return (new MailMessage)
            ->subject("[{$typeLabel}] {$this->announcement->title}")
            ->greeting("Aviso de {$winery->name}")
            ->line($this->announcement->body)
            ->action(__('Ver en Agro365'), route('viticulturist.announcements'))
            ->salutation('— Equipo Agro365');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'announcement_id' => $this->announcement->id,
            'winery_id' => $this->announcement->winery_id,
            'winery_name' => $this->announcement->winery->name,
            'title' => $this->announcement->title,
            'type' => $this->announcement->type,
        ];
    }
}
