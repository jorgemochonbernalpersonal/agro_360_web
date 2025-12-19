<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;
use App\Models\User;

class ViticulturistInvitationNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected User $creator
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $registerUrl = route('register', ['email' => $notifiable->email]);

        return (new MailMessage)
            ->subject('Invitación a Agro365')
            ->line(new HtmlString(
                '<div style="text-align:center; margin-bottom: 16px;">
                    <img src="'.asset('images/logo.png').'" alt="Agro365"
                         style="max-width: 160px; height: auto;">
                 </div>'
            ))
            ->greeting('Hola ' . ($notifiable->name ?: ''))
            ->line('Has sido invitado a Agro365 por el viticultor ' . $this->creator->name . '.')
            ->line('Agro365 es el cuaderno de campo digital para gestionar tus parcelas, tratamientos y equipo.')
            ->line('Para activar tu cuenta y elegir tu contraseña, haz clic en el siguiente botón:')
            ->action('Activar mi cuenta', $registerUrl)
            ->line('Si no esperabas esta invitación, puedes ignorar este correo.')
            ->salutation("Saludos,\nAgro365");
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}


