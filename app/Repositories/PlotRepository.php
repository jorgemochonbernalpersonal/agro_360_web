<?php

namespace App\Repositories;

use App\Models\Plot;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PlotRepository
{
    /**
     * Encontrar parcelas para un usuario
     */
    public function findForUser(User $user, array $filters = []): Collection
    {
        $query = Plot::query()->forUser($user);

        return $this->applyFilters($query, $filters)->get();
    }

    /**
     * Encontrar parcelas paginadas
     */
    public function paginateForUser(User $user, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Plot::query()->forUser($user);

        return $this->applyFilters($query, $filters)->paginate($perPage);
    }

    /**
     * Encontrar parcelas activas
     */
    public function findActive(User $user): Collection
    {
        return Plot::query()
            ->forUser($user)
            ->active()
            ->withCommonRelations()
            ->orderByName()
            ->get();
    }

    /**
     * Encontrar parcelas con alertas NDVI habilitadas
     */
    public function findWithNdviAlerts(User $user): Collection
    {
        return Plot::query()
            ->forUser($user)
            ->withNdviAlerts()
            ->with(['viticulturist', 'latestRemoteSensing'])
            ->get();
    }

    /**
     * Buscar parcelas por texto
     */
    public function search(User $user, string $term, int $limit = 10): Collection
    {
        return Plot::query()
            ->forUser($user)
            ->active()
            ->search($term)
            ->withCommonRelations()
            ->limit($limit)
            ->get();
    }

    /**
     * Contar parcelas por viticultor
     */
    public function countByViticulturist(int $viticulturistId): int
    {
        return Plot::where('viticulturist_id', $viticulturistId)->count();
    }

    /**
     * Contar parcelas activas por viticultor
     */
    public function countActiveByViticulturist(int $viticulturistId): int
    {
        return Plot::where('viticulturist_id', $viticulturistId)
            ->active()
            ->count();
    }

    /**
     * Calcular área total por viticultor
     */
    public function totalAreaByViticulturist(int $viticulturistId): float
    {
        return (float) Plot::where('viticulturist_id', $viticulturistId)
            ->active()
            ->sum('area');
    }

    /**
     * Encontrar parcelas con plazo de seguridad activo
     */
    public function findWithActiveWithdrawalPeriods(User $user): Collection
    {
        return Plot::query()
            ->forUser($user)
            ->withActiveWithdrawalPeriods()
            ->with(['viticulturist', 'lastPhytosanitaryTreatment'])
            ->get();
    }

    /**
     * Encontrar parcelas bloqueadas
     */
    public function findLocked(User $user): Collection
    {
        return Plot::query()
            ->forUser($user)
            ->locked()
            ->with(['viticulturist', 'lockedBy'])
            ->orderBy('locked_at', 'desc')
            ->get();
    }

    /**
     * Encontrar parcelas por ubicación
     */
    public function findByLocation(
        User $user,
        ?int $autonomousCommunityId = null,
        ?int $provinceId = null,
        ?int $municipalityId = null
    ): Collection {
        $query = Plot::query()->forUser($user);

        if ($autonomousCommunityId) {
            $query->inAutonomousCommunity($autonomousCommunityId);
        }

        if ($provinceId) {
            $query->inProvince($provinceId);
        }

        if ($municipalityId) {
            $query->inMunicipality($municipalityId);
        }

        return $query->withCommonRelations()->get();
    }

    /**
     * Aplicar filtros a la query
     */
    protected function applyFilters($query, array $filters)
    {
        if (isset($filters['active']) && $filters['active']) {
            $query->active();
        }

        if (isset($filters['search'])) {
            $query->search($filters['search']);
        }

        if (isset($filters['province_id'])) {
            $query->inProvince($filters['province_id']);
        }

        if (isset($filters['municipality_id'])) {
            $query->inMunicipality($filters['municipality_id']);
        }

        if (isset($filters['min_area'])) {
            $query->withAreaGreaterThan($filters['min_area']);
        }

        if (isset($filters['max_area'])) {
            $query->withAreaLessThan($filters['max_area']);
        }

        if (isset($filters['with_ndvi_alerts']) && $filters['with_ndvi_alerts']) {
            $query->withNdviAlerts();
        }

        // Ordenamiento
        if (isset($filters['sort_by'])) {
            switch ($filters['sort_by']) {
                case 'name':
                    $query->orderByName($filters['sort_direction'] ?? 'asc');
                    break;
                case 'area':
                    $query->orderByArea($filters['sort_direction'] ?? 'desc');
                    break;
                default:
                    $query->orderByName();
            }
        } else {
            $query->orderByName();
        }

        return $query;
    }
}
