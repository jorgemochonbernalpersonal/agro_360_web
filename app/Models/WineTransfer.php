<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WineTransfer extends Model
{
    const TRANSFER_TYPES = [
        'racking'  => 'Trasiego',
        'blending' => 'Mezcla / Coupage',
        'top_up'   => 'Relleno',
        'other'    => 'Otro',
    ];

    protected $fillable = [
        'wine_id',
        'from_container_id',
        'to_container_id',
        'quantity',
        'unit_of_measurement_id',
        'transfer_type',
        'transfer_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quantity'      => 'decimal:3',
        'transfer_date' => 'date',
    ];

    public function wine(): BelongsTo
    {
        return $this->belongsTo(Wine::class);
    }

    public function fromContainer(): BelongsTo
    {
        return $this->belongsTo(Container::class, 'from_container_id');
    }

    public function toContainer(): BelongsTo
    {
        return $this->belongsTo(Container::class, 'to_container_id');
    }

    public function unitOfMeasurement(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasurement::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTransferTypeLabelAttribute(): string
    {
        return self::TRANSFER_TYPES[$this->transfer_type] ?? $this->transfer_type;
    }
}
