<?php

namespace App\Notifications;

use App\Models\DiseaseAlertSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Symfony\Component\Mime\Email;

/**
 * Email de riesgo alto de mildiu y/o oídio para un suscriptor público.
 */
class DiseaseRiskAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param array<int, string>               $diseases 'mildew' y/o 'powdery'
     * @param array<int, array<string, mixed>> $days     días con riesgo alto (salida de DiseaseRiskModel)
     */
    public function __construct(
        protected DiseaseAlertSubscription $subscription,
        protected array $diseases,
        protected array $days,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $place = $this->subscription->municipality->name ?? 'tu municipio';
        $names = array_map(fn (string $d) => $d === 'mildew' ? 'mildiu' : 'oídio', $this->diseases);
        $unsubscribeUrl = $this->subscription->unsubscribeUrl();

        $mail = (new MailMessage)
            ->subject('Riesgo alto de '.implode(' y ', $names).' en '.$place)
            ->greeting('Riesgo alto en '.$place)
            ->withSymfonyMessage(function (Email $message) use ($unsubscribeUrl) {
                $message->getHeaders()->addTextHeader('List-Unsubscribe', '<'.$unsubscribeUrl.'>');
            });

        if (in_array('mildew', $this->diseases, true)) {
            $mail->line('**Mildiu:** se dan las condiciones de la regla de los tres dieces (≥10 mm de lluvia en 48 h con más de 10 °C). Si tus brotes superan los 10 cm, vigila la aparición de manchas de aceite y valora la protección.');
        }
        if (in_array('powdery', $this->diseases, true)) {
            $mail->line('**Oídio:** el índice de Gubler-Thomas está en zona de riesgo alto (≥60). Las temperaturas de estos días favorecen la enfermedad: revisa hojas y racimos.');
        }

        foreach ($this->days as $day) {
            $date = \Carbon\Carbon::parse($day['date'])->locale('es')->isoFormat('dddd D [de] MMMM');
            $mail->line('• '.ucfirst($date).': '.number_format($day['rain'], 1, ',', '.').' mm, '
                .number_format($day['temp_mean'], 1, ',', '.').' °C de media, índice oídio '.$day['powdery_index']);
        }

        return $mail
            ->action('Ver el riesgo de los próximos días', route('tools.show', 'alertas-mildiu-oidio'))
            ->line('Es un modelo orientativo basado en la meteo del municipio: confirma siempre en campo antes de tratar.')
            ->line('¿No quieres más avisos? [Darte de baja]('.$unsubscribeUrl.')')
            ->salutation('Agro365');
    }
}
