<?php

namespace App\Console\Commands;

use App\Models\PlotAlertPreference;
use App\Notifications\RemoteSensingAlertNotification;
use App\Services\RemoteSensing\NasaEarthdataService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CheckRemoteSensingAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alerts:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check remote sensing data for all plots and send alerts if thresholds are breached';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting remote sensing alert check...');

        $preferences = PlotAlertPreference::with(['plot', 'user'])->get();

        $service = app(NasaEarthdataService::class);
        $alertsSent = 0;

        foreach ($preferences as $pref) {
            if (! $pref->plot || ! $pref->user) {
                continue;
            }

            try {
                $data = $service->getLatestData($pref->plot);

                if (! $data || $data->ndvi_mean >= $pref->ndvi_threshold) {
                    continue;
                }

                $cacheKey = "ndvi_alert_sent_{$pref->plot_id}_{$pref->user_id}";

                if (Cache::has($cacheKey)) {
                    $this->line("Skipped {$pref->plot->name} / {$pref->user->name} — cooldown active");

                    continue;
                }

                Cache::put($cacheKey, true, now()->addHours(24));

                $pref->user->notify(new RemoteSensingAlertNotification(
                    $pref->plot,
                    $data->ndvi_mean,
                    $pref->ndvi_threshold,
                    $pref->email_enabled
                ));

                $this->info("Alert sent → {$pref->plot->name} / {$pref->user->name} (NDVI: {$data->ndvi_mean} < {$pref->ndvi_threshold})");
                $alertsSent++;

            } catch (\Exception $e) {
                Log::error("Error checking alert for plot {$pref->plot_id} / user {$pref->user_id}: ".$e->getMessage());
                $this->error("Error: plot {$pref->plot_id} / user {$pref->user_id}");
            }
        }

        $this->info("Done. Sent {$alertsSent} alerts.");
    }
}
