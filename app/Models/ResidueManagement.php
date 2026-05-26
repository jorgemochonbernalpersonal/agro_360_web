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
        'incorporation' => __('Triturado e incorporación al suelo'),
        'removal'       => __('Retirada de la explotación'),
        'burning'       => __('Quema (cuando permitido)'),
        'composting'    => __('Compostaje'),
        'biogas'        => __('Biogás'),
        'sale'          => __('Venta'),
        'other'         => __('Otro'),
    ];

    const MATERIAL_TYPES = [
        'pruning_wood' => __('Madera/leña de poda'),
        'grape_marc'   => __('Orujo'),
        'vine_leaves'  => __('Hojas de vid'),
        'grass'        => __('Cubierta vegetal'),
        'other'        => __('Otro'),
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
