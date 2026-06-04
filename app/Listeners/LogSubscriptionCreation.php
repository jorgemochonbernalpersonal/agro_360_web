<?php

namespace App\Listeners;

use App\Events\SubscriptionCreated;
use App\Services\SecurityLogger;
use Illuminate\Support\Facades\Log;

class LogSubscriptionCreation
{
    /**
     * Handle the event.
     */
    public function handle(SubscriptionCreated $event): void
    {
        $subscription = $event->subscription;

        Log::info('Nueva suscripción creada', [
            'subscription_id' => $subscription->id,
            'user_id' => $subscription->user_id,
            'plan_type' => $subscription->plan_type,
            'amount' => $subscription->amount,
            'starts_at' => $subscription->starts_at,
            'ends_at' => $subscription->ends_at,
        ]);

        // Log de seguridad
        SecurityLogger::log([
            'event' => 'subscription_created',
            'user_id' => $subscription->user_id,
            'subscription_id' => $subscription->id,
            'plan_type' => $subscription->plan_type,
        ]);
    }
}
