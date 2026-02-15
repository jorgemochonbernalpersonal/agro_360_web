<?php

namespace App\Listeners;

use App\Events\PaymentCompleted;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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

        // TODO: Implementar envío de email
        // Mail::to($user->email)->send(new PaymentConfirmation($payment));
    }
}
