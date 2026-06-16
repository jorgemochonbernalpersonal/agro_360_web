<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContainerMaintenance extends Model
{
    const TYPES = [
        'cleaning' => 'Limpieza',
        'sulfuring' => 'Sulfitado',
        'inspection' => 'Inspección',
        'repair' => 'Reparación',
        'tartrate_removal' => 'Desconfitado (tartratos)',
        'other' => 'Otro',
    ];

    const STATUSES = [
        'scheduled' => 'Programado',
        'in_progress' => 'En curso',
        'in_review' => 'En revisión',
        'approved' => 'Aprobado',
        'completed' => 'Completado',
        'cancelled' => 'Cancelado',
    ];

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
        'scheduled_date' => 'date',
        'performed_date' => 'date',
        'next_maintenance_date' => 'date',
        'cost' => 'decimal:2',
    ];

    public static function typeOptions(): array
    {
        return array_map(fn ($v) => __($v), static::TYPES);
    }

    public static function statusOptions(): array
    {
        return array_map(fn ($v) => __($v), static::STATUSES);
    }

    /** @return BelongsTo<Container, $this> */
    public function container(): BelongsTo
    {
        return $this->belongsTo(Container::class);
    }

    /** @return HasMany<ContainerMaintenanceSupply, $this> */
    public function supplies(): HasMany
    {
        return $this->hasMany(ContainerMaintenanceSupply::class, 'container_maintenance_id');
    }

    /** @return HasMany<ContainerMaintenanceWaste, $this> */
    public function wastes(): HasMany
    {
        return $this->hasMany(ContainerMaintenanceWaste::class, 'container_maintenance_id');
    }

    public function getTypeLabel(): string
    {
        return self::TYPES[$this->maintenance_type];
    }

    public function getStatusLabel(): string
    {
        return self::STATUSES[$this->status];
    }
}
