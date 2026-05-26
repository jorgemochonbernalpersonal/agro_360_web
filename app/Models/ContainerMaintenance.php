<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'cleaning'         => __('Limpieza'),
        'sulfuring'        => __('Sulfitado'),
        'inspection'       => __('Inspección'),
        'repair'           => __('Reparación'),
        'tartrate_removal' => __('Desconfitado (tartratos)'),
        'other'            => __('Otro'),
    ];

    const STATUSES = [
        'scheduled'  => __('Programado'),
        'in_progress'=> __('En curso'),
        'in_review'  => __('En revisión'),
        'approved'   => __('Aprobado'),
        'completed'  => __('Completado'),
        'cancelled'  => __('Cancelado'),
    ];

    public function container(): BelongsTo
    {
        return $this->belongsTo(Container::class);
    }

    public function supplies(): HasMany
    {
        return $this->hasMany(ContainerMaintenanceSupply::class, 'container_maintenance_id');
    }

    public function wastes(): HasMany
    {
        return $this->hasMany(ContainerMaintenanceWaste::class, 'container_maintenance_id');
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
