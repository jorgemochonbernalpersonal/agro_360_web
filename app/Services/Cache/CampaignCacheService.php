<?php

namespace App\Services\Cache;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class CampaignCacheService
{
    /**
     * Cache duration in seconds (24 hours)
     */
    private const CACHE_TTL = 86400;

    /**
     * Get campaign statistics (cached)
     */
    public function getStats(Campaign $campaign): array
    {
        return Cache::remember(
            $this->getStatsKey($campaign),
            self::CACHE_TTL,
            fn() => $this->calculateStats($campaign)
        );
    }

    /**
     * Get user campaigns (cached)
     */
    public function getUserCampaigns(User $user): \Illuminate\Support\Collection
    {
        return Cache::remember(
            "user_{$user->id}_campaigns",
            self::CACHE_TTL,
            fn() => $user->campaigns()->with('activities')->get()
        );
    }

    /**
     * Invalidate campaign stats cache
     */
    public function invalidateStats(Campaign $campaign): void
    {
        Cache::forget($this->getStatsKey($campaign));
    }

    /**
     * Invalidate user campaigns cache
     */
    public function invalidateUserCampaigns(User $user): void
    {
        Cache::forget("user_{$user->id}_campaigns");
    }

    /**
     * Invalidate all campaign-related caches
     */
    public function invalidateAll(Campaign $campaign): void
    {
        $this->invalidateStats($campaign);
        
        if ($campaign->user) {
            $this->invalidateUserCampaigns($campaign->user);
        }

        // Invalidar cache de tags
        Cache::tags(['campaigns', "campaign_{$campaign->id}"])->flush();
    }

    /**
     * Calculate campaign statistics
     */
    protected function calculateStats(Campaign $campaign): array
    {
        return [
            'total_activities' => $campaign->activities()->count(),
            'activities_by_type' => $campaign->activities()
                ->selectRaw('activity_type, COUNT(*) as count')
                ->groupBy('activity_type')
                ->pluck('count', 'activity_type')
                ->toArray(),
            'total_plots' => $campaign->activities()
                ->distinct('plot_id')
                ->count('plot_id'),
            'date_range' => [
                'start' => $campaign->activities()->min('activity_date'),
                'end' => $campaign->activities()->max('activity_date'),
            ],
            'calculated_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Get cache key for stats
     */
    protected function getStatsKey(Campaign $campaign): string
    {
        return "campaign_{$campaign->id}_stats";
    }
}
