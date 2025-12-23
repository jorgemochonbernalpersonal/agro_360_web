<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class InvoiceStateCoherence implements Rule
{
    protected $status;
    protected $paymentStatus;
    protected $deliveryStatus;
    protected $failureMessage;

    public function __construct($status, $paymentStatus, $deliveryStatus)
    {
        $this->status = $status;
        $this->paymentStatus = $paymentStatus;
        $this->deliveryStatus = $deliveryStatus;
    }

    /**
     * Determine if the validation rule passes.
     */
    public function passes($attribute, $value): bool
    {
        // Regla 1: Si está cancelada, no puede estar pagada
        if ($this->status === 'cancelled' && $this->paymentStatus === 'paid') {
            $this->failureMessage = 'Una factura cancelada no puede estar marcada como pagada.';
            return false;
        }

        // Regla 2: Si está cancelada, no puede estar entregada
        if ($this->status === 'cancelled' && $this->deliveryStatus === 'delivered') {
            $this->failureMessage = 'Una factura cancelada no puede estar marcada como entregada.';
            return false;
        }

        // Regla 3: Si está entregada, el status debe ser al menos "approved"
        if ($this->deliveryStatus === 'delivered' && $this->status === 'draft') {
            $this->failureMessage = 'No puedes marcar como entregada una factura en borrador.';
            return false;
        }

        // Regla 4: Si está pagada, debe estar al menos aprobada
        if ($this->paymentStatus === 'paid' && $this->status === 'draft') {
            $this->failureMessage = 'No puedes marcar como pagada una factura en borrador.';
            return false;
        }

        // Regla 5: Si está cancelada, el pago no puede estar pending
        if ($this->status === 'cancelled' && $this->paymentStatus === 'unpaid') {
            // Esto es coherente, una factura cancelada puede quedar como unpaid
            return true;
        }

        return true;
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return $this->failureMessage ?? 'Los estados de la factura son incoherentes.';
    }
}
