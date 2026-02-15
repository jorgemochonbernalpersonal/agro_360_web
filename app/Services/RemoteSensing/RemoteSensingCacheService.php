<?php

namespace App\Services\RemoteSensing;

use App\Contracts\RemoteSensing\CacheServiceInterface;
use App\Models\Plot;
use Illuminate\Support\Facades\Cache;

/**
 * Centralized cache management for remote sensing data
 */
class RemoteSensingCacheService implements CacheServiceInterface
{
    // Cache TTLs (Time To Live)
    private const TTL_WEATHER = 3600;        // 1 hour
    private const TTL_NDVI = 86400;          // 24 hours
    private const TTL_FORECAST = 7200;       // 2 hours
    private const TTL_SOIL = 3600;           // 1 hour
    private const TTL_SOLAR = 3600;          // 1 hour
    private const TTL_NASA_TOKEN = 86400;    // 24 hours

    // Cache key prefixes
    private const PREFIX = 'remote_sensing';
    private const PREFIX_WEATHER = self::PREFIX . ':weather';
    private const PREFIX_NDVI = self::PREFIX . ':ndvi';
    private const PREFIX_FORECAST = self::PREFIX . ':forecast';
    private const PREFIX_SOIL = self::PREFIX . ':soil';
    private const PREFIX_SOLAR = self::PREFIX . ':solar';
    private const PREFIX_NASA_TOKEN = self::PREFIX . ':nasa_token';

    /**
     * Get cache key for weather data
     */
    public function getWeatherKey(Plot $plot): string
    {
        return self::PREFIX_WEATHER . ":{$plot->id}";
    }

    /**
     * Get cache key for NDVI data
     */
    public function getNdviKey(Plot $plot): string
    {
        return self::PREFIX_NDVI . ":latest:{$plot->id}";
    }

    /**
     * Get cache key for historical NDVI data
     */
    public function getNdviHistoricalKey(Plot $plot): string
    {
        return self::PREFIX_NDVI . ":historical:{$plot->id}";
    }

    /**
     * Get cache key for forecast data
     */
    public function getForecastKey(Plot $plot, int $days): string
    {
        return self::PREFIX_FORECAST . ":{$plot->id}:{$days}";
    }

    /**
     * Get cache key for soil data
     */
    public function getSoilKey(Plot $plot): string
    {
        return self::PREFIX_SOIL . ":{$plot->id}";
    }

    /**
     * Get cache key for solar data
     */
    public function getSolarKey(Plot $plot): string
    {
        return self::PREFIX_SOLAR . ":{$plot->id}";
    }

    /**
     * Get cache key for NASA token
     */
    public function getNasaTokenKey(): string
    {
        return self::PREFIX_NASA_TOKEN;
    }

    /**
     * Get TTL for weather data
     */
    public function getWeatherTtl(): int
    {
        return self::TTL_WEATHER;
    }

    /**
     * Get TTL for NDVI data
     */
    public function getNdviTtl(): int
    {
        return self::TTL_NDVI;
    }

    /**
     * Get TTL for forecast data
     */
    public function getForecastTtl(): int
    {
        return self::TTL_FORECAST;
    }

    /**
     * Get TTL for soil data
     */
    public function getSoilTtl(): int
    {
        return self::TTL_SOIL;
    }

    /**
     * Get TTL for solar data
     */
    public function getSolarTtl(): int
    {
        return self::TTL_SOLAR;
    }

    /**
     * Get TTL for NASA token
     */
    public function getNasaTokenTtl(): int
    {
        return self::TTL_NASA_TOKEN;
    }

    /**
     * Get from cache with a custom key
     */
    public function get(string $key)
    {
        return Cache::get($key);
    }

    /**
     * Put in cache with a custom key and TTL
     */
    public function put(string $key, $value, int $ttl): void
    {
        Cache::put($key, $value, $ttl);
    }

    /**
     * Forget a specific cache key
     */
    public function forget(string $key): void
    {
        Cache::forget($key);
    }

    /**
     * Clear all cache for a plot
     */
    public function clearAllForPlot(Plot $plot): void
    {
        Cache::forget($this->getWeatherKey($plot));
        Cache::forget($this->getNdviKey($plot));
        Cache::forget($this->getNdviHistoricalKey($plot));
        Cache::forget($this->getSoilKey($plot));
        Cache::forget($this->getSolarKey($plot));
        
        // Clear all forecast variants (1-7 days)
        for ($days = 1; $days <= 7; $days++) {
            Cache::forget($this->getForecastKey($plot, $days));
        }
    }

    /**
     * Clear weather cache for a plot
     */
    public function clearWeatherForPlot(Plot $plot): void
    {
        Cache::forget($this->getWeatherKey($plot));
        Cache::forget($this->getSoilKey($plot));
        Cache::forget($this->getSolarKey($plot));
        
        for ($days = 1; $days <= 7; $days++) {
            Cache::forget($this->getForecastKey($plot, $days));
        }
    }

    /**
     * Clear NDVI cache for a plot
     */
    public function clearNdviForPlot(Plot $plot): void
    {
        Cache::forget($this->getNdviKey($plot));
        Cache::forget($this->getNdviHistoricalKey($plot));
    }

    /**
     * Clear NASA token cache
     */
    public function clearNasaToken(): void
    {
        Cache::forget($this->getNasaTokenKey());
    }

    /**
     * Clear all remote sensing cache
     */
    public function clearAll(): void
    {
        Cache::tags([self::PREFIX])->flush();
    }
}
