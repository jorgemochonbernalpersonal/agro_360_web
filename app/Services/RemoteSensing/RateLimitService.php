<?php

namespace App\Services\RemoteSensing;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Service to manage API rate limiting
 * Prevents hitting API limits and potential bans
 */
class RateLimitService
{
    // NASA AppEEARS rate limits (conservative approach)
    private const MAX_REQUESTS_PER_HOUR = 50;
    private const MAX_REQUESTS_PER_DAY = 500;
    
    // Open-Meteo rate limits (they're more permissive but we limit anyway)
    private const OPEN_METEO_MAX_PER_HOUR = 100;
    private const OPEN_METEO_MAX_PER_DAY = 1000;

    /**
     * Check if we can make a request to NASA API
     */
    public function canMakeNasaRequest(): bool
    {
        return $this->canMakeRequest('nasa');
    }

    /**
     * Check if we can make a request to Open-Meteo API
     */
    public function canMakeOpenMeteoRequest(): bool
    {
        return $this->canMakeRequest('open_meteo');
    }

    /**
     * Check if we can make a request to a specific service
     */
    public function canMakeRequest(string $service): bool
    {
        $limits = $this->getLimits($service);
        
        $hourKey = $this->getHourKey($service);
        $dayKey = $this->getDayKey($service);
        
        $hourCount = Cache::get($hourKey, 0);
        $dayCount = Cache::get($dayKey, 0);
        
        $canMake = $hourCount < $limits['hour'] && $dayCount < $limits['day'];
        
        if (!$canMake) {
            Log::warning("Rate limit reached for {$service}", [
                'hour_count' => $hourCount,
                'day_count' => $dayCount,
                'limits' => $limits,
            ]);
        }
        
        return $canMake;
    }

    /**
     * Record a request to NASA API
     */
    public function recordNasaRequest(): void
    {
        $this->recordRequest('nasa');
    }

    /**
     * Record a request to Open-Meteo API
     */
    public function recordOpenMeteoRequest(): void
    {
        $this->recordRequest('open_meteo');
    }

    /**
     * Record a request to a specific service
     */
    public function recordRequest(string $service): void
    {
        $hourKey = $this->getHourKey($service);
        $dayKey = $this->getDayKey($service);
        
        // Increment hour counter (TTL: 1 hour)
        if (!Cache::has($hourKey)) {
            Cache::put($hourKey, 0, 3600);
        }
        Cache::increment($hourKey);
        
        // Increment day counter (TTL: 24 hours)
        if (!Cache::has($dayKey)) {
            Cache::put($dayKey, 0, 86400);
        }
        Cache::increment($dayKey);
    }

    /**
     * Get current usage for a service
     */
    public function getUsage(string $service): array
    {
        $hourKey = $this->getHourKey($service);
        $dayKey = $this->getDayKey($service);
        $limits = $this->getLimits($service);
        
        $hourCount = Cache::get($hourKey, 0);
        $dayCount = Cache::get($dayKey, 0);
        
        return [
            'service' => $service,
            'hour' => [
                'used' => $hourCount,
                'limit' => $limits['hour'],
                'remaining' => max(0, $limits['hour'] - $hourCount),
                'percentage' => round(($hourCount / $limits['hour']) * 100, 2),
            ],
            'day' => [
                'used' => $dayCount,
                'limit' => $limits['day'],
                'remaining' => max(0, $limits['day'] - $dayCount),
                'percentage' => round(($dayCount / $limits['day']) * 100, 2),
            ],
        ];
    }

    /**
     * Reset counters for a service (use with caution)
     */
    public function resetCounters(string $service): void
    {
        Cache::forget($this->getHourKey($service));
        Cache::forget($this->getDayKey($service));
        
        Log::info("Rate limit counters reset for {$service}");
    }

    /**
     * Get all services usage
     */
    public function getAllUsage(): array
    {
        return [
            'nasa' => $this->getUsage('nasa'),
            'open_meteo' => $this->getUsage('open_meteo'),
        ];
    }

    /**
     * Get cache key for hourly counter
     */
    private function getHourKey(string $service): string
    {
        return "rate_limit:{$service}:hour:" . now()->format('Y-m-d-H');
    }

    /**
     * Get cache key for daily counter
     */
    private function getDayKey(string $service): string
    {
        return "rate_limit:{$service}:day:" . now()->format('Y-m-d');
    }

    /**
     * Get rate limits for a service
     */
    private function getLimits(string $service): array
    {
        return match ($service) {
            'nasa' => [
                'hour' => self::MAX_REQUESTS_PER_HOUR,
                'day' => self::MAX_REQUESTS_PER_DAY,
            ],
            'open_meteo' => [
                'hour' => self::OPEN_METEO_MAX_PER_HOUR,
                'day' => self::OPEN_METEO_MAX_PER_DAY,
            ],
            default => [
                'hour' => 10,
                'day' => 100,
            ],
        };
    }

    /**
     * Wait until we can make a request (blocking)
     * Use with caution - can block for up to an hour
     */
    public function waitForAvailability(string $service, int $maxWaitSeconds = 60): bool
    {
        $waited = 0;
        $interval = 5; // Check every 5 seconds
        
        while (!$this->canMakeRequest($service) && $waited < $maxWaitSeconds) {
            sleep($interval);
            $waited += $interval;
        }
        
        return $this->canMakeRequest($service);
    }
}
