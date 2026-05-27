<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResidueManagement extends Model
{
    use HasFactory;

    protected $table = 'residue_managements';

    const PRACTICE_TYPES = [
        'incorporation' => 'Triturado e incorporación al suelo',
        'removal'       => 'Retirada de la explotación',
        'burning'       => 'Quema (cuando permitido)',
        'composting'    => 'Compostaje',
        'biogas'        => 'Biogás',
        'sale'          => 'Venta',
        'other'         => 'Otro',
    ];

    const MATERIAL_TYPES = [
        'pruning_wood' => 'Madera/leña de poda',
        'grape_marc'   => 'Orujo',
        'vine_leaves'  => 'Hojas de vid',
        'grass'        => 'Cubierta vegetal',
        'other'        => 'Otro',
    ];

    protected $fillable = [
        'campaign_id',
        'plot_id',
        'plot_planting_id',
        'viticulturist_id',
        'date',
        'practice_type',
        'material_type',
        'estimated_quantity',
        'quantity_unit',
        'justification',
        'notes',
        'active',
    ];

    protected $casts = [
        'date'               => 'date',
        'estimated_quantity' => 'decimal:2',
        'active'             => 'boolean',
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

    public function getPracticeLabelAttribute(): string
    {
        return self::PRACTICE_TYPES[$this->practice_type] ?? $this->practice_type;
    }

    public function getMaterialLabelAttribute(): string
    {
        return self::MATERIAL_TYPES[$this->material_type] ?? $this->material_type;
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
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
