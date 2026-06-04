<?php

namespace App\Notifications;

use App\Notifications\Concerns\RespectsPreferences;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegulatoryDeadlineNotification extends Notification implements ShouldQueue
{
    use Queueable, RespectsPreferences;

    public function __construct(
        public readonly string $deadlineLabel,
        public readonly string $deadlineDate,
        public readonly int $daysLeft,
        public readonly string $producerType,  // 'gran' | 'pequeño'
        public readonly string $deadlineType,  // 'monthly' | 'semi_annual'
    ) {}

    public function notificationCategory(): string
    {
        return 'regulatory';
    }

    public function via(object $notifiable): array
    {
        return $this->filterChannelsByPreferences($notifiable, ['mail', 'database']);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $prefix = $this->daysLeft === 1 ? '⚠️ URGENTE — ' : '';
        $dayWord = $this->daysLeft === 1 ? 'día' : 'días';
        $date = Carbon::parse($this->deadlineDate)->translatedFormat('d \d\e F \d\e Y');

        return (new MailMessage)
            ->subject($prefix.'INFOVI — Declaración vence en '.$this->daysLeft.' '.$dayWord)
            ->greeting(__('Hola, ').$notifiable->name.':')
            ->line(__('Tu próxima declaración **INFOVI** está a punto de vencer.'))
            ->line('**Declaración:** '.$this->deadlineLabel)
            ->line('**Fecha límite:** '.$date)
            ->line('**Tipo de productor:** '.ucfirst($this->producerType).' productor ('
                .($this->deadlineType === 'monthly' ? 'declaración mensual' : 'declaración semestral').')')
            ->line('---')
            ->line('**Pasos para declarar:**')
            ->line('1. Abre Agro365 → SILICIE → INFOVI y comprueba los cuadros de existencias.')
            ->line('2. Exporta el CSV SILICIE desde el panel SILICIE.')
            ->line('3. Accede a **mapa.gob.es/infovi** y envía la declaración.')
            ->action(__('Abrir INFOVI en Agro365'), route('winery.silicie.infovi'))
            ->line(__('Si ya has realizado la declaración puedes ignorar este recordatorio.'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'regulatory_deadline',
            'deadline_label' => $this->deadlineLabel,
            'deadline_date' => $this->deadlineDate,
            'days_left' => $this->daysLeft,
            'producer_type' => $this->producerType,
            'deadline_type' => $this->deadlineType,
        ];
    }
}
