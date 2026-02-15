<?php

namespace App\DataTransferObjects;

class PendingSubscriptionData
{
    public function __construct(
        public readonly int $paymentId,
        public readonly string $planType,
        public readonly float $amount,
        public readonly ?string $paypalOrderId = null
    ) {}

    /**
     * Crear desde array de sesión
     */
    public static function fromSession(array $data): self
    {
        return new self(
            paymentId: $data['payment_id'],
            planType: $data['plan_type'],
            amount: $data['amount'],
            paypalOrderId: $data['paypal_order_id'] ?? null
        );
    }

    /**
     * Convertir a array para guardar en sesión
     */
    public function toArray(): array
    {
        return [
            'payment_id' => $this->paymentId,
            'plan_type' => $this->planType,
            'amount' => $this->amount,
            'paypal_order_id' => $this->paypalOrderId,
        ];
    }

    /**
     * Verificar si es plan anual
     */
    public function isYearly(): bool
    {
        return $this->planType === 'yearly';
    }

    /**
     * Verificar si es plan mensual
     */
    public function isMonthly(): bool
    {
        return $this->planType === 'monthly';
    }
}
