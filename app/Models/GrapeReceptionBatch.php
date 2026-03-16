<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\WineryYieldForecast;

/**
 * Acumulador de recepciones de uva por bodega + plantación + campaña.
 *
 * Una bodega puede recibir N cargas (Harvest) de la misma plantación en la
 * misma campaña. Este registro centraliza el total y sirve de referencia
 * para el control de límites PAC (visión bodega).
 */
class GrapeReceptionBatch extends Model
{
    protected $fillable = [
        'winery_id',
        'viticulturist_id',
        'plot_planting_id',
        'campaign_id',
        'vintage_year',
        'total_weight_kg',
        'designation_of_origin',
        'status',
        'notes',
    ];

    protected $casts = [
        'vintage_year'    => 'integer',
        'total_weight_kg' => 'decimal:3',
    ];

    // ─── Relaciones ─────────────────────────────────────────────────────────

    public function winery(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winery_id');
    }

    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    public function plotPlanting(): BelongsTo
    {
        return $this->belongsTo(PlotPlanting::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /** Recepciones individuales de esta batch */
    public function receptions(): HasMany
    {
        return $this->hasMany(Harvest::class, 'batch_id');
    }

    // ─── Helpers ────────────────────────────────────────────────────────────

    /**
     * Cierra el lote — no se pueden añadir más recepciones.
     */
    public function close(): void
    {
        $this->update(['status' => 'closed']);
    }

    /**
     * Reabre el lote para permitir nuevas recepciones.
     */
    public function reopen(): void
    {
        $this->update(['status' => 'open']);
    }

    /**
     * Recalcula y persiste el total a partir de las recepciones activas.
     * Úsalo tras cancelar una recepción para mantener la coherencia.
     */
    public function recalculateTotal(): void
    {
        $this->total_weight_kg = $this->receptions()
            ->where('status', 'active')
            ->sum('total_weight');
        $this->save();
    }

    /**
     * Devuelve el límite operativo efectivo con jerarquía:
     *   1º Forecast confirmado de bodega (acuerdo comercial)
     *   2º Límite PAC de plantación × factor edad (regulatorio)
     *
     * El forecast NUNCA puede superar el límite PAC — se aplica el menor.
     */
    public function effectiveLimit(): ?float
    {
        $pacLimit = $this->plotPlanting?->effectiveHarvestLimitKg($this->vintage_year);

        $forecast = WineryYieldForecast::where('winery_id', $this->winery_id)
            ->where('plot_planting_id', $this->plot_planting_id)
            ->where('campaign_id', $this->campaign_id)
            ->where('status', 'confirmed')
            ->first();

        if ($forecast && $forecast->estimated_kg > 0) {
            return $pacLimit !== null
                ? min((float) $forecast->estimated_kg, $pacLimit)
                : (float) $forecast->estimated_kg;
        }

        return $pacLimit;
    }

    /**
     * Límite PAC puro (sin forecast), para mostrar ambos en el panel.
     */
    public function pacLimit(): ?float
    {
        return $this->plotPlanting?->effectiveHarvestLimitKg($this->vintage_year);
    }

    /**
     * Forecast confirmado para esta batch, si existe.
     */
    public function forecast(): ?WineryYieldForecast
    {
        return WineryYieldForecast::where('winery_id', $this->winery_id)
            ->where('plot_planting_id', $this->plot_planting_id)
            ->where('campaign_id', $this->campaign_id)
            ->where('status', 'confirmed')
            ->first();
    }

    /**
     * Porcentaje usado del límite (0-100+). Null si no hay límite.
     */
    public function usagePercentage(): ?float
    {
        $limit = $this->effectiveLimit();
        if (!$limit || $limit <= 0) {
            return null;
        }

        return round(((float) $this->total_weight_kg / $limit) * 100, 1);
    }

    /**
     * ¿Se ha superado el límite PAC?
     */
    public function exceedsLimit(): bool
    {
        $limit = $this->effectiveLimit();
        return $limit !== null && (float) $this->total_weight_kg > $limit;
    }

    // ─── Scopes ─────────────────────────────────────────────────────────────

    public function scopeForWinery($query, int $wineryId)
    {
        return $query->where('winery_id', $wineryId);
    }

    public function scopeForVintage($query, int $year)
    {
        return $query->where('vintage_year', $year);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeExceedingLimit($query)
    {
        return $query->whereHas('plotPlanting', function ($q) {
            $q->whereNotNull('harvest_limit_kg')->where('harvest_limit_kg', '>', 0);
        })->whereColumn('total_weight_kg', '>', function ($sub) {
            $sub->selectRaw('harvest_limit_kg')
                ->from('plot_plantings')
                ->whereColumn('plot_plantings.id', 'grape_reception_batches.plot_planting_id')
                ->limit(1);
        });
    }
}
