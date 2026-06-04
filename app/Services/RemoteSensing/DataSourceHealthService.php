<?php

namespace App\Services\RemoteSensing;

use App\Models\PlotRemoteSensing;
use Illuminate\Support\Facades\Cache;

/**
 * Reports the availability and status of each remote sensing data source.
 * Used by the Dashboard to show small health badges next to the refresh button.
 */
class DataSourceHealthService
{
    public function __construct(
        private RateLimitService $rateLimitService,
    ) {}

    /**
     * Returns an array of data source status descriptors.
     */
    public function getStatus(): array
    {
        $copernicusUsage = $this->rateLimitService->getCopernicusMonthlyUsage();
        $copernicusLimit = 30000;
        $copernicusWarning = $this->rateLimitService->isCopernicusLimitApproaching();
        $copernicusOk = $this->rateLimitService->canUseCopernicus();

        return [
            'copernicus' => [
                'available' => Cache::has('copernicus_access_token'),
                'mock' => false,
                'label' => __('Sentinel-2'),
                'last_fetch' => $this->getLastFetchDate(),
                'monthly_usage' => $copernicusUsage,
                'monthly_limit' => $copernicusLimit,
                'limit_warning' => $copernicusWarning,
                'limit_ok' => $copernicusOk,
            ],
            'nasa_lai' => [
                'available' => true,
                'mock' => config('services.nasa_earthdata.mock', false),
                'label' => __('NASA LAI'),
            ],
            'open_meteo' => [
                'available' => true,
                'mock' => config('services.open_meteo.mock', false),
                'label' => __('Open-Meteo'),
            ],
        ];
    }

    /**
     * ISO date of the most recently stored Sentinel-2 image across all plots.
     */
    private function getLastFetchDate(): ?string
    {
        $latest = PlotRemoteSensing::where('data_source', 'copernicus')
            ->orderBy('updated_at', 'desc')
            ->value('updated_at');

        return $latest ? \Carbon\Carbon::parse($latest)->toDateString() : null;
    }
}
