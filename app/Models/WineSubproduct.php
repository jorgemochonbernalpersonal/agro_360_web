<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WineSubproduct extends Model
{
    const TYPES = [
        'pomace'  => 'Orujo',
        'lees'   => 'Lías',
        'vinasse' => 'Vinaza',
        'other'  => 'Otro subproducto',
    ];

    const DESTINATIONS = [
        'distillery'      => 'Destilería',
        'authorized_plant'=> 'Planta autorizada',
        'own_use'         => 'Uso propio',
        'other'           => 'Otro destino',
    ];

    protected $fillable = [
        'user_id',
        'wine_id',
        'type',
        'subproduct_date',
        'quantity',
        'unit_of_measurement_id',
        'destination',
        'destination_name',
        'lot_number',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'subproduct_date' => 'date',
        'quantity'        => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wine(): BelongsTo
    {
        return $this->belongsTo(Wine::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasurement::class, 'unit_of_measurement_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getDestinationLabelAttribute(): string
    {
        return self::DESTINATIONS[$this->destination] ?? $this->destination;
    }

    public function getTypeBadgeColorAttribute(): string
    {
        return match ($this->type) {
            'pomace'  => 'amber',
            'lees'   => 'blue',
            'vinasse' => 'purple',
            default  => 'zinc',
        };
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
