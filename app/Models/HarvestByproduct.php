<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HarvestByproduct extends Model
{
    protected $fillable = [
        'viticulturist_id', 'campaign_id', 'date',
        'byproduct_type', 'quantity_kg',
        'destination_type', 'destination_name',
        'document_reference', 'notes', 'active',
    ];

    protected $casts = [
        'date'        => 'date',
        'quantity_kg' => 'decimal:3',
        'active'      => 'boolean',
    ];

    const BYPRODUCT_TYPES = [
        'pomace' => __('Orujo / Hollejo'),
        'stem'   => __('Raspón / Escobajo'),
        'lees'   => __('Lías'),
        'other'  => __('Otro'),
    ];

    const DESTINATION_TYPES = [
        'cooperative'        => __('Cooperativa vinícola'),
        'winery'            => __('Bodega'),
        'distillery'        => __('Destilería / Alcoholera'),
        'composting'         => __('Planta de compostaje'),
        'authorized_landfill' => __('Vertedero autorizado'),
        'other'             => __('Otro destino'),
    ];

    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function getByproductTypeLabelAttribute(): string
    {
        return self::BYPRODUCT_TYPES[$this->byproduct_type] ?? $this->byproduct_type;
    }

    public function getDestinationTypeLabelAttribute(): string
    {
        return self::DESTINATION_TYPES[$this->destination_type] ?? $this->destination_type;
    }
}
