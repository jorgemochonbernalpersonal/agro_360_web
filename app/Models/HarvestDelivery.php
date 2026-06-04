<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HarvestDelivery extends Model
{
    protected $fillable = [
        'viticulturist_id',
        'plot_planting_id',
        'harvest_id',
        'status',
        'discrepancy_kg',
        'dispute_note',
        'dispute_submitted_at',
        'dispute_resolution_note',
        'dispute_resolved_at',
        'vintage_year',
        'buyer_name',
        'delivered_kg',
        'price_per_kg',
        'total_price',
        'delivery_date',
        'ticket_number',
        'destination_rega_code',
        'vehicle_plate',
        'notes',
        'harvest_time',
        'disqualified',
        'disqualified_reason',
        'baume_degree',
        'brix_degree',
        'potential_alcohol',
        'acidity_level',
        'ph_level',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'delivered_kg' => 'decimal:2',
        'price_per_kg' => 'decimal:3',
        'total_price' => 'decimal:3',
        'discrepancy_kg' => 'decimal:2',
        'vintage_year' => 'integer',
        'disqualified' => 'boolean',
        'dispute_submitted_at' => 'datetime',
        'dispute_resolved_at' => 'datetime',
        'baume_degree' => 'decimal:2',
        'brix_degree' => 'decimal:2',
        'potential_alcohol' => 'decimal:2',
        'acidity_level' => 'decimal:2',
        'ph_level' => 'decimal:2',
    ];

    // ── Relations ────────────────────────────────────────────────────────────

    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    public function plotPlanting(): BelongsTo
    {
        return $this->belongsTo(PlotPlanting::class, 'plot_planting_id');
    }

    /**
     * Recepción de bodega que confirmó esta entrega (null = aún pendiente).
     */
    public function harvest(): BelongsTo
    {
        return $this->belongsTo(Harvest::class, 'harvest_id');
    }

    // ── Status helpers ───────────────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isMatched(): bool
    {
        return $this->status === 'matched';
    }

    public function isDisputed(): bool
    {
        return $this->status === 'disputed';
    }

    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    public function hasDisputeNote(): bool
    {
        return ! is_null($this->dispute_submitted_at);
    }

    /**
     * Porcentaje de discrepancia respecto a lo declarado por el viticultor.
     */
    public function discrepancyPercentage(): ?float
    {
        if (! $this->discrepancy_kg || ! $this->delivered_kg || (float) $this->delivered_kg === 0.0) {
            return null;
        }

        return round((float) $this->discrepancy_kg / (float) $this->delivered_kg * 100, 1);
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeForViticulturist(Builder $query, int $viticulturistId): Builder
    {
        return $query->where('viticulturist_id', $viticulturistId);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeMatched(Builder $query): Builder
    {
        return $query->where('status', 'matched');
    }

    public function scopeDisputed(Builder $query): Builder
    {
        return $query->where('status', 'disputed');
    }

    public function scopeResolved(Builder $query): Builder
    {
        return $query->where('status', 'resolved');
    }
}
