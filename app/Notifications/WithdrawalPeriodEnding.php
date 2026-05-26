<?php

namespace App\Notifications;

use App\Models\PhytosanitaryTreatment;
use App\Notifications\Concerns\RespectsPreferences;
use App\Support\AppLink;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalPeriodEnding extends Notification implements ShouldQueue
{
    use Queueable, RespectsPreferences;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public PhytosanitaryTreatment $treatment,
        public Carbon $safeDate,
        public int $daysRemaining
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function notificationCategory(): string
    {
        return 'withdrawal_period';
    }

    public function via(object $notifiable): array
    {
        return $this->filterChannelsByPreferences($notifiable, ['mail', 'database']);
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $activity = $this->treatment->activity;
        $product = $this->treatment->product;

        return (new MailMessage)
            ->subject(__('Recordatorio: Plazo de Seguridad Fitosanitario'))
            ->greeting('📅 Recordatorio de Plazo de Seguridad')
            ->line("El plazo de seguridad para el tratamiento realizado en **{$activity->plot->name}** finalizará pronto.")
            ->line("**Producto:** {$product->name}")
            ->line("**Fecha de aplicación:** {$activity->activity_date->format('d/m/Y')}")
            ->line("**Fecha segura para cosecha:** {$this->safeDate->format('d/m/Y')}")
            ->line("**Días restantes:** {$this->daysRemaining}")
            ->action(__('Ver actividad'), AppLink::url(route('activities.show', $activity), 'agro365://home'))
            ->line(__('No olvides respetar el plazo de seguridad antes de la cosecha.'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'treatment_id' => $this->treatment->id,
            'activity_id' => $this->treatment->agricultural_activity_id,
            'product_name' => $this->treatment->product->name,
            'safe_date' => $this->safeDate->toDateString(),
            'days_remaining' => $this->daysRemaining,
        ];
    }
}
