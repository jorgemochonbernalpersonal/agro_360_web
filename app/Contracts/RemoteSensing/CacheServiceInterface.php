<?php

namespace App\Contracts\RemoteSensing;

use App\Models\Plot;

/**
 * Contract for cache management
 */
interface CacheServiceInterface
{
    /**
     * Get cache key for weather data
     */
    public function getWeatherKey(Plot $plot): string;

    /**
     * Get cache key for NDVI data
     */
    public function getNdviKey(Plot $plot): string;

    /**
     * Get cache key for forecast data
     */
    public function getForecastKey(Plot $plot, int $days): string;

    /**
     * Clear all cache for a plot
     */
    public function clearAllForPlot(Plot $plot): void;

    /**
     * Get TTL for weather data
     */
    public function getWeatherTtl(): int;

    /**
     * Get TTL for NDVI data
     */
    public function getNdviTtl(): int;
}
