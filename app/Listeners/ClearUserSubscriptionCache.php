<?php

namespace App\Listeners;

use App\Events\SubscriptionCreated;
use Illuminate\Support\Facades\Cache;

class ClearUserSubscriptionCache
{
    /**
     * Handle the event.
     */
    public function handle(SubscriptionCreated $event): void
    {
        $subscription = $event->subscription;

        // Limpiar cache de suscripción del usuario
        Cache::forget("user_{$subscription->user_id}_active_subscription");
        Cache::forget("user_{$subscription->user_id}_subscription_status");
    }
}
