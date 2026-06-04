<?php

namespace App\Notifications;

use App\Models\Payment;
use App\Notifications\Concerns\RespectsPreferences;
use App\Support\AppLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentConfirmation extends Notification implements ShouldQueue
{
    use Queueable, RespectsPreferences;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Payment $payment
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function notificationCategory(): string
    {
        return 'payment';
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
            ->subject(__('Confirmación de Pago - Agro365'))
            ->greeting('¡Pago recibido!')
            ->line(__('Hemos recibido tu pago correctamente.'))
            ->line('**Detalles del pago:**')
            ->line('- Monto: €'.number_format($this->payment->amount, 2))
            ->line('- Plan: '.$this->payment->plan_type)
            ->line('- ID de transacción: '.$this->payment->paypal_order_id)
            ->action(__('Ver mis suscripciones'), AppLink::url(url('/dashboard/subscriptions'), 'agro365://home'))
            ->line(__('Gracias por confiar en Agro365.'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'payment_id' => $this->payment->id,
            'amount' => $this->payment->amount,
            'plan_type' => $this->payment->plan_type,
            'status' => $this->payment->status,
        ];
    }
}
