<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TemporaryPasswordNotification extends Notification
{
    use Queueable;

    protected $temporaryPassword;
    protected $pdfPath;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $temporaryPassword, string $pdfPath = null)
    {
        $this->temporaryPassword = $temporaryPassword;
        $this->pdfPath = $pdfPath;
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
        $loginUrl = url('/login');
        
        // Solo forzar HTTPS en producción
        if (app()->environment('production')) {
            $loginUrl = str_replace('http://', 'https://', $loginUrl);
        }
        
        $mail = (new MailMessage)
                    ->subject('Bienvenido a Agro365 - Credenciales de Acceso')
                    ->greeting('¡Bienvenido a Agro365!')
                    ->line('Se ha creado una cuenta para ti en Agro365.')
                    ->line('Adjunto encontrarás un PDF con tus credenciales de acceso.')
                    ->line('Por motivos de seguridad, **deberás cambiar tu contraseña** al iniciar sesión por primera vez.')
                    ->action('Iniciar Sesión', $loginUrl)
                    ->line('Si no solicitaste esta cuenta, por favor contacta con el administrador.');
        
        // Adjuntar PDF si existe
        if ($this->pdfPath && file_exists($this->pdfPath)) {
            $mail->attach($this->pdfPath, [
                'as' => 'credenciales_agro365.pdf',
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
