<?php

namespace App\Notifications;

use App\Models\Harvest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QualityFeedbackNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Harvest $harvest,
        public string $wineryName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $h = $this->harvest;
        $variety = $h->plotPlanting?->grapeVariety?->name ?? 'Uva';
        $plot = $h->plotPlanting?->plot?->name ?? '';

        $mail = (new MailMessage)
            ->subject("Informe de calidad — {$variety} ({$this->wineryName})")
            ->greeting('Datos de calidad de tu entrega')
            ->line("**Bodega:** {$this->wineryName}")
            ->line("**Variedad:** {$variety}".($plot ? " · Parcela: {$plot}" : ''))
            ->line('**Peso recibido:** '.number_format($h->total_weight, 0).' kg')
            ->line('**Fecha:** '.$h->harvest_start_date?->format('d/m/Y'));

        if ($h->baume_degree !== null) {
            $mail->line("**Grado Baumé:** {$h->baume_degree}°");
        }
        if ($h->probable_alcohol !== null) {
            $mail->line("**Alcohol probable:** {$h->probable_alcohol}%");
        }
        if ($h->total_acidity !== null) {
            $mail->line("**Acidez total:** {$h->total_acidity} g/L");
        }
        if ($h->ph !== null) {
            $mail->line("**pH:** {$h->ph}");
        }
        if ($h->quality_notes) {
            $mail->line("**Observaciones:** {$h->quality_notes}");
        }

        return $mail
            ->action(__('Ver mis entregas'), route('viticulturist.harvests.index'))
            ->salutation('— Equipo Agro365');
    }

    public function toArray(object $notifiable): array
    {
        $h = $this->harvest;

        return [
            'harvest_id' => $h->id,
            'winery_name' => $this->wineryName,
            'variety' => $h->plotPlanting?->grapeVariety?->name,
            'weight_kg' => $h->total_weight,
            'baume' => $h->baume_degree,
            'alcohol' => $h->probable_alcohol,
            'acidity' => $h->total_acidity,
            'ph' => $h->ph,
        ];
    }
}
