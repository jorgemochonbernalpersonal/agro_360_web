<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlotPlanting extends Model
{
    use HasFactory;

    protected $fillable = [
        'plot_id',
        'name',
        'grape_variety_id',
        'area_planted',
        'harvest_limit_kg',
        'planting_year',
        'vine_count',
        'density',
        'row_spacing',
        'vine_spacing',
        'rootstock',
        'training_system_id',
        'irrigated',
        'status',
        'active',
        'notes',
        'planting_authorization',
        'authorization_date',
        'right_type',
        'uprooting_date',
        'designation_of_origin',
    ];

    protected $casts = [
        'area_planted' => 'decimal:3',
        'harvest_limit_kg' => 'decimal:3',
        'row_spacing' => 'decimal:3',
        'vine_spacing' => 'decimal:3',
        'authorization_date' => 'date',
        'uprooting_date' => 'date',
        'irrigated' => 'boolean',
        'active' => 'boolean',
        'planting_year' => 'integer',
        'vine_count' => 'integer',
        'density' => 'integer',
    ];

    /**
     * Relación con la parcela
     *
     * @return BelongsTo<Plot, $this>
     */
    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }

    /**
     * Observaciones fenológicas de la plantación
     */
    public function phenologyObservations(): HasMany
    {
        return $this->hasMany(PhenologyObservation::class);
    }

    /**
     * Logs de auditoría de la plantación
     */
    public function auditLogs()
    {
        return $this->hasMany(PlotPlantingAuditLog::class);
    }

    /**
     * Certificaciones de la plantación
     */
    public function certifications()
    {
        return $this->hasMany(PlantingCertification::class);
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
     *
     * @param mixed $query
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope para plantaciones con riego
     *
     * @param mixed $query
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
     * Obtener el rendimiento real total para una añada concreta (cualquier campaña).
     * Cuenta tanto cosechas de viticultor como de bodega.
     */
    public function getTotalActualYieldForVintage(int $year): float
    {
        return $this->harvests()
            ->where('vintage', $year)
            ->where('status', 'active')
            ->sum('total_weight');
    }

    /**
     * Total de kg recepcionados por una bodega concreta en una añada.
     * Solo cuenta registros con winery_id (recepciones de bodega).
     */
    public function getTotalWineryReceptionsForVintage(int $year, int $wineryId): float
    {
        return $this->harvests()
            ->where('winery_id', $wineryId)
            ->where('vintage', $year)
            ->where('status', 'active')
            ->sum('total_weight');
    }

    /**
     * Total de kg registrados por el viticultor en su cuaderno para una añada.
     * Solo cuenta registros con activity_id (cuaderno de campo).
     */
    public function getTotalViticulturistYieldForVintage(int $year, int $viticulturistId): float
    {
        return $this->harvests()
            ->whereHas('activity', fn ($q) => $q->where('viticulturist_id', $viticulturistId))
            ->where('vintage', $year)
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
        return ! is_null($this->harvest_limit_kg) && $this->harvest_limit_kg > 0;
    }

    /**
     * Obtener el porcentaje usado del límite de cosecha (para todas las cosechas)
     */
    public function getHarvestLimitUsagePercentage(): ?float
    {
        if (! $this->hasHarvestLimit()) {
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
        if (! $this->hasHarvestLimit()) {
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
        if (! $this->hasHarvestLimit()) {
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
        if (! $this->hasHarvestLimit()) {
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
        if (! $this->hasHarvestLimit()) {
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
        if (! $this->hasHarvestLimit()) {
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
        if (! $estimatedYield) {
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

    /**
     * ========================================
     * EDAD Y CICLO DE VIDA
     * ========================================
     */

    /**
     * Obtener la edad de la plantación en años
     */
    public function getAgeAttribute(): int
    {
        if (! $this->planting_year) {
            return 0;
        }

        return now()->year - $this->planting_year;
    }

    /**
     * Obtener la etapa del ciclo de vida
     */
    public function getLifeCycleStageAttribute(): string
    {
        $age = $this->age;

        if ($age < 3) {
            return 'joven';
        }
        if ($age < 8) {
            return 'desarrollo';
        }
        if ($age < 25) {
            return 'productiva';
        }
        if ($age < 40) {
            return 'madura';
        }

        return 'vieja';
    }

    /**
     * Límite de cosecha efectivo según edad de la plantación (como vinai2).
     *
     * Factor de producción por edad:
     *   < 3 años  → 0%   (plantación no productiva)
     *   3 años    → 33%
     *   4 años    → 75%
     *   ≥ 5 años  → 100%
     *
     * Si no hay planting_year o harvest_limit_kg, devuelve null (sin límite definido).
     */
    public function effectiveHarvestLimitKg(?int $harvestYear = null): ?float
    {
        if (! $this->hasHarvestLimit() || ! $this->planting_year) {
            return $this->harvest_limit_kg ? (float) $this->harvest_limit_kg : null;
        }

        $year = $harvestYear ?? now()->year;
        $age = $year - $this->planting_year;

        $factor = match (true) {
            $age < 3 => 0.0,
            $age === 3 => 0.33,
            $age === 4 => 0.75,
            default => 1.0,
        };

        return round((float) $this->harvest_limit_kg * $factor, 2);
    }

    /**
     * Verificar si necesita replantación
     */
    public function needsReplanting(): bool
    {
        return $this->age > 35 || $this->status === 'replanting';
    }

    /**
     * Obtener productividad esperada según edad
     */
    public function getExpectedProductivityAttribute(): string
    {
        $stage = $this->life_cycle_stage;

        return match ($stage) {
            'joven' => 'Baja (20-40% del máximo)',
            'desarrollo' => 'Media (60-80%)',
            'productiva' => 'Alta (100%)',
            'madura' => 'Media-Alta (80-90%)',
            'vieja' => 'Baja-Media (40-60%)',
            default => 'Desconocida',
        };
    }
}
