<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WineryYieldForecast extends Model
{
    protected $fillable = [
        'winery_id',
        'viticulturist_id',
        'plot_planting_id',
        'campaign_id',
        'vintage_year',
        'estimated_kg',
        'estimation_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'vintage_year' => 'integer',
        'estimated_kg' => 'decimal:3',
        'estimation_date' => 'date',
    ];

    // ─── Relaciones ─────────────────────────────────────────────────────────

    /** @return BelongsTo<User, $this> */
    public function winery(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winery_id');
    }

    /** @return BelongsTo<User, $this> */
    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    /** @return BelongsTo<PlotPlanting, $this> */
    public function plotPlanting(): BelongsTo
    {
        return $this->belongsTo(PlotPlanting::class);
    }

    /** @return BelongsTo<Campaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    // ─── Helpers ────────────────────────────────────────────────────────────

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Kg recibidos reales asociados a esta previsión (desde el batch).
     */
    public function receivedKg(): float
    {
        return (float) GrapeReceptionBatch::where('winery_id', $this->winery_id)
            ->where('plot_planting_id', $this->plot_planting_id)
            ->where('campaign_id', $this->campaign_id)
            ->value('total_weight_kg') ?? 0;
    }

    /**
     * % ejecutado respecto al forecast.
     */
    public function executionPercentage(): ?float
    {
        if (! $this->estimated_kg || $this->estimated_kg <= 0) {
            return null;
        }

        return round(($this->receivedKg() / (float) $this->estimated_kg) * 100, 1);
    }

    /**
     * ¿El recibido supera la previsión?
     */
    public function exceedsForecast(): bool
    {
        return $this->receivedKg() > (float) $this->estimated_kg;
    }

    // ─── Scopes ─────────────────────────────────────────────────────────────

    public function scopeForWinery($query, int $wineryId)
    {
        return $query->where('winery_id', $wineryId);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeForVintage($query, int $year)
    {
        return $query->where('vintage_year', $year);
    }
}
