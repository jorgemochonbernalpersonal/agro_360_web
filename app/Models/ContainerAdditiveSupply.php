<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContainerAdditiveSupply extends Model
{
    protected $fillable = [
        'container_id',
        'container_current_state_id',
        'winery_supply_id',
        'additive_name',
        'quantity',
        'unit_of_measurement_id',
        'additive_date',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'additive_date' => 'date',
    ];

    public function container(): BelongsTo
    {
        return $this->belongsTo(Container::class);
    }

    public function containerCurrentState(): BelongsTo
    {
        return $this->belongsTo(ContainerCurrentState::class, 'container_current_state_id');
    }

    public function winerySupply(): BelongsTo
    {
        return $this->belongsTo(WinerySupply::class);
    }

    public function unitOfMeasurement(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasurement::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->winerySupply?->name ?? $this->additive_name ?? '—';
    }
}
