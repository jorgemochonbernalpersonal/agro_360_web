<?php

namespace App\Notifications;

use App\Models\Subscription;
use App\Notifications\Concerns\RespectsPreferences;
use App\Support\AppLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionExpiringSoon extends Notification implements ShouldQueue
{
    use Queueable, RespectsPreferences;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Subscription $subscription,
        public int $daysRemaining
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function notificationCategory(): string
    {
        return 'subscription';
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
        return (new MailMessage)
            ->subject('Tu suscripción expira pronto - Agro365')
            ->greeting('¡Hola!')
            ->line("Tu suscripción al plan **{$this->subscription->plan_type}** expirará en {$this->daysRemaining} días.")
            ->line('Para seguir disfrutando de todas las funcionalidades de Agro365, renueva tu suscripción.')
            ->action('Renovar suscripción', AppLink::url(url('/pricing'), 'agro365://home'))
            ->line('Si tienes alguna pregunta, no dudes en contactarnos.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'subscription_id' => $this->subscription->id,
            'plan_type' => $this->subscription->plan_type,
            'days_remaining' => $this->daysRemaining,
            'ends_at' => $this->subscription->ends_at->toIso8601String(),
        ];
    }
}
