<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PaymentService
{
    /**
     * Crear un pago pendiente
     */
    public function createPendingPayment(
        User $user,
        string $planType,
        float $amount
    ): Payment {
        return Payment::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'status' => Payment::STATUS_PENDING,
            'payment_method' => 'paypal',
        ]);
    }

    /**
     * Buscar orden de PayPal por ID o desde el último pago pendiente
     */
    public function findPayPalOrderId(User $user, ?string $token, ?int $pendingPaymentId = null): ?string
    {
        // 1. Intentar desde el parámetro token
        if ($token) {
            return $token;
        }

        // 2. Intentar desde el pago pendiente en sesión
        if ($pendingPaymentId) {
            $payment = Payment::where('id', $pendingPaymentId)
                ->where('user_id', $user->id)
                ->where('status', Payment::STATUS_PENDING)
                ->first();

            if ($payment && $payment->paypal_order_id) {
                return $payment->paypal_order_id;
            }
        }

        // 3. Buscar el último pago pendiente del usuario
        $payment = Payment::where('user_id', $user->id)
            ->where('status', Payment::STATUS_PENDING)
            ->orderBy('created_at', 'desc')
            ->first();

        return $payment?->paypal_order_id;
    }

    /**
     * Capturar un pago de PayPal
     */
    public function capturePayPalPayment(string $orderId): array
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        
        $token = $provider->getAccessToken();
        $provider->setAccessToken($token);

        $response = $provider->capturePaymentOrder($orderId);

        if (!isset($response['status']) || $response['status'] !== 'COMPLETED') {
            throw new \Exception(__('El pago no se completó correctamente en PayPal.'));
        }

        return $response;
    }

    /**
     * Actualizar pago como completado
     */
    public function completePayment(Payment $payment, array $paypalResponse): Payment
    {
        $payment->update([
            'status' => Payment::STATUS_COMPLETED,
            'paypal_payment_id' => $paypalResponse['purchase_units'][0]['payments']['captures'][0]['id'] ?? null,
            'paypal_response' => $paypalResponse,
            'paid_at' => now(),
        ]);

        Log::info('Pago completado exitosamente', [
            'payment_id' => $payment->id,
            'user_id' => $payment->user_id,
            'amount' => $payment->amount,
        ]);

        return $payment->fresh();
    }

    /**
     * Cancelar suscripciones activas de un usuario
     */
    public function cancelActiveSubscriptions(User $user): int
    {
        $count = Subscription::where('user_id', $user->id)
            ->where('status', Subscription::STATUS_ACTIVE)
            ->update([
                'status' => Subscription::STATUS_CANCELLED,
                'cancelled_at' => now(),
            ]);

        Log::info('Suscripciones previas canceladas', [
            'user_id' => $user->id,
            'count' => $count,
        ]);

        return $count;
    }

    /**
     * Crear una nueva suscripción
     */
    public function createSubscription(
        User $user,
        string $planType,
        float $amount,
        string $paypalOrderId
    ): Subscription {
        $startsAt = now();
        $endsAt = $planType === 'yearly'
            ? $startsAt->copy()->addYear()
            : $startsAt->copy()->addMonth();

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_type' => $planType,
            'amount' => $amount,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'paypal_order_id' => $paypalOrderId,
        ]);

        Log::info('Nueva suscripción creada', [
            'subscription_id' => $subscription->id,
            'user_id' => $user->id,
            'plan_type' => $planType,
            'ends_at' => $endsAt,
        ]);

        return $subscription;
    }

    /**
     * Vincular pago con suscripción
     */
    public function linkPaymentToSubscription(Payment $payment, Subscription $subscription): void
    {
        $payment->update(['subscription_id' => $subscription->id]);
    }

    /**
     * Procesar todo el flujo de pago exitoso
     * Este método orquesta todas las operaciones necesarias
     */
    public function processSuccessfulPayment(
        User $user,
        string $orderId,
        array $pendingData
    ): Subscription {
        // 1. Buscar el pago pendiente
        $payment = Payment::where('paypal_order_id', $orderId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // 2. Capturar el pago en PayPal
        $paypalResponse = $this->capturePayPalPayment($orderId);

        // 3. Marcar el pago como completado
        $this->completePayment($payment, $paypalResponse);

        // 4. Cancelar suscripciones anteriores
        $this->cancelActiveSubscriptions($user);

        // 5. Crear nueva suscripción
        $subscription = $this->createSubscription(
            $user,
            $pendingData['plan_type'],
            $pendingData['amount'],
            $orderId
        );

        // 6. Vincular pago con suscripción
        $this->linkPaymentToSubscription($payment, $subscription);

        return $subscription;
    }

    /**
     * Manejar error de pago
     */
    public function handlePaymentError(\Exception $e, User $user, ?string $orderId = null): void
    {
        Log::error('Error procesando pago de PayPal', [
            'user_id' => $user->id,
            'order_id' => $orderId,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
    }
}
