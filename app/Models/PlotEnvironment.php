<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlotEnvironment extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'plot_id',
        'plot_planting_id',
        'viticulturist_id',
        'water_intake_nearby',
        'water_intake_distance_m',
        'protected_zone_total',
        'protected_zone_partial',
        'protection_zone_type',
        'buffer_zone_m',
        'slope_pct',
        'erosion_risk',
        'notes',
    ];

    protected $casts = [
        'water_intake_nearby'    => 'boolean',
        'water_intake_distance_m'=> 'decimal:2',
        'protected_zone_total'   => 'boolean',
        'protected_zone_partial' => 'boolean',
        'buffer_zone_m'          => 'decimal:2',
        'slope_pct'              => 'decimal:2',
        'erosion_risk'           => 'boolean',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }

    public function plotPlanting(): BelongsTo
    {
        return $this->belongsTo(PlotPlanting::class);
    }

    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    public function hasRestrictions(): bool
    {
        return $this->water_intake_nearby
            || $this->protected_zone_total
            || $this->protected_zone_partial;
    }

    public function scopeForViticulturist($query, int $viticulturistId)
    {
        return $query->where('viticulturist_id', $viticulturistId);
    }

    public function scopeForCampaign($query, int $campaignId)
    {
        return $query->where('campaign_id', $campaignId);
    }
}
