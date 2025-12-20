<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstimatedYield extends Model
{
    protected $fillable = [
        'plot_planting_id',
        'campaign_id',
        'estimated_by',
        'estimated_yield_per_hectare',
        'estimated_total_yield',
        'estimation_date',
        'estimation_method',
        'status',
        'actual_yield_per_hectare',
        'actual_total_yield',
        'variance_percentage',
        'notes',
    ];

    protected $casts = [
        'estimated_yield_per_hectare' => 'decimal:3',
        'estimated_total_yield' => 'decimal:3',
        'estimation_date' => 'date',
        'actual_yield_per_hectare' => 'decimal:3',
        'actual_total_yield' => 'decimal:3',
        'variance_percentage' => 'decimal:2',
    ];

    /**
     * Plantación asociada
     */
    public function plotPlanting(): BelongsTo
    {
        return $this->belongsTo(PlotPlanting::class);
    }

    /**
     * Campaña asociada
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Usuario que realizó la estimación
     */
    public function estimator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'estimated_by');
    }

    /**
     * Calcular diferencia porcentual automáticamente
     */
    protected static function booted()
    {
        static::saving(function ($yield) {
            if ($yield->estimated_total_yield && $yield->actual_total_yield) {
                if ($yield->estimated_total_yield > 0) {
                    $variance = (($yield->actual_total_yield - $yield->estimated_total_yield) / $yield->estimated_total_yield) * 100;
                    $yield->variance_percentage = round($variance, 2);
                }
            }
        });
    }

    /**
     * Actualizar rendimiento real basado en las cosechas de la campaña
     */
    public function updateActualYield(): void
    {
        $harvests = Harvest::whereHas('activity', function($q) {
            $q->where('campaign_id', $this->campaign_id);
        })
        ->where('plot_planting_id', $this->plot_planting_id)
        ->where('status', 'active')
        ->get();

        $totalWeight = $harvests->sum('total_weight');
        $planting = $this->plotPlanting;

        if ($planting && $planting->area_planted > 0) {
            $this->actual_total_yield = round($totalWeight, 3);
            $this->actual_yield_per_hectare = round($totalWeight / $planting->area_planted, 3);
            $this->save();
        }
    }

    /**
     * Scope para filtrar por plantación
     */
    public function scopeForPlanting($query, int $plantingId)
    {
        return $query->where('plot_planting_id', $plantingId);
    }

    /**
     * Scope para filtrar por campaña
     */
    public function scopeForCampaign($query, int $campaignId)
    {
        return $query->where('campaign_id', $campaignId);
    }

    /**
     * Scope para estimaciones confirmadas
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope para estimaciones en borrador
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Verificar si la estimación está confirmada
     */
    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    /**
     * Verificar si hay rendimiento real registrado
     */
    public function hasActualYield(): bool
    {
        return !is_null($this->actual_total_yield);
    }

    /**
     * Obtener la diferencia absoluta entre estimado y real
     */
    public function getAbsoluteVariance(): ?float
    {
        if (!$this->hasActualYield() || !$this->estimated_total_yield) {
            return null;
        }

        return abs($this->actual_total_yield - $this->estimated_total_yield);
    }
}
