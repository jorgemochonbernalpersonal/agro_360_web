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
        return (new MailMessage)
            ->subject('¡Bienvenido a Agro365! 🌱')
            ->greeting("¡Hola {$notifiable->name}!")
            ->line('Bienvenido a Agro365, tu cuaderno de campo digital profesional.')
            ->line('**¿Qué puedes hacer en Agro365?**')
            ->line('✅ Gestionar tus parcelas y plantaciones')
            ->line('✅ Registrar actividades agrícolas (fitosanitarios, riegos, cosechas...)')
            ->line('✅ Generar informes oficiales con firma digital')
            ->line('✅ Monitorear la salud de tus viñedos con teledetección')
            ->line('✅ Controlar el stock de productos y maquinaria')
            ->action('Ir al Dashboard', AppLink::url(url('/dashboard'), 'agro365://home'))
            ->line('Si necesitas ayuda, no dudes en contactarnos en info@agro365.es')
            ->line('¡Comienza hoy tu gestión agrícola digital!');
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
