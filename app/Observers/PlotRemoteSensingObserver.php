<?php

namespace App\Observers;

use App\Models\PlotRemoteSensing;
use App\Notifications\RemoteSensingAlertNotification;
use Illuminate\Support\Facades\Cache;

class PlotRemoteSensingObserver
{
    /**
     * Fire NDVI alert after any save (create or update).
     * Includes a 24-hour cooldown per plot to avoid notification spam.
     */
    public function saved(PlotRemoteSensing $record): void
    {
        $ndvi = $record->ndvi_mean;

        if ($ndvi === null) {
            return;
        }

        $plot = $record->plot()->with('viticulturist')->first();

        if (!$plot || !$plot->viticulturist) {
            return;
        }

        $threshold = (float) ($plot->ndvi_alert_threshold ?? 0.30);

        if ((float) $ndvi >= $threshold) {
            return; // NDVI is fine — no alert needed
        }

        // Cooldown: send at most one alert per plot per 24 hours
        $cacheKey = "ndvi_alert_sent_{$plot->id}";
        if (Cache::has($cacheKey)) {
            return;
        }

        Cache::put($cacheKey, true, now()->addHours(24));

        $plot->viticulturist->notify(
            new RemoteSensingAlertNotification($plot, (float) $ndvi, $threshold)
        );
    }
}
