<?php

namespace App\Actions\Subscription;

use App\DataTransferObjects\PendingSubscriptionData;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use App\Services\PaymentService;

class CreateSubscriptionFromPaymentAction
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    /**
     * Crear suscripción desde un pago exitoso
     */
    public function execute(
        User $user,
        string $orderId,
        PendingSubscriptionData $data
    ): Subscription {
        // 1. Buscar el pago pendiente
        $payment = Payment::where('paypal_order_id', $orderId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // 2. Capturar el pago en PayPal
        $paypalResponse = $this->paymentService->capturePayPalPayment($orderId);

        // 3. Marcar el pago como completado
        $this->paymentService->completePayment($payment, $paypalResponse);

        // 4. Cancelar suscripciones anteriores
        $this->paymentService->cancelActiveSubscriptions($user);

        // 5. Crear nueva suscripción
        $subscription = $this->paymentService->createSubscription(
            $user,
            $data->planType,
            $data->amount,
            $orderId
        );

        // 6. Vincular pago con suscripción
        $this->paymentService->linkPaymentToSubscription($payment, $subscription);

        return $subscription;
    }
}
