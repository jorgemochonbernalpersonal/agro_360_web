<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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

        if (! isset($response['status']) || $response['status'] !== 'COMPLETED') {
            throw new \Exception(__('El pago no se completó correctamente en PayPal.'));
        }

        return $response;
    }

    /**
     * Actualizar pago como completado
     */
    public function completePayment(Payment $payment, array $paypalResponse): Payment
    {
        // Preservar el plan_type que se guardó al crear el pedido: paypal_response se
        // sobrescribe aquí con la respuesta de captura, y perderíamos el plan si no lo
        // propagamos.
        $planType = $this->resolvePlanType($payment);

        $payment->update([
            'status' => Payment::STATUS_COMPLETED,
            'paypal_payment_id' => $paypalResponse['purchase_units'][0]['payments']['captures'][0]['id'] ?? null,
            'paypal_response' => $planType !== null
                ? array_merge($paypalResponse, ['plan_type' => $planType])
                : $paypalResponse,
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
     *
     * IMPORTANTE: plan_type y amount se leen del propio registro Payment (localizado
     * por el orderId que PayPal devuelve), NUNCA de $pendingData/session. La sesión
     * 'pending_subscription' se sobrescribe si el usuario abre un segundo checkout en
     * otra pestaña; confiar en ella permitiría aprobar una orden antigua (p. ej. mensual)
     * y crear una suscripción con el plan/precio de la orden más reciente (p. ej. anual).
     * $pendingData se mantiene solo como fallback si un pago muy antiguo no tuviera
     * plan_type persistido.
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

        // 2. Capturar el pago en PayPal (externo — fuera de la transacción BD)
        $paypalResponse = $this->capturePayPalPayment($orderId);

        // 3-6. Efectos en BD son atómicos: si cualquier write falla, todo revierte
        $subscription = DB::transaction(function () use ($user, $payment, $paypalResponse, $pendingData, $orderId) {
            $planType = $this->resolvePlanType($payment) ?? ($pendingData['plan_type'] ?? null);
            $amount = (float) $payment->amount;

            if (! $planType) {
                throw new \Exception(__('No se pudo determinar el plan de la suscripción para este pago.'));
            }

            // Comparar el importe realmente capturado por PayPal contra el importe
            // esperado registrado en el Payment. Si no coinciden, no confiamos en
            // ninguno de los dos ciegamente: abortamos para evitar crear una
            // suscripción con un importe distinto al efectivamente cobrado.
            $capturedAmount = $paypalResponse['purchase_units'][0]['payments']['captures'][0]['amount']['value'] ?? null;
            if ($capturedAmount !== null && abs((float) $capturedAmount - $amount) > 0.01) {
                Log::error('Importe capturado por PayPal no coincide con el Payment', [
                    'payment_id' => $payment->id,
                    'expected_amount' => $amount,
                    'captured_amount' => $capturedAmount,
                ]);

                throw new \Exception(__('El importe cobrado no coincide con el importe esperado.'));
            }

            // 3. Marcar el pago como completado
            $this->completePayment($payment, $paypalResponse);

            // 4. Cancelar suscripciones anteriores
            $this->cancelActiveSubscriptions($user);

            // 5. Crear nueva suscripción
            $subscription = $this->createSubscription(
                $user,
                $planType,
                $amount,
                $orderId
            );

            // 6. Vincular pago con suscripción
            $this->linkPaymentToSubscription($payment, $subscription);

            return $subscription;
        });

        return $subscription;
    }

    /**
     * Leer el plan_type persistido en el propio Payment (guardado dentro de
     * paypal_response al crear el pedido). Ver processSuccessfulPayment() para el motivo
     * de no usar la sesión.
     */
    private function resolvePlanType(Payment $payment): ?string
    {
        $response = $payment->paypal_response;

        if (is_array($response) && ! empty($response['plan_type'])) {
            return (string) $response['plan_type'];
        }

        return null;
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
