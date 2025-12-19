<?php

namespace App\Livewire\Subscription;

use App\Models\Subscription;
use App\Models\Payment;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Illuminate\Support\Facades\Log;

class Manage extends Component
{
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
            session()->flash('error', 'Ya tienes una suscripción activa.');
            return;
        }

        // Determinar precio según el plan
        $amount = $this->selectedPlan === 'yearly' 
            ? Subscription::PRICE_YEARLY 
            : Subscription::PRICE_MONTHLY;

        try {
            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $token = $provider->getAccessToken();
            $provider->setAccessToken($token);

            // Crear orden de pago
            $order = $provider->createOrder([
                "intent" => "CAPTURE",
                "purchase_units" => [
                    [
                        "amount" => [
                            "currency_code" => "EUR",
                            "value" => number_format($amount, 2, '.', '')
                        ],
                        "description" => "Suscripción Agro365 - " . ($this->selectedPlan === 'yearly' ? 'Anual' : 'Mensual')
                    ]
                ],
                "application_context" => [
                    "return_url" => route('payment.success'),
                    "cancel_url" => route('payment.cancel'),
                    "brand_name" => "Agro365",
                    "locale" => "es-ES",
                    "landing_page" => "BILLING",
                    "shipping_preference" => "NO_SHIPPING",
                    "user_action" => "PAY_NOW"
                ]
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
                    ]
                ]);

                // Redirigir a PayPal usando JavaScript
                foreach ($order['links'] as $link) {
                    if ($link['rel'] === 'approve') {
                        $this->dispatch('redirect-to-paypal', url: $link['href']);
                        return;
                    }
                }
            }

            session()->flash('error', 'Error al crear la orden de pago. Por favor, inténtalo de nuevo.');
        } catch (\Exception $e) {
            Log::error('Error initiating PayPal payment', [
                'user_id' => $user->id,
                'plan' => $this->selectedPlan,
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', 'Error al procesar el pago: ' . $e->getMessage());
        }
    }

    public function cancelSubscription()
    {
        $user = Auth::user();
        $subscription = $user->activeSubscription;

        if (!$subscription) {
            session()->flash('error', 'No tienes una suscripción activa.');
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
            session()->flash('message', 'Suscripción cancelada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error canceling subscription', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', 'Error al cancelar la suscripción: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.subscription.manage')->layout('layouts.app');
    }
}
