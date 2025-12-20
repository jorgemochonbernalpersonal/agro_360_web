<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\TrainingSystem;

class PlotPlanting extends Model
{
    protected $fillable = [
        'plot_id',
        'name',
        'grape_variety_id',
        'area_planted',
        'harvest_limit_kg',
        'planting_year',
        'planting_date',
        'vine_count',
        'density',
        'row_spacing',
        'vine_spacing',
        'rootstock',
        'training_system',
        'training_system_id',
        'irrigated',
        'status',
        'notes',
    ];

    protected $casts = [
        'area_planted' => 'decimal:3',
        'harvest_limit_kg' => 'decimal:3',
        'row_spacing' => 'decimal:3',
        'vine_spacing' => 'decimal:3',
        'planting_date' => 'date',
        'irrigated' => 'boolean',
        'planting_year' => 'integer',
        'vine_count' => 'integer',
        'density' => 'integer',
    ];

    /**
     * Relación con la parcela
     */
    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }

    /**
     * Relación con la variedad de uva
     */
    public function grapeVariety(): BelongsTo
    {
        return $this->belongsTo(GrapeVariety::class);
    }

    /**
     * Sistema de conducción (catálogo).
     */
    public function trainingSystem(): BelongsTo
    {
        return $this->belongsTo(TrainingSystem::class, 'training_system_id');
    }

    /**
     * Scope para plantaciones activas
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope para plantaciones con riego
     */
    public function scopeIrrigated($query)
    {
        return $query->where('irrigated', true);
    }

    /**
     * Cosechas realizadas en esta plantación
     */
    public function harvests()
    {
        return $this->hasMany(Harvest::class, 'plot_planting_id');
    }

    /**
     * Última cosecha de esta plantación
     */
    public function lastHarvest()
    {
        return $this->hasOne(Harvest::class, 'plot_planting_id')
            ->where('status', 'active')
            ->latest('harvest_start_date');
    }

    /**
     * Rendimientos estimados para esta plantación
     */
    public function estimatedYields()
    {
        return $this->hasMany(EstimatedYield::class);
    }

    /**
     * Obtener el rendimiento estimado para una campaña específica
     */
    public function getEstimatedYieldForCampaign(int $campaignId): ?EstimatedYield
    {
        return $this->estimatedYields()
            ->where('campaign_id', $campaignId)
            ->first();
    }

    /**
     * Obtener el rendimiento real total de todas las cosechas
     */
    public function getTotalActualYield(): float
    {
        return $this->harvests()
            ->where('status', 'active')
            ->sum('total_weight');
    }

    /**
     * Obtener el rendimiento real total de una campaña específica
     */
    public function getTotalActualYieldForCampaign(int $campaignId): float
    {
        return $this->harvests()
            ->whereHas('activity', function ($q) use ($campaignId) {
                $q->where('campaign_id', $campaignId);
            })
            ->where('status', 'active')
            ->sum('total_weight');
    }

    /**
     * Verificar si tiene límite de cosecha establecido
     */
    public function hasHarvestLimit(): bool
    {
        return !is_null($this->harvest_limit_kg) && $this->harvest_limit_kg > 0;
    }

    /**
     * Obtener el porcentaje usado del límite de cosecha (para todas las cosechas)
     */
    public function getHarvestLimitUsagePercentage(): ?float
    {
        if (!$this->hasHarvestLimit()) {
            return null;
        }

        $totalHarvested = $this->getTotalActualYield();
        if ($this->harvest_limit_kg == 0) {
            return null;
        }

        return round(($totalHarvested / $this->harvest_limit_kg) * 100, 3);
    }

    /**
     * Obtener el porcentaje usado del límite de cosecha para una campaña específica
     */
    public function getHarvestLimitUsagePercentageForCampaign(int $campaignId): ?float
    {
        if (!$this->hasHarvestLimit()) {
            return null;
        }

        $totalHarvested = $this->getTotalActualYieldForCampaign($campaignId);
        if ($this->harvest_limit_kg == 0) {
            return null;
        }

        return round(($totalHarvested / $this->harvest_limit_kg) * 100, 3);
    }

    /**
     * Obtener el peso disponible restante del límite (para todas las cosechas)
     */
    public function getRemainingHarvestLimit(): ?float
    {
        if (!$this->hasHarvestLimit()) {
            return null;
        }

        $totalHarvested = $this->getTotalActualYield();
        return max(0, round($this->harvest_limit_kg - $totalHarvested, 3));
    }

    /**
     * Obtener el peso disponible restante del límite para una campaña específica
     */
    public function getRemainingHarvestLimitForCampaign(int $campaignId): ?float
    {
        if (!$this->hasHarvestLimit()) {
            return null;
        }

        $totalHarvested = $this->getTotalActualYieldForCampaign($campaignId);
        return max(0, round($this->harvest_limit_kg - $totalHarvested, 3));
    }

    /**
     * Verificar si excede el límite de cosecha (para todas las cosechas)
     */
    public function exceedsHarvestLimit(): bool
    {
        if (!$this->hasHarvestLimit()) {
            return false;
        }

        $totalHarvested = $this->getTotalActualYield();
        return $totalHarvested > $this->harvest_limit_kg;
    }

    /**
     * Verificar si excede el límite de cosecha para una campaña específica
     */
    public function exceedsHarvestLimitForCampaign(int $campaignId, float $additionalWeight = 0): bool
    {
        if (!$this->hasHarvestLimit()) {
            return false;
        }

        $totalHarvested = $this->getTotalActualYieldForCampaign($campaignId);
        $newTotal = $totalHarvested + $additionalWeight;
        return $newTotal > $this->harvest_limit_kg;
    }

    /**
     * Obtener la varianza entre el rendimiento estimado y real para una campaña
     */
    public function getYieldVariance(int $campaignId, ?float $additionalWeight = null): ?array
    {
        $estimatedYield = $this->getEstimatedYieldForCampaign($campaignId);
        if (!$estimatedYield) {
            return null;
        }

        $totalHarvested = $this->getTotalActualYieldForCampaign($campaignId);
        if ($additionalWeight !== null) {
            $totalHarvested += $additionalWeight;
        }

        $variance = $totalHarvested - $estimatedYield->estimated_total_yield;
        $variancePercentage = $estimatedYield->estimated_total_yield > 0
            ? round(($variance / $estimatedYield->estimated_total_yield) * 100, 3)
            : null;

        return [
            'estimated' => $estimatedYield->estimated_total_yield,
            'actual' => $totalHarvested,
            'variance' => $variance,
            'variance_percentage' => $variancePercentage,
            'is_over_yield' => $variance > 0, // Sobrerendimiento
            'is_under_yield' => $variance < 0, // Subrendimiento
        ];
    }
}
