<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\GrapeReceptionBatch;
use App\Models\PlotPlanting;

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
        'active',
        'actual_yield_per_hectare',
        'actual_total_yield',
        'variance_percentage',
        'notes',
        // Muestreo de campo
        'thumbs_per_vine',
        'bunches_per_plant',
        'bunch_weight_grams',
        'total_plants_sampled',
        'sampling_area_pct',
        'health_percentage',
        'health_status',
        'other_wineries',
        'potential_alcohol',
        'vintage',
        'auto_calculated_yield',
        // Multi-ronda
        'estimation_round',
    ];

    protected $casts = [
        'estimated_yield_per_hectare' => 'decimal:3',
        'estimated_total_yield' => 'decimal:3',
        'estimation_date' => 'date',
        'active' => 'boolean',
        'actual_yield_per_hectare' => 'decimal:3',
        'actual_total_yield' => 'decimal:3',
        'variance_percentage' => 'decimal:2',
        'thumbs_per_vine' => 'integer',
        'bunches_per_plant' => 'decimal:2',
        'bunch_weight_grams' => 'decimal:2',
        'total_plants_sampled' => 'integer',
        'sampling_area_pct' => 'decimal:2',
        'health_percentage' => 'decimal:2',
        'health_status'     => 'string',
        'other_wineries'    => 'boolean',
        'potential_alcohol' => 'decimal:2',
        'auto_calculated_yield' => 'decimal:2',
        'estimation_round' => 'integer',
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
     * Etiquetas de ronda de estimación
     */
    public const ROUNDS = [
        1 => 'Pre-envero',
        2 => 'Envero',
        3 => 'Pre-vendimia',
        4 => 'Revisión final',
    ];

    public static function roundOptions(): array
    {
        return array_map(fn ($v) => __($v), static::ROUNDS);
    }

    public const HEALTH_STATUSES = [
        'excellent'         => 'Excelente',
        'good'              => 'Bueno',
        'botrytis_light'    => 'Botrytis leve',
        'botrytis_moderate' => 'Botrytis moderada',
        'oidium_light'      => 'Oidio leve',
        'oidium_moderate'   => 'Oidio moderado',
        'mixed'             => 'Afección mixta',
        'poor'              => 'Deficiente',
    ];

    public static function healthStatusOptions(): array
    {
        return array_map(fn ($v) => __($v), static::HEALTH_STATUSES);
    }

    /**
     * Calcular diferencia porcentual y rendimiento automático desde muestreo
     */
    protected static function booted()
    {
        static::saving(function ($yield) {
            // Auto-calcular desde datos de muestreo
            if ($yield->bunches_per_plant && $yield->bunch_weight_grams) {
                $planting = $yield->plotPlanting ?? PlotPlanting::find($yield->plot_planting_id);
                if ($planting && $planting->vine_count > 0) {
                    $factor = $yield->health_percentage ? ($yield->health_percentage / 100) : 1;
                    $yield->auto_calculated_yield = round(
                        ($yield->bunches_per_plant * $yield->bunch_weight_grams / 1000)
                        * $planting->vine_count
                        * $factor,
                        2
                    );
                }
            }

            // Calcular varianza estimado vs real
            if ($yield->estimated_total_yield && $yield->actual_total_yield) {
                if ($yield->estimated_total_yield > 0) {
                    $variance = (($yield->actual_total_yield - $yield->estimated_total_yield) / $yield->estimated_total_yield) * 100;
                    $yield->variance_percentage = round($variance, 2);
                }
            }
        });
    }

    /**
     * Nombre de la ronda actual
     */
    public function getRoundLabelAttribute(): string
    {
        return __(self::ROUNDS[$this->estimation_round] ?? "Ronda {$this->estimation_round}");
    }

    /**
     * Actualizar rendimiento real basado en TODAS las cosechas de la añada (vintage).
     * Cuenta tanto cosechas de viticultor como de bodega para la misma plantación.
     */
    public function updateActualYield(): void
    {
        // Usar vintage si está definido, sino deducirlo del año de la campaña
        $vintage = $this->vintage ?? $this->campaign?->year ?? now()->year;

        // Fuente primaria: registros del cuaderno del viticultor
        $totalWeight = (float) Harvest::where('plot_planting_id', $this->plot_planting_id)
            ->where('vintage', $vintage)
            ->where('status', 'active')
            ->whereNotNull('activity_id')
            ->sum('total_weight');

        // Fallback: lote acumulado de bodega si el viticultor no registró en cuaderno
        if ($totalWeight === 0.0) {
            $totalWeight = (float) GrapeReceptionBatch::where('plot_planting_id', $this->plot_planting_id)
                ->where('vintage_year', $vintage)
                ->sum('total_weight_kg');
        }

        $planting = $this->plotPlanting;
        if (!$planting) return;

        $this->actual_total_yield = round($totalWeight, 3);
        $this->actual_yield_per_hectare = $planting->area_planted > 0
            ? round($totalWeight / (float) $planting->area_planted, 3)
            : 0;
        $this->save();
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
