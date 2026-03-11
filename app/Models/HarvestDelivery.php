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
        'delivery_date'    => 'date',
        'delivered_kg'     => 'decimal:2',
        'price_per_kg'     => 'decimal:4',
        'total_price'      => 'decimal:2',
        'vintage_year'     => 'integer',
        'disqualified'     => 'boolean',
        'baume_degree'     => 'decimal:2',
        'brix_degree'      => 'decimal:2',
        'potential_alcohol'=> 'decimal:2',
        'acidity_level'    => 'decimal:2',
        'ph_level'         => 'decimal:2',
    ];

    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    public function plotPlanting(): BelongsTo
    {
        return $this->belongsTo(PlotPlanting::class, 'plot_planting_id');
    }

    public function scopeForViticulturist(Builder $query, int $viticulturistId): Builder
    {
        return $query->where('viticulturist_id', $viticulturistId);
    }
}
