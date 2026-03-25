<?php

namespace App\Services\Cache;

use App\Models\Plot;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class PlotCacheService
{
    /**
     * Cache duration in seconds (12 hours)
     */
    private const CACHE_TTL = 43200;

    /**
     * Get user plots with relations (cached)
     */
    public function getUserPlots(User $user, array $with = []): Collection
    {
        $cacheKey = $this->getUserPlotsKey($user, $with);

        return Cache::remember(
            $cacheKey,
            self::CACHE_TTL,
            function () use ($user, $with) {
                $query = $user->plots()->active();
                
                if (!empty($with)) {
                    $query->with($with);
                }

                return $query->get();
            }
        );
    }

    /**
     * Get plot with full details (cached)
     */
    public function getPlotDetails(Plot $plot): Plot
    {
        return Cache::remember(
            "plot_{$plot->id}_details",
            self::CACHE_TTL,
            fn() => $plot->load([
                'viticulturist',
                'province',
                'municipality',
                'plantings.grapeVariety',
                'latestRemoteSensing',
            ])
        );
    }

    /**
     * Get plot statistics (cached)
     */
    public function getPlotStats(Plot $plot): array
    {
        return Cache::remember(
            "plot_{$plot->id}_stats",
            self::CACHE_TTL,
            fn() => [
                'total_activities' => $plot->agriculturalActivities()->count(),
                'total_area' => $plot->area,
                'plantings_count' => $plot->plantings()->count(),
                'last_activity_date' => $plot->agriculturalActivities()->max('activity_date'),
                'activities_this_month' => $plot->agriculturalActivities()
                    ->whereMonth('activity_date', now()->month)
                    ->whereYear('activity_date', now()->year)
                    ->count(),
            ]
        );
    }

    /**
     * Invalidate user plots cache
     */
    public function invalidateUserPlots(User $user): void
    {
        // Invalidar todas las variaciones de cache de plots del usuario
        Cache::forget("user_{$user->id}_plots");
        try {
            Cache::tags(['user_plots', "user_{$user->id}"])->flush();
        } catch (\BadMethodCallException) {
            // Cache store doesn't support tags — key-based forget is sufficient
        }
    }

    /**
     * Invalidate specific plot cache
     */
    public function invalidatePlot(Plot $plot): void
    {
        Cache::forget("plot_{$plot->id}_details");
        Cache::forget("plot_{$plot->id}_stats");

        if ($plot->viticulturist) {
            $this->invalidateUserPlots($plot->viticulturist);
        }

        try {
            Cache::tags(['plots', "plot_{$plot->id}"])->flush();
        } catch (\BadMethodCallException) {
            // Cache store doesn't support tags — key-based forget is sufficient
        }
    }

    /**
     * Warm up cache for user
     */
    public function warmUpUserCache(User $user): void
    {
        // Precalentar cache de plots del usuario
        $this->getUserPlots($user, ['plantings', 'province', 'municipality']);
    }

    /**
     * Get cache key for user plots
     */
    protected function getUserPlotsKey(User $user, array $with): string
    {
        $withKey = empty($with) ? 'basic' : md5(implode(',', $with));
        return "user_{$user->id}_plots_{$withKey}";
    }
}
