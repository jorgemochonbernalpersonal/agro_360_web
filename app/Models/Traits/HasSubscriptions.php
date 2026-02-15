<?php

namespace App\Models\Traits;

use App\Models\Payment;
use App\Models\Subscription;

trait HasSubscriptions
{
    /**
     * Relación con suscripciones
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Suscripción activa
     */
    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)
            ->where('status', Subscription::STATUS_ACTIVE)
            ->where('ends_at', '>', now());
    }

    /**
     * Pagos del usuario
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Verificar si el usuario tiene una suscripción activa
     */
    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription()->exists();
    }
}
