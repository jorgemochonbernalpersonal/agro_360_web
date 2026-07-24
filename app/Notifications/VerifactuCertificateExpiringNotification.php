<?php

namespace App\Notifications;

use App\Notifications\Concerns\RespectsPreferences;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Aviso proactivo de que el certificado de firma VeriFactu de un usuario está
 * a punto de caducar, para que se renueve ANTES de que las facturas empiecen
 * a fallar al emitir (hoy la caducidad solo se descubre cuando signXml()
 * lanza el error en plena emisión). Se dispara desde
 * `verifactu:notify-expiring-certs`.
 */
class VerifactuCertificateExpiringNotification extends Notification implements ShouldQueue
{
    use Queueable, RespectsPreferences;

    /**
     * @param  int  $days  Días que faltan para la caducidad.
     * @param  string  $url  Ajustes (pestaña fiscal) donde renovar el certificado.
     * @param  string  $expiresOn  Fecha de caducidad (d/m/Y).
     */
    public function __construct(
        public int $days,
        public string $url,
        public string $expiresOn,
    ) {}

    public function notificationCategory(): string
    {
        return 'verifactu_certificate';
    }

    public function via(object $notifiable): array
    {
        return $this->filterChannelsByPreferences($notifiable, ['mail', 'database']);
    }

    private function title(): string
    {
        return "Tu certificado VeriFactu caduca en {$this->days} día(s)";
    }

    private function body(): string
    {
        return "Tu certificado de firma VeriFactu caduca el {$this->expiresOn}. Cuando caduque, no podrás enviar facturas a la AEAT hasta que subas uno nuevo.";
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title())
            ->line($this->body())
            ->action('Renovar certificado', url($this->url))
            ->line('Si ya lo has renovado, ignora este aviso.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'verifactu_cert_expiring',
            'title' => $this->title(),
            'message' => $this->body(),
            'url' => $this->url,
            'days' => $this->days,
        ];
    }
}
