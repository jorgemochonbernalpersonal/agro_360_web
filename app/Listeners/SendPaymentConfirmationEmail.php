<?php

namespace App\Listeners;

use App\Events\PaymentCompleted;
use App\Notifications\PaymentConfirmation;
use Illuminate\Support\Facades\Log;

class SendPaymentConfirmationEmail
{
    /**
     * Handle the event.
     */
    public function handle(PaymentCompleted $event): void
    {
        $payment = $event->payment;
        $user = $payment->user;

        Log::info('Enviando email de confirmación de pago', [
            'payment_id' => $payment->id,
            'user_id' => $user->id,
            'amount' => $payment->amount,
        ]);

        $user->notify(new PaymentConfirmation($payment));
    }
}
