<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalGrape extends Model
{
    protected $fillable = [
        'user_id',
        'supplier_name',
        'grape_type',
        'grape_variety_id',
        'color',
        'protection_level',
        'geographic_origin',
        'vintage_year',
        'alcohol_pct',
        'total_weight_kg',
        'used_weight_kg',
        'entry_date',
        'harvest_date',
        'expiration_date',
        'container_id',
        'notes',
        'status',
    ];

    protected $casts = [
        'total_weight_kg' => 'decimal:3',
        'used_weight_kg'  => 'decimal:3',
        'alcohol_pct'     => 'decimal:2',
        'entry_date'      => 'date',
        'harvest_date'    => 'date',
        'expiration_date' => 'date',
    ];

    const TYPES = [
        'grapes'    => 'Uva',
        'must'      => 'Mosto',
        'bulk_wine' => 'Vino a granel',
    ];

    const COLORS = [
        'white' => 'Blanca',
        'red'   => 'Tinta',
        'rose'  => 'Rosada',
        'other' => 'Otra',
    ];

    const STATUSES = [
        'available' => 'Disponible',
        'used'      => 'Usado',
        'archived'  => 'Archivado',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function grapeVariety(): BelongsTo
    {
        return $this->belongsTo(GrapeVariety::class);
    }

    public function container(): BelongsTo
    {
        return $this->belongsTo(Container::class);
    }

    public function getAvailableWeightKg(): float
    {
        return max(0, (float) $this->total_weight_kg - (float) $this->used_weight_kg);
    }

    public function getTypeLabel(): string
    {
        return self::TYPES[$this->grape_type] ?? $this->grape_type;
    }

    public function getColorLabel(): string
    {
        return self::COLORS[$this->color] ?? '—';
    }

    public function scopeForWinery($query, int $wineryId)
    {
        return $query->where('user_id', $wineryId);
    }
}
