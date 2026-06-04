<?php

namespace App\Services\Cache;

use Illuminate\Support\Facades\Cache;

class QueryCacheService
{
    /**
     * Cache a query result
     */
    public function remember(string $key, int $ttl, callable $callback): mixed
    {
        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Cache with tags
     */
    public function rememberWithTags(array $tags, string $key, int $ttl, callable $callback): mixed
    {
        return Cache::tags($tags)->remember($key, $ttl, $callback);
    }

    /**
     * Invalidate by tags
     */
    public function flushTags(array $tags): void
    {
        Cache::tags($tags)->flush();
    }

    /**
     * Invalidate specific key
     */
    public function forget(string $key): void
    {
        Cache::forget($key);
    }

    /**
     * Get geographic data (provinces, municipalities) - Long TTL
     */
    public function getProvinces(): \Illuminate\Support\Collection
    {
        return Cache::remember(
            'geography_provinces',
            604800, // 1 semana
            fn () => \App\Models\Province::with('autonomousCommunity')->orderBy('name')->get()
        );
    }

    /**
     * Get municipalities by province
     */
    public function getMunicipalities(int $provinceId): \Illuminate\Support\Collection
    {
        return Cache::remember(
            "geography_municipalities_province_{$provinceId}",
            604800, // 1 semana
            fn () => \App\Models\Municipality::where('province_id', $provinceId)
                ->orderBy('name')
                ->get()
        );
    }

    /**
     * Get catalog data (grape varieties, training systems, etc.)
     */
    public function getGrapeVarieties(): \Illuminate\Support\Collection
    {
        return Cache::remember(
            'catalog_grape_varieties',
            604800, // 1 semana
            fn () => \App\Models\GrapeVariety::orderBy('name')->get()
        );
    }

    /**
     * Get training systems
     */
    public function getTrainingSystems(): \Illuminate\Support\Collection
    {
        return Cache::remember(
            'catalog_training_systems',
            604800, // 1 semana
            fn () => \App\Models\TrainingSystem::orderBy('name')->get()
        );
    }

    /**
     * Get taxes
     */
    public function getTaxes(): \Illuminate\Support\Collection
    {
        return Cache::remember(
            'catalog_taxes',
            604800, // 1 semana
            fn () => \App\Models\Tax::orderBy('rate')->get()
        );
    }

    /**
     * Invalidate all catalog caches
     */
    public function invalidateCatalogs(): void
    {
        $keys = [
            'catalog_grape_varieties',
            'catalog_training_systems',
            'catalog_taxes',
            'geography_provinces',
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }

        Cache::tags(['catalogs', 'geography'])->flush();
    }
}
