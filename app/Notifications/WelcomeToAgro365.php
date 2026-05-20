<?php

namespace App\Notifications;

use App\Models\User;
use App\Support\AppLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeToAgro365 extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct() {}

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
        $isWinery   = $notifiable->hasWineryAccess();
        $isProducer = $notifiable->role === 'producer';

        $dashboardPath = match (true) {
            $isWinery   => '/winery/dashboard',
            $isProducer => '/producer/dashboard',
            default     => '/viticulturist/dashboard',
        };

        $dashboardUrl = AppLink::url(url($dashboardPath), 'agro365://home');

        $nextSteps = $isWinery
            ? [
                '1. Accede al dashboard y revisa el panel de control',
                '2. Da de alta a tus viticultores para que registren sus parcelas',
                '3. Configura tu primera campana de vendimia',
            ]
            : [
                '1. Accede al dashboard y familiarízate con el panel',
                '2. Registra tus parcelas y plantaciones',
                '3. Empieza a anotar actividades en el cuaderno de campo',
            ];

        $message = (new MailMessage)
            ->subject('Tu cuenta de Agro365 ya está activa')
            ->greeting("Hola {$notifiable->name},")
            ->line('Tu email ha sido verificado y tu cuenta está completamente activa.')
            ->line('Tienes **3 meses de acceso completo gratuito** para explorar todas las funcionalidades.')
            ->line('**Primeros pasos recomendados:**');

        foreach ($nextSteps as $step) {
            $message->line($step);
        }

        return $message
            ->action('Ir al Dashboard', $dashboardUrl)
            ->line('---')
            ->line('**Tus datos de acceso:**')
            ->line("Email: **{$notifiable->email}**")
            ->line('Contraseña: la que elegiste al registrarte.')
            ->line('Si no la recuerdas, usa "¿Olvidaste tu contraseña?" en la pantalla de inicio de sesión.')
            ->line('Si necesitas ayuda, contáctanos en info@agro365.es')
            ->salutation('Saludos, El equipo de Agro365');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'welcome',
        ];
    }
}
