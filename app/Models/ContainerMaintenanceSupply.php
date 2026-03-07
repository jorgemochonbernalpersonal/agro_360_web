<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContainerMaintenanceSupply extends Model
{
    protected $fillable = [
        'container_maintenance_id',
        'winery_supply_id',
        'supply_name',
        'quantity_used',
        'unit_of_measurement_id',
        'cost',
        'notes',
    ];

    protected $casts = [
        'quantity_used' => 'decimal:3',
        'cost'          => 'decimal:2',
    ];

    public function maintenance(): BelongsTo
    {
        return $this->belongsTo(ContainerMaintenance::class, 'container_maintenance_id');
    }

    public function winerySupply(): BelongsTo
    {
        return $this->belongsTo(WinerySupply::class);
    }

    public function unitOfMeasurement(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasurement::class);
    }

    /**
     * Nombre a mostrar: del catálogo si existe, o el nombre libre.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->winerySupply?->name ?? $this->supply_name ?? '—';
    }
}
