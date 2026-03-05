<?php

namespace App\Repositories;

use App\Models\AgriculturalActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AgriculturalActivityRepository
{
    /**
     * Encontrar actividades para un viticultor con filtros
     */
    public function findForViticulturist(
        User $user,
        array $filters = [],
        int $perPage = 10
    ): LengthAwarePaginator {
        $query = AgriculturalActivity::forViticulturist($user->id)
            ->with(['plot', 'plotPlanting.grapeVariety', 'crew', 'crewMember.viticulturist', 'campaign',
                    'phytosanitaryTreatment.product', 'fertilization', 'irrigation', 'culturalWork',
                    'observation', 'harvest', 'postHarvestTreatment.product'])
            ->orderBy('activity_date', 'desc');

        // Aplicar filtros
        $query = $this->applyFilters($query, $filters);

        return $query->paginate($perPage);
    }

    /**
     * Obtener estadísticas de actividades por campaña
     */
    public function getStatsForCampaign(int $userId, int $campaignId, array $filters = []): array
    {
        $query = AgriculturalActivity::forViticulturist($userId)
            ->forCampaign($campaignId);

        // Aplicar filtros adicionales
        if (isset($filters['plot_id'])) {
            $query->forPlot($filters['plot_id']);
        }
        if (isset($filters['date_from'])) {
            $query->where('activity_date', '>=', $filters['date_from']);
        }
        if (isset($filters['date_to'])) {
            $query->where('activity_date', '<=', $filters['date_to']);
        }

        // Obtener estadísticas en una sola query
        $stats = $query
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN activity_type = ? THEN 1 ELSE 0 END) as phytosanitary,
                SUM(CASE WHEN activity_type = ? THEN 1 ELSE 0 END) as fertilization,
                SUM(CASE WHEN activity_type = ? THEN 1 ELSE 0 END) as irrigation,
                SUM(CASE WHEN activity_type = ? THEN 1 ELSE 0 END) as cultural,
                SUM(CASE WHEN activity_type = ? THEN 1 ELSE 0 END) as observation,
                SUM(CASE WHEN activity_type = ? THEN 1 ELSE 0 END) as harvest
            ', ['phytosanitary', 'fertilization', 'irrigation', 'cultural', 'observation', 'harvest'])
            ->first();

        return [
            'total' => $stats->total ?? 0,
            'phytosanitary' => $stats->phytosanitary ?? 0,
            'fertilization' => $stats->fertilization ?? 0,
            'irrigation' => $stats->irrigation ?? 0,
            'cultural' => $stats->cultural ?? 0,
            'observation' => $stats->observation ?? 0,
            'harvest' => $stats->harvest ?? 0,
        ];
    }

    /**
     * Obtener actividades recientes
     */
    public function getRecentActivities(int $userId, int $limit = 5): Collection
    {
        return AgriculturalActivity::forViticulturist($userId)
            ->with(['plot:id,name', 'plotPlanting.grapeVariety:id,name'])
            ->orderBy('activity_date', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Obtener actividades por tipo
     */
    public function getByType(int $userId, string $type, int $limit = null): Collection
    {
        $query = AgriculturalActivity::forViticulturist($userId)
            ->ofType($type)
            ->with(['plot', 'plotPlanting.grapeVariety'])
            ->orderBy('activity_date', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Contar actividades por parcela
     */
    public function countByPlot(int $userId, int $plotId, ?int $campaignId = null): int
    {
        $query = AgriculturalActivity::forViticulturist($userId)
            ->forPlot($plotId);

        if ($campaignId) {
            $query->forCampaign($campaignId);
        }

        return $query->count();
    }

    /**
     * Aplicar filtros a la query
     */
    protected function applyFilters($query, array $filters)
    {
        if (isset($filters['campaign_id'])) {
            $query->forCampaign($filters['campaign_id']);
        }

        if (isset($filters['plot_id'])) {
            $query->forPlot($filters['plot_id']);
        }

        if (isset($filters['activity_type'])) {
            $query->ofType($filters['activity_type']);
        }

        if (isset($filters['date_from'])) {
            $query->where('activity_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('activity_date', '<=', $filters['date_to']);
        }

        if (isset($filters['search'])) {
            $search = '%' . strtolower($filters['search']) . '%';
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(notes) LIKE ?', [$search])
                  ->orWhereHas('plot', function($plotQuery) use ($search) {
                      $plotQuery->whereRaw('LOWER(name) LIKE ?', [$search]);
                  })
                  ->orWhereHas('phytosanitaryTreatment.product', function($productQuery) use ($search) {
                      $productQuery->whereRaw('LOWER(name) LIKE ?', [$search]);
                  })
                  ->orWhereHas('fertilization', function($fertQuery) use ($search) {
                      $fertQuery->whereRaw('LOWER(fertilizer_name) LIKE ?', [$search]);
                  });
            });
        }

        if (isset($filters['product_filter']) && $filters['activity_type'] === 'phytosanitary') {
            $query->whereHas('phytosanitaryTreatment', function($treatmentQuery) use ($filters) {
                $treatmentQuery->where('product_id', $filters['product_filter']);
            });
        }

        return $query;
    }
}
