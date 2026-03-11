<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends VerifyEmail
{
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        $isWinery = $notifiable->hasWineryAccess();

        $roleLabel   = $isWinery ? 'bodega' : 'viticultor';
        $description = $isWinery
            ? 'tu plataforma de gestión de vendimia, viticultores y bodega digital.'
            : 'tu cuaderno de campo digital para viticultores.';

        $features = $isWinery
            ? [
                '✅ Gestionar campañas de vendimia y recepciones de uva',
                '✅ Administrar tus viticultores asociados',
                '✅ Consultar parcelas y datos SIGPAC de tus viticultores',
                '✅ Teledetección y mapa de viñedos asociados',
                '✅ Control de lotes de vino y contenedores',
                '✅ Facturación de compra de uva y venta de vino',
                '✅ Acceso al cuaderno de campo de tus viticultores',
            ]
            : [
                '✅ Gestionar tus parcelas y plantaciones',
                '✅ Registrar actividades agrícolas (fitosanitarios, riegos, cosechas...)',
                '✅ Generar informes oficiales con firma digital',
                '✅ Monitorear la salud de tus viñedos con teledetección',
                '✅ Controlar el stock de productos y maquinaria',
            ];

        $message = (new MailMessage)
            ->subject('Verifica tu cuenta de Agro365')
            ->greeting("Hola {$notifiable->name},")
            ->line("Gracias por registrarte en Agro365 como **{$roleLabel}**, {$description}")
            ->line('Para activar tu cuenta y empezar a utilizar la plataforma, por favor verifica tu dirección de correo electrónico haciendo clic en el siguiente botón:')
            ->action('Verificar mi email', $verificationUrl)
            ->line('Este enlace de verificación expirará en 24 horas.')
            ->line('**¿Qué puedes hacer en Agro365?**');

        foreach ($features as $feature) {
            $message->line($feature);
        }

        return $message
            ->line('Si no has solicitado esta cuenta, puedes ignorar este mensaje sin problemas.')
            ->line('Si tienes alguna pregunta, puedes contactarnos en info@agro365.es')
            ->salutation('Saludos, El equipo de Agro365');
    }
}
