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
        'pomace' => 'Orujo / Hollejo',
        'stem'   => 'Raspón / Escobajo',
        'lees'   => 'Lías',
        'other'  => 'Otro',
    ];

    const DESTINATION_TYPES = [
        'cooperative'        => 'Cooperativa vinícola',
        'winery'            => 'Bodega',
        'distillery'        => 'Destilería / Alcoholera',
        'composting'         => 'Planta de compostaje',
        'authorized_landfill' => 'Vertedero autorizado',
        'other'             => 'Otro destino',
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
