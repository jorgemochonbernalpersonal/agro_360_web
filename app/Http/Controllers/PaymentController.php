<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Subscription;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    public function success(Request $request)
    {
        $user = Auth::user();
        $pendingData = session('pending_subscription');

        if (!$pendingData) {
            session()->flash('error', 'No se encontró información de pago pendiente.');
            return redirect()->route('subscription.manage');
        }

        try {
            // Obtener el order ID de PayPal
            $orderId = $this->paymentService->findPayPalOrderId(
                $user,
                $request->get('token'),
                $pendingData['payment_id'] ?? null
            );

            if (!$orderId) {
                throw new \Exception('No se encontró el ID de la orden de pago.');
            }

            // Procesar el pago completo (captura PayPal, actualiza pago, crea suscripción)
            $subscription = $this->paymentService->processSuccessfulPayment(
                $user,
                $orderId,
                $pendingData
            );

            // Limpiar sesión y mostrar mensaje de éxito
            session()->forget('pending_subscription');
            session()->flash('message', '¡Pago completado! Tu suscripción está activa.');

            return redirect()->route('subscription.manage');

        } catch (\Exception $e) {
            $this->paymentService->handlePaymentError($e, $user, $orderId ?? null);
            session()->flash('error', 'Error al procesar el pago: ' . $e->getMessage());
            return redirect()->route('subscription.manage');
        }
    }

    public function cancel()
    {
        session()->forget('pending_subscription');
        session()->flash('message', 'Pago cancelado.');
        return redirect()->route('subscription.manage');
    }
}
