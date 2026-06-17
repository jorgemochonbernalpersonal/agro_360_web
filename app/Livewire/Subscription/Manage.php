<?php

namespace App\Livewire\Subscription;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class Manage extends Component
{
    use WithToastNotifications;

    public $selectedPlan = 'monthly';

    public $activeSubscription;

    public function mount()
    {
        $this->activeSubscription = Auth::user()->activeSubscription;
    }

    public function selectPlan($plan)
    {
        $this->selectedPlan = $plan;
    }

    public function initiatePayment()
    {
        $user = Auth::user();

        // Verificar si ya tiene una suscripción activa
        if ($user->hasActiveSubscription()) {
            $this->toastError(__('Ya tienes una suscripción activa.'));

            return;
        }

        // Bodega adscrita a una DO: su acceso lo cubre el paquete de la DO, no paga.
        if ($user->isCoveredByDo()) {
            $this->toastError(__('Tu bodega está adscrita a una Denominación de Origen — tu acceso está cubierto sin coste.'));

            return;
        }

        // Determinar precio según rol y plan:
        // DO/supervisor → tramo por nº de bodegas adscritas.
        // Bodegas → tramo de red (según nº de viticultores gestionados).
        // Viticultores / productores → precio fijo por vinculación.
        $isWinery = $user->hasWineryAccess() && ! $user->hasViticulturistAccess();
        $isDO = $user->isSupervisor();

        if ($isDO) {
            $amount = $this->selectedPlan === 'yearly'
                ? $user->doYearlyPrice()
                : $user->doMonthlyPrice();

            if ($amount === null) {
                $this->toastError(__('Tu DO supera las 100 bodegas. Contáctanos para un plan personalizado.'));

                return;
            }
        } elseif ($isWinery) {
            $amount = $this->selectedPlan === 'yearly'
                ? $user->wineryYearlyPrice()
                : $user->wineryMonthlyPrice();

            // Tramo enterprise (>100 viticultores): requiere negociación directa
            if ($amount === null) {
                $this->toastError(__('Tu bodega supera los 100 viticultores. Contáctanos para un plan personalizado.'));

                return;
            }
        } else {
            $amount = $this->selectedPlan === 'yearly'
                ? $user->viticulturistYearlyPrice()
                : $user->viticulturistMonthlyPrice();
        }

        try {
            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $token = $provider->getAccessToken();
            $provider->setAccessToken($token);

            // Crear orden de pago
            $order = $provider->createOrder([
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'amount' => [
                            'currency_code' => 'EUR',
                            'value' => number_format($amount, 2, '.', ''),
                        ],
                        'description' => 'Suscripción Agro365 - '.($this->selectedPlan === 'yearly' ? 'Anual' : 'Mensual'),
                    ],
                ],
                'application_context' => [
                    'return_url' => route('payment.success'),
                    'cancel_url' => route('payment.cancel'),
                    'brand_name' => 'Agro365',
                    'locale' => 'es-ES',
                    'landing_page' => 'BILLING',
                    'shipping_preference' => 'NO_SHIPPING',
                    'user_action' => 'PAY_NOW',
                ],
            ]);

            if (isset($order['id']) && $order['status'] === 'CREATED') {
                // Crear registro de pago pendiente
                $payment = Payment::create([
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'currency' => 'EUR',
                    'status' => Payment::STATUS_PENDING,
                    'paypal_order_id' => $order['id'],
                    'paypal_response' => $order,
                ]);

                // Guardar información de la suscripción en sesión
                session([
                    'pending_subscription' => [
                        'plan_type' => $this->selectedPlan,
                        'amount' => $amount,
                        'payment_id' => $payment->id,
                    ],
                ]);

                // Redirigir a PayPal usando JavaScript
                foreach ($order['links'] as $link) {
                    if ($link['rel'] === 'approve') {
                        $this->dispatch('redirect-to-paypal', url: $link['href']);

                        return;
                    }
                }
            }

            $this->toastError(__('Error al crear la orden de pago. Por favor, inténtalo de nuevo.'));
        } catch (\Exception $e) {
            Log::error('Error initiating PayPal payment', [
                'user_id' => $user->id,
                'plan' => $this->selectedPlan,
                'error' => $e->getMessage(),
            ]);
            $this->toastError(__('Error al procesar el pago: :error', ['error' => $e->getMessage()]));
        }
    }

    public function cancelSubscription()
    {
        $user = Auth::user();
        $subscription = $user->activeSubscription;

        if (! $subscription) {
            $this->toastError(__('No tienes una suscripción activa.'));

            return;
        }

        try {
            // Si tiene PayPal subscription ID, cancelar en PayPal
            if ($subscription->paypal_subscription_id) {
                $provider = new PayPalClient;
                $provider->setApiCredentials(config('paypal'));
                $token = $provider->getAccessToken();
                $provider->setAccessToken($token);

                $provider->cancelSubscription($subscription->paypal_subscription_id, 'Usuario canceló la suscripción');
            }

            $subscription->cancel();
            $this->activeSubscription = null;
            $this->toastSuccess(__('Suscripción cancelada correctamente.'));
        } catch (\Exception $e) {
            Log::error('Error canceling subscription', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);
            $this->toastError(__('Error al cancelar la suscripción: :error', ['error' => $e->getMessage()]));
        }
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $user = Auth::user();
        $isWinery = $user->hasWineryAccess() && ! $user->hasViticulturistAccess();
        $isDO = $user->isSupervisor();

        if ($isDO) {
            $tier = $user->doTier();
            $monthlyPrice = $tier['monthly'];
            $yearlyPrice  = $tier['yearly'];
            $wineryTier   = null;
            $doTier       = $tier;
        } elseif ($isWinery) {
            $tier = $user->wineryTier();
            $monthlyPrice = $tier['monthly'];
            $yearlyPrice  = $tier['yearly'];
            $wineryTier   = $tier;
            $doTier       = null;
        } else {
            $monthlyPrice = $user->viticulturistMonthlyPrice();
            $yearlyPrice  = $user->viticulturistYearlyPrice();
            $wineryTier   = null;
            $doTier       = null;
        }

        return view('livewire.subscription.manage', [
            'monthlyPrice'    => $monthlyPrice,
            'yearlyPrice'     => $yearlyPrice,
            'isWinery'        => $isWinery,
            'isDO'            => $isDO,
            'wineryTier'      => $wineryTier,
            'doTier'          => $doTier,
            'isWineryLinked'  => $user->hasWinery(),
            'isProducer'      => $user->isProducer(),
            'isCoveredByDo'   => $user->isCoveredByDo(),
        ]);
    }
}
