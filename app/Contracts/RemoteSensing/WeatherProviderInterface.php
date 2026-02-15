<?php

namespace App\Contracts\RemoteSensing;

use App\Models\Plot;

/**
 * Contract for weather data providers (Open-Meteo, etc.)
 */
interface WeatherProviderInterface
{
    /**
     * Get current weather data for a plot
     */
    public function getCurrentWeather(Plot $plot, bool $forceRefresh = false): array;

    /**
     * Get weather forecast for a plot
     */
    public function getForecast(Plot $plot, int $days = 7, bool $forceRefresh = false): array;

    /**
     * Get soil data for a plot
     */
    public function getSoilData(Plot $plot, bool $forceRefresh = false): array;

    /**
     * Get solar radiation data for a plot
     */
    public function getSolarData(Plot $plot, bool $forceRefresh = false): array;
}
