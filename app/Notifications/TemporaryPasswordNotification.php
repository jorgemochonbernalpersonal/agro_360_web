<?php

namespace App\Notifications;

use App\Support\AppLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TemporaryPasswordNotification extends Notification
{
    use Queueable;

    protected $temporaryPassword;
    protected $pdfContent;

    public function __construct(string $temporaryPassword, string $pdfContent = null)
    {
        $this->temporaryPassword = $temporaryPassword;
        $this->pdfContent = $pdfContent;
    }

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
        $loginUrl = AppLink::url(url('/login'), 'agro365://login');

        // Solo forzar HTTPS en producción
        if (app()->environment('production')) {
            $loginUrl = str_replace('http://', 'https://', $loginUrl);
        }
        
        $mail = (new MailMessage)
                    ->subject(__('Bienvenido a Agro365 — Credenciales de Acceso'))
                    ->greeting(__('¡Bienvenido a Agro365!'))
                    ->line(__('Se ha creado una cuenta para ti en Agro365.'))
                    ->line(__('Adjunto encontrarás un PDF con tus credenciales de acceso.'))
                    ->line(__('Por motivos de seguridad, **deberás cambiar tu contraseña** al iniciar sesión por primera vez.'))
                    ->action(__('Iniciar Sesión'), $loginUrl)
                    ->line(__('---'))
                    ->line('**Para volver a entrar después:** ve a agro365.es, haz clic en "Iniciar sesión" y usa tu email con la nueva contraseña que elijas. Si no la recuerdas, usa "¿Olvidaste tu contraseña?".')
                    ->line(__('Si no solicitaste esta cuenta, por favor contacta con quien te dio de alta.'));
        
        if ($this->pdfContent) {
            $mail->attachData($this->pdfContent, 'credenciales_agro365.pdf', [
                'mime' => 'application/pdf',
            ]);
        }
        
        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
