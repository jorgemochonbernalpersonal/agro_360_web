<?php

namespace App\Jobs;

use App\Models\Plot;
use App\Services\RemoteSensing\CopernicusSentinel2Service;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Queue job that fetches Sentinel-2 vegetation indices for a single plot.
 * Uses plain integer IDs to avoid Eloquent model serialization issues.
 */
class UpdatePlotSentinel2Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 90;

    public int $backoff = 120;

    public function __construct(
        public readonly int $plotId,
        public readonly ?int $plotSigpacId = null,
    ) {}

    public function handle(CopernicusSentinel2Service $service): void
    {
        $plot = Plot::find($this->plotId);

        if (! $plot) {
            Log::warning('UpdatePlotSentinel2Job: plot not found', [
                'plot_id' => $this->plotId,
            ]);

            return;
        }

        $result = $service->fetchAndStore($plot, $this->plotSigpacId);

        if ($result) {
            Log::info('UpdatePlotSentinel2Job completed', [
                'plot_id' => $this->plotId,
                'plot_sigpac_id' => $this->plotSigpacId,
                'ndvi' => $result->ndvi_mean,
                'gndvi' => $result->gndvi,
                'image_date' => $result->image_date?->toDateString(),
            ]);
        } else {
            Log::warning('UpdatePlotSentinel2Job: no data returned', [
                'plot_id' => $this->plotId,
                'plot_sigpac_id' => $this->plotSigpacId,
            ]);
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('UpdatePlotSentinel2Job permanently failed', [
            'plot_id' => $this->plotId,
            'plot_sigpac_id' => $this->plotSigpacId,
            'error' => $e->getMessage(),
        ]);
    }
}
