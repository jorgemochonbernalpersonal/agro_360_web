<?php

namespace App\Notifications;

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
        $isWinery = $notifiable->hasWineryAccess();
        $isProducer = $notifiable->role === 'producer';

        $dashboardPath = match (true) {
            $isWinery => '/winery/dashboard',
            $isProducer => '/producer/dashboard',
            default => '/viticulturist/dashboard',
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
            ->subject(__('Tu cuenta de Agro365 ya está activa'))
            ->greeting(__('Hola :name,', ['name' => $notifiable->name]))
            ->line(__('Tu email ha sido verificado y tu cuenta está completamente activa.'))
            ->line(__('Tienes **3 meses de acceso completo gratuito** para explorar todas las funcionalidades.'))
            ->line(__('**Primeros pasos recomendados:**'));

        foreach ($nextSteps as $step) {
            $message->line($step);
        }

        return $message
            ->action(__('Ir al Dashboard'), $dashboardUrl)
            ->line(__('---'))
            ->line(__('**Tus datos de acceso:**'))
            ->line(__('Email: **:email**', ['email' => $notifiable->email]))
            ->line(__('Contraseña: la que elegiste al registrarte.'))
            ->line(__('Si no la recuerdas, usa "¿Olvidaste tu contraseña?" en la pantalla de inicio de sesión.'))
            ->line(__('Si necesitas ayuda, contáctanos en info@agro365.es'))
            ->salutation(__('Saludos, El equipo de Agro365'));
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
