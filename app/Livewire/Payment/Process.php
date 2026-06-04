<?php

namespace App\Livewire\Payment;

use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class Process extends Component
{
    public function success($token = null)
    {
        $user = Auth::user();
        $pendingData = session('pending_subscription');

        if (! $pendingData) {
            session()->flash('error', __('No se encontró información de pago pendiente.'));

            return redirect()->route('subscription.manage');
        }

        try {
            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $token = $provider->getAccessToken();
            $provider->setAccessToken($token);

            // Obtener el order ID de la sesión o del request
            $orderId = request()->get('token') ?? $pendingData['payment_id'] ?? null;

            if (! $orderId) {
                // Buscar el pago pendiente
                $payment = Payment::where('id', $pendingData['payment_id'])
                    ->where('user_id', $user->id)
                    ->where('status', Payment::STATUS_PENDING)
                    ->first();

                if ($payment && $payment->paypal_order_id) {
                    $orderId = $payment->paypal_order_id;
                }
            }

            if (! $orderId) {
                throw new \Exception(__('No se encontró el ID de la orden de pago.'));
            }

            // Capturar el pago
            $response = $provider->capturePaymentOrder($orderId);

            if (isset($response['status']) && $response['status'] === 'COMPLETED') {
                // Actualizar el pago
                $payment = Payment::where('paypal_order_id', $orderId)
                    ->where('user_id', $user->id)
                    ->first();

                if ($payment) {
                    $payment->update([
                        'status' => Payment::STATUS_COMPLETED,
                        'paypal_payment_id' => $response['purchase_units'][0]['payments']['captures'][0]['id'] ?? null,
                        'paypal_response' => $response,
                        'paid_at' => now(),
                    ]);

                    // Crear o actualizar la suscripción
                    $startsAt = now();
                    $endsAt = $pendingData['plan_type'] === 'yearly'
                        ? $startsAt->copy()->addYear()
                        : $startsAt->copy()->addMonth();

                    // Cancelar suscripciones anteriores
                    Subscription::where('user_id', $user->id)
                        ->where('status', Subscription::STATUS_ACTIVE)
                        ->update(['status' => Subscription::STATUS_CANCELLED, 'cancelled_at' => now()]);

                    // Crear nueva suscripción
                    $subscription = Subscription::create([
                        'user_id' => $user->id,
                        'plan_type' => $pendingData['plan_type'],
                        'amount' => $pendingData['amount'],
                        'status' => Subscription::STATUS_ACTIVE,
                        'starts_at' => $startsAt,
                        'ends_at' => $endsAt,
                        'paypal_order_id' => $orderId,
                    ]);

                    $payment->update(['subscription_id' => $subscription->id]);

                    session()->forget('pending_subscription');
                    session()->flash('message', __('¡Pago completado! Tu suscripción está activa.'));

                    return redirect()->route('subscription.manage');
                }
            }

            session()->flash('error', __('El pago no se completó correctamente.'));

            return redirect()->route('subscription.manage');
        } catch (\Exception $e) {
            Log::error('Error processing PayPal payment', [
                'user_id' => $user->id,
                'order_id' => $orderId ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            session()->flash('error', __('Error al procesar el pago: :message', ['message' => $e->getMessage()]));

            return redirect()->route('subscription.manage');
        }
    }

    public function cancel()
    {
        session()->forget('pending_subscription');
        session()->flash('message', __('Pago cancelado.'));

        return redirect()->route('subscription.manage');
    }

    public function render()
    {
        return view('livewire.payment.process');
    }
}
