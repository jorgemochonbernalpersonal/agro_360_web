<?php

namespace App\Listeners;

use App\Events\ActivityCreated;
use App\Notifications\WithdrawalPeriodEnding;
use Illuminate\Support\Facades\Log;

class CheckWithdrawalPeriodAlerts
{
    /**
     * Handle the event.
     */
    public function handle(ActivityCreated $event): void
    {
        $activity = $event->activity;

        // Solo procesar si es tratamiento fitosanitario
        if ($activity->activity_type !== 'phytosanitary') {
            return;
        }

        $treatment = $activity->phytosanitaryTreatment;

        if (! $treatment || ! $treatment->product) {
            return;
        }

        $product = $treatment->product;

        if (! $product->withdrawal_period_days) {
            return;
        }

        // Calcular fecha de seguridad
        $safeDate = $activity->activity_date->copy()->addDays($product->withdrawal_period_days);

        Log::info('Tratamiento fitosanitario registrado con plazo de seguridad', [
            'activity_id' => $activity->id,
            'plot_id' => $activity->plot_id,
            'product_name' => $product->name,
            'withdrawal_days' => $product->withdrawal_period_days,
            'safe_date' => $safeDate->toDateString(),
        ]);

        $daysRemaining = (int) now()->diffInDays($safeDate, false);

        if ($daysRemaining >= 0 && $daysRemaining <= $product->withdrawal_period_days) {
            $activity->user->notify(new WithdrawalPeriodEnding($treatment, $safeDate, $daysRemaining));
        }
    }
}
