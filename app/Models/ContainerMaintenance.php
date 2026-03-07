<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContainerMaintenance extends Model
{
    protected $fillable = [
        'container_id',
        'maintenance_type',
        'maintenance_name',
        'scheduled_date',
        'performed_date',
        'next_maintenance_date',
        'status',
        'cost',
        'performed_by',
        'notes',
    ];

    protected $casts = [
        'scheduled_date'       => 'date',
        'performed_date'       => 'date',
        'next_maintenance_date'=> 'date',
        'cost'                 => 'decimal:2',
    ];

    const TYPES = [
        'cleaning'         => 'Limpieza',
        'sulfuring'        => 'Sulfitado',
        'inspection'       => 'Inspección',
        'repair'           => 'Reparación',
        'tartrate_removal' => 'Desconfitado (tartratos)',
        'other'            => 'Otro',
    ];

    const STATUSES = [
        'scheduled'  => 'Programado',
        'completed'  => 'Completado',
        'cancelled'  => 'Cancelado',
    ];

    public function container(): BelongsTo
    {
        return $this->belongsTo(Container::class);
    }

    public function getTypeLabel(): string
    {
        return self::TYPES[$this->maintenance_type] ?? $this->maintenance_type;
    }

    public function getStatusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
