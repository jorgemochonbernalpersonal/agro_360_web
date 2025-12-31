<?php

namespace App\Services;

use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Servicio centralizado para cachear queries relacionados con viticultores
 * Reduce carga en base de datos para queries frecuentes
 */
class ViticulturistCacheService
{
    /**
     * TTL del cache en segundos (5 minutos)
     */
    private const CACHE_TTL = 300;

    /**
     * Obtener IDs de viticultores visibles para un viticultor
     * Cachea el resultado para evitar queries repetidas
     * 
     * @param User $viticulturist
     * @param int|null $wineryId Opcional: filtrar por winery específica
     * @return Collection<int> IDs de viticultores visibles
     */
    public function getVisibleViticulturistIds(User $viticulturist, ?int $wineryId = null): Collection
    {
        if (!$viticulturist->isViticulturist()) {
            return collect();
        }

        $cacheKey = $this->getCacheKey('visible_ids', $viticulturist->id, $wineryId);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($viticulturist, $wineryId) {
            return WineryViticulturist::visibleTo($viticulturist, $wineryId)
                ->pluck('viticulturist_id')
                ->unique();
        });
    }

    /**
     * Obtener viticultores editables por un viticultor
     * Solo incluye viticultores creados directamente por él
     * 
     * @param User $viticulturist
     * @return Collection<int> IDs de viticultores editables
     */
    public function getEditableViticulturistIds(User $viticulturist): Collection
    {
        if (!$viticulturist->isViticulturist()) {
            return collect();
        }

        $cacheKey = $this->getCacheKey('editable_ids', $viticulturist->id);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($viticulturist) {
            return WineryViticulturist::editableBy($viticulturist)
                ->pluck('viticulturist_id')
                ->unique();
        });
    }

    /**
     * Obtener todos los IDs de parcelas visibles para un viticultor
     * Incluye sus parcelas y parcelas de viticultores visibles
     * 
     * @param User $viticulturist
     * @return Collection<int> IDs de parcelas visibles
     */
    public function getVisiblePlotIds(User $viticulturist): Collection
    {
        if (!$viticulturist->isViticulturist()) {
            return collect();
        }

        $cacheKey = $this->getCacheKey('plot_ids', $viticulturist->id);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($viticulturist) {
            // Obtener IDs de viticultores visibles
            $visibleViticulturistIds = $this->getVisibleViticulturistIds($viticulturist);
            
            // Añadir el propio viticultor
            $allViticulturistIds = $visibleViticulturistIds->push($viticulturist->id)->unique();
            
            // Obtener IDs de parcelas
            return \App\Models\Plot::whereIn('viticulturist_id', $allViticulturistIds)
                ->pluck('id');
        });
    }

    /**
     * Limpiar cache de un viticultor específico
     * Útil cuando se crean/eliminan relaciones
     * 
     * @param int $viticulturistId
     * @return void
     */
    public function clearCache(int $viticulturistId): void
    {
        // Limpiar todos los tipos de cache para este viticultor
        $patterns = ['visible_ids', 'editable_ids', 'plot_ids'];
        
        foreach ($patterns as $pattern) {
            // Sin winery
            Cache::forget($this->getCacheKey($pattern, $viticulturistId));
            
            // Con winery (probar varias wineries comunes)
            // Nota: Esto es una limitación del cache por clave
            // En producción, considerar usar tags de cache (Redis)
            for ($wineryId = 1; $wineryId <= 100; $wineryId++) {
                Cache::forget($this->getCacheKey($pattern, $viticulturistId, $wineryId));
            }
        }
    }

    /**
     * Limpiar todo el cache de viticultores
     * Útil para mantenimiento o después de migraciones
     * 
     * @return void
     */
    public function clearAllCache(): void
    {
        // Nota: Esto solo funciona con drivers de cache que soporten flush
        // Para producción, usar tags de cache (Redis)
        Cache::flush();
    }

    /**
     * Generar clave de cache consistente
     * 
     * @param string $type
     * @param int $viticulturistId
     * @param int|null $wineryId
     * @return string
     */
    private function getCacheKey(string $type, int $viticulturistId, ?int $wineryId = null): string
    {
        $key = "viticulturist_cache:{$type}:{$viticulturistId}";
        
        if ($wineryId !== null) {
            $key .= ":winery_{$wineryId}";
        }
        
        return $key;
    }

    /**
     * Verificar si el cache está habilitado
     * Útil para debugging o testing
     * 
     * @return bool
     */
    public function isCacheEnabled(): bool
    {
        return config('cache.default') !== 'array';
    }

    /**
     * Obtener estadísticas de cache (si están disponibles)
     * 
     * @param int $viticulturistId
     * @return array
     */
    public function getCacheStats(int $viticulturistId): array
    {
        $stats = [];
        $patterns = ['visible_ids', 'editable_ids', 'plot_ids'];
        
        foreach ($patterns as $pattern) {
            $key = $this->getCacheKey($pattern, $viticulturistId);
            $stats[$pattern] = Cache::has($key) ? 'cached' : 'not_cached';
        }
        
        return $stats;
    }
}
