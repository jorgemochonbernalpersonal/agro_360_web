<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;
use App\Models\User;
use App\Support\AppLink;

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
        $webUrl  = route('auth.claim-account', ['token' => $this->token]);
        $deepUrl = 'agro365://claim/' . $this->token;

        if (app()->environment('production')) {
            $webUrl = str_replace('http://', 'https://', $webUrl);
        }

        return (new MailMessage)
            ->subject('Invitación a Agro365')
            ->greeting('Hola ' . ($notifiable->name ?: ''))
            ->line($this->creatorLabel() . ' te ha invitado a acceder a Agro365.')
            ->line('Ya tienes tus parcelas y plantaciones configuradas. Solo necesitas activar tu cuenta para acceder al cuaderno de campo digital.')
            ->line('El enlace de activación es válido — úsalo para elegir tu contraseña:')
            ->action('Activar mi cuenta', AppLink::url($webUrl, $deepUrl))
            ->line('Si no esperabas esta invitación, puedes ignorar este correo.')
            ->salutation("Saludos,\nAgro365");
    }

    private function creatorLabel(): string
    {
        $type = $this->creator->isSupervisor() ? 'La denominación de origen' : 'La bodega';
        return "{$type} **{$this->creator->name}**";
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


