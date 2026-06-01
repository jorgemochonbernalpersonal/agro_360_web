<?php

namespace App\Models\Builders;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class PlotQueryBuilder extends Builder
{
    /**
     * Filtrar parcelas por usuario según su rol
     */
    public function forUser(User $user): self
    {
        return match ($user->role) {
            'admin'         => $this,
            'supervisor'    => $this->forSupervisor($user),
            'winery'        => $this->forWinery($user),
            'viticulturist' => $this->forViticulturist($user),
            'producer'      => $this->forViticulturist($user),
            default         => $this->whereRaw('1 = 0'),
        };
    }

    /**
     * Filtrar parcelas para un supervisor
     */
    public function forSupervisor(User $user): self
    {
        return $this->where(function ($query) use ($user) {
            // Via bodegas supervisadas: supervisor_winery → winery_viticulturist → plot
            $query->whereIn('viticulturist_id', function ($q) use ($user) {
                $q->select('viticulturist_id')
                  ->from('winery_viticulturist')
                  ->whereIn('winery_id', function ($sq) use ($user) {
                      $sq->select('winery_id')
                         ->from('supervisor_winery')
                         ->where('supervisor_id', $user->id);
                  });
            });

            // Via relación directa: supervisor_viticulturist → plot
            $query->orWhereIn('viticulturist_id', function ($q) use ($user) {
                $q->select('viticulturist_id')
                  ->from('supervisor_viticulturist')
                  ->where('supervisor_id', $user->id);
            });
        });
    }

    /**
     * Filtrar parcelas para una bodega
     */
    public function forWinery(User $user): self
    {
        return $this->whereIn('viticulturist_id', function($q) use ($user) {
            $q->select('viticulturist_id')
              ->from('winery_viticulturist')
              ->where('winery_id', $user->id);
        });
    }

    /**
     * Filtrar parcelas visibles para un viticultor
     * Incluye sus propias parcelas y parcelas de viticultores visibles
     */
    public function forViticulturist(User $user): self
    {
        if (!$user->hasViticulturistAccess()) {
            return $this->whereRaw('1 = 0');
        }

        return $this->where(function($q) use ($user) {
            // Sus propias parcelas
            $q->where('viticulturist_id', $user->id);
            
            // Parcelas de viticultores creados por él
            $q->orWhereIn('viticulturist_id', function($subQuery) use ($user) {
                $subQuery->select('viticulturist_id')
                    ->from('winery_viticulturist')
                    ->where('parent_viticulturist_id', $user->id)
                    ->where('source', 'viticulturist');
            });
            
            // Parcelas de viticultores del mismo supervisor EN LA MISMA bodega
            $q->orWhereIn('viticulturist_id', function($subQuery) use ($user) {
                $subQuery->select('wv2.viticulturist_id')
                    ->from('winery_viticulturist as wv1')
                    ->join('winery_viticulturist as wv2', function($join) {
                        $join->on('wv2.supervisor_id', '=', 'wv1.supervisor_id')
                             ->on('wv2.winery_id', '=', 'wv1.winery_id')
                             ->where('wv2.source', '=', 'supervisor');
                    })
                    ->where('wv1.viticulturist_id', $user->id)
                    ->where('wv1.source', 'supervisor')
                    ->whereNotNull('wv1.supervisor_id')
                    ->whereNotNull('wv1.winery_id');
            });
            
            // Parcelas de viticultores de sus wineries
            $q->orWhereIn('viticulturist_id', function($subQuery) use ($user) {
                $subQuery->select('wv2.viticulturist_id')
                    ->from('winery_viticulturist as wv1')
                    ->join('winery_viticulturist as wv2', 'wv2.winery_id', '=', 'wv1.winery_id')
                    ->where('wv1.viticulturist_id', $user->id)
                    ->whereNotNull('wv1.winery_id')
                    ->where(function($wineryQ) {
                        $wineryQ->where('wv2.source', 'own')
                                ->orWhere('wv2.source', 'viticulturist');
                    });
            });
        });
    }

    /**
     * Filtrar solo parcelas activas
     */
    public function active(): self
    {
        return $this->where('active', true);
    }

    /**
     * Filtrar solo parcelas bloqueadas
     */
    public function locked(): self
    {
        return $this->where('is_locked', true);
    }

    /**
     * Filtrar solo parcelas no bloqueadas
     */
    public function unlocked(): self
    {
        return $this->where('is_locked', false);
    }

    /**
     * Filtrar por comunidad autónoma
     */
    public function inAutonomousCommunity(int $communityId): self
    {
        return $this->where('autonomous_community_id', $communityId);
    }

    /**
     * Filtrar por provincia
     */
    public function inProvince(int $provinceId): self
    {
        return $this->where('province_id', $provinceId);
    }

    /**
     * Filtrar por municipio
     */
    public function inMunicipality(int $municipalityId): self
    {
        return $this->where('municipality_id', $municipalityId);
    }

    /**
     * Ordenar por nombre
     */
    public function orderByName(string $direction = 'asc'): self
    {
        return $this->orderBy('name', $direction);
    }

    /**
     * Ordenar por área
     */
    public function orderByArea(string $direction = 'desc'): self
    {
        return $this->orderBy('area', $direction);
    }

    /**
     * Parcelas con área mayor a
     */
    public function withAreaGreaterThan(float $area): self
    {
        return $this->where('area', '>', $area);
    }

    /**
     * Parcelas con área menor a
     */
    public function withAreaLessThan(float $area): self
    {
        return $this->where('area', '<', $area);
    }

    /**
     * Parcelas con alertas NDVI habilitadas
     */
    public function withNdviAlerts(): self
    {
        return $this->where('alert_email_enabled', true);
    }

    /**
     * Cargar relaciones comunes de manera optimizada
     */
    public function withCommonRelations(): self
    {
        return $this->with([
            'viticulturist:id,name,email',
            'province:id,name',
            'municipality:id,name',
        ]);
    }

    /**
     * Cargar relaciones completas
     */
    public function withFullRelations(): self
    {
        return $this->with([
            'viticulturist:id,name,email',
            'autonomousCommunity:id,name',
            'province:id,name',
            'municipality:id,name',
            'plantings.grapeVariety',
            'sigpacCodes',
        ]);
    }

    /**
     * Búsqueda por texto
     */
    public function search(string $term): self
    {
        $term = '%' . strtolower($term) . '%';
        
        return $this->where(function($q) use ($term) {
            $q->whereRaw('LOWER(name) LIKE ?', [$term])
              ->orWhereRaw('LOWER(description) LIKE ?', [$term]);
        });
    }

    /**
     * Parcelas con plazo de seguridad activo
     */
    public function withActiveWithdrawalPeriods(): self
    {
        $today = now();
        
        return $this->whereHas('agriculturalActivities', function($query) use ($today) {
            $query->where('activity_type', 'phytosanitary')
                  ->whereHas('phytosanitaryTreatment.product', function($q) use ($today) {
                      $q->whereNotNull('withdrawal_period_days')
                        ->whereRaw('DATE_ADD(activity_date, INTERVAL withdrawal_period_days DAY) > ?', [$today]);
                  });
        });
    }
}
