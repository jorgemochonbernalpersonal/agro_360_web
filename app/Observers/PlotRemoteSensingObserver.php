<?php

namespace App\Observers;

use App\Models\PlotAlertPreference;
use App\Models\PlotRemoteSensing;
use App\Notifications\RemoteSensingAlertNotification;
use Illuminate\Support\Facades\Cache;

class PlotRemoteSensingObserver
{
    /**
     * After any save, notify every user who has a personal alert preference
     * for this plot and whose threshold is breached.
     * Cooldown: one notification per (plot, user) every 24 hours.
     */
    public function saved(PlotRemoteSensing $record): void
    {
        $ndvi = $record->ndvi_mean;

        if ($ndvi === null) {
            return;
        }

        $plot = $record->plot()->first();

        if (!$plot) {
            return;
        }

        $preferences = PlotAlertPreference::where('plot_id', $plot->id)
            ->with('user')
            ->get();

        foreach ($preferences as $pref) {
            if (!$pref->user) {
                continue;
            }

            if ((float) $ndvi >= $pref->ndvi_threshold) {
                continue; // NDVI above this user's threshold — no alert
            }

            // Cooldown: one alert per (plot, user) per 24 hours
            $cacheKey = "ndvi_alert_sent_{$plot->id}_{$pref->user_id}";
            if (Cache::has($cacheKey)) {
                continue;
            }

            Cache::put($cacheKey, true, now()->addHours(24));

            $pref->user->notify(
                new RemoteSensingAlertNotification($plot, (float) $ndvi, $pref->ndvi_threshold, $pref->email_enabled)
            );
        }
    }
}
