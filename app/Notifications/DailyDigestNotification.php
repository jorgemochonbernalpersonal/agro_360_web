<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class DailyDigestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Collection $unreadNotifications
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $count = $this->unreadNotifications->count();

        $mail = (new MailMessage)
            ->subject("Resumen diario Agro365 — {$count} notificaciones")
            ->greeting("Buenos días, {$notifiable->name}")
            ->line("Tienes **{$count} notificaciones** pendientes de revisar:");

        foreach ($this->unreadNotifications->take(10) as $notification) {
            $data = $notification->data;
            $message = $data['message'] ?? $data['title'] ?? 'Nueva notificación';
            $icon = $data['icon'] ?? $data['report_icon'] ?? '•';
            $mail->line("{$icon} {$message}");
        }

        if ($count > 10) {
            $remaining = $count - 10;
            $mail->line("...y {$remaining} más.");
        }

        $mail->action('Ver todas en Agro365', url('/dashboard'))
             ->line('Puedes cambiar a notificaciones instantáneas en Configuración > Notificaciones.');

        return $mail;
    }
}
