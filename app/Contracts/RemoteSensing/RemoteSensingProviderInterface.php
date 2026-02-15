<?php

namespace App\Contracts\RemoteSensing;

use App\Models\Plot;
use App\Models\PlotRemoteSensing;
use Illuminate\Support\Collection;

/**
 * Contract for remote sensing data providers (NASA, Copernicus, etc.)
 */
interface RemoteSensingProviderInterface
{
    /**
     * Get the latest remote sensing data for a plot
     */
    public function getLatestData(Plot $plot, bool $forceRefresh = false): ?PlotRemoteSensing;

    /**
     * Get historical remote sensing data for a plot
     */
    public function getHistoricalData(Plot $plot, int $days = 90): Collection;

    /**
     * Fetch and store remote sensing data
     */
    public function fetchAndStoreNdvi(Plot $plot): ?PlotRemoteSensing;

    /**
     * Clear cached data for a plot
     */
    public function clearCache(Plot $plot): void;
}
