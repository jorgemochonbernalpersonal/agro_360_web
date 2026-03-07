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
        protected User   $creator,
        protected string $token,
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
        // URL de activación con token único
        $registerUrl = route('auth.claim-account', ['token' => $this->token]);

        if (app()->environment('production')) {
            $registerUrl = str_replace('http://', 'https://', $registerUrl);
        }
        
        // Generar URL absoluta para la imagen
        $logoUrl = url('images/logo.png');
        
        // Solo forzar HTTPS en producción
        if (app()->environment('production')) {
            $logoUrl = str_replace('http://', 'https://', $logoUrl);
        }

        return (new MailMessage)
            ->subject('Invitación a Agro365')
            ->line(new HtmlString(
                '<div style="text-align:center; margin-bottom: 16px;">
                    <img src="'.$logoUrl.'" alt="Agro365"
                         style="max-width: 160px; height: auto;">
                 </div>'
            ))
            ->greeting('Hola ' . ($notifiable->name ?: ''))
            ->line('La bodega **' . $this->creator->name . '** te ha invitado a acceder a Agro365.')
            ->line('Ya tienes tus parcelas y plantaciones configuradas. Solo necesitas activar tu cuenta para acceder al cuaderno de campo digital.')
            ->line('El enlace de activación es válido — úsalo para elegir tu contraseña:')
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


