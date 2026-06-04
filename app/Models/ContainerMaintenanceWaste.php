<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContainerMaintenanceWaste extends Model
{
    protected $fillable = [
        'container_maintenance_id',
        'container_waste_type_id',
        'custom_waste_type',
        'waste_date',
        'quantity',
        'unit_of_measurement_id',
        'disposal_method',
        'cost',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'cost' => 'decimal:2',
        'waste_date' => 'date',
    ];

    public function maintenance(): BelongsTo
    {
        return $this->belongsTo(ContainerMaintenance::class, 'container_maintenance_id');
    }

    public function wasteType(): BelongsTo
    {
        return $this->belongsTo(ContainerWasteType::class, 'container_waste_type_id');
    }

    public function unitOfMeasurement(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasurement::class);
    }

    public function getDisplayTypeAttribute(): string
    {
        if ($this->container_waste_type_id && $this->wasteType) {
            return $this->wasteType->name === 'Otro'
                ? ($this->custom_waste_type ?: 'Otro')
                : $this->wasteType->name;
        }

        return $this->custom_waste_type ?: '—';
    }
}
