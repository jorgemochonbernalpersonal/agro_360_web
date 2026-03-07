<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Container extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'container_room_id',
        'name',
        'description',
        'photos',
        'thumbnail_img',
        'capacity',
        'used_capacity',
        'quantity',
        'serial_number',
        'unit_of_measurement_id',
        'type_id',
        'material_id',
        'oak_type',
        'toast_type',
        'purchase_date',
        'next_maintenance_date',
        'supplier_name',
        'x_position',
        'y_position',
        'archived',
    ];

    protected $casts = [
        'capacity' => 'decimal:2',
        'used_capacity' => 'decimal:2',
        'quantity' => 'integer',
        'purchase_date' => 'date',
        'next_maintenance_date' => 'datetime',
        'archived' => 'boolean',
        'photos' => 'array',
    ];

    /**
     * Usuario propietario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Tipo de contenedor
     */
    public function containerType(): BelongsTo
    {
        return $this->belongsTo(ContainerType::class, 'type_id');
    }

    /**
     * Material del contenedor
     */
    public function containerMaterial(): BelongsTo
    {
        return $this->belongsTo(ContainerMaterial::class, 'material_id');
    }

    /**
     * Unidad de medida
     */
    public function unitOfMeasurement(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasurement::class);
    }

    /**
     * Sala/Bodega donde está ubicado
     */
    public function containerRoom(): BelongsTo
    {
        return $this->belongsTo(ContainerRoom::class);
    }

    /**
     * Todos los estados activos del contenedor (uno por cosecha).
     */
    public function currentStates(): HasMany
    {
        return $this->hasMany(ContainerCurrentState::class);
    }

    /**
     * Estado más reciente del contenedor (para compatibilidad con vistas legacy).
     * Para multi-cosecha usa currentStates().
     */
    public function currentState(): HasOne
    {
        return $this->hasOne(ContainerCurrentState::class)->latestOfMany();
    }

    /**
     * Historial de movimientos
     */
    public function histories(): HasMany
    {
        return $this->hasMany(ContainerHistory::class)->orderBy('start_date', 'desc');
    }

    /**
     * Cosechas que usan este contenedor
     */
    public function harvests(): HasMany
    {
        return $this->hasMany(Harvest::class, 'container_id');
    }

    /**
     * Mantenimientos del contenedor
     */
    public function maintenances(): HasMany
    {
        return $this->hasMany(ContainerMaintenance::class)->orderByDesc('scheduled_date');
    }

    /**
     * Aditivos enológicos aplicados al contenido activo
     */
    public function additiveSupplies(): HasMany
    {
        return $this->hasMany(ContainerAdditiveSupply::class)->orderByDesc('additive_date');
    }

    /**
     * Procesos de vinificación en los que participa este contenedor
     */
    public function wineProcessDetails(): BelongsToMany
    {
        return $this->belongsToMany(WineProcessDetail::class, 'wine_process_detail_containers')
            ->withPivot(['quantity', 'unit_of_measurement_id'])
            ->withTimestamps();
    }

    /**
     * Obtener la cosecha más reciente del contenedor.
     * Para todos los contenidos usa currentStates().
     */
    public function getCurrentHarvest(): ?Harvest
    {
        return $this->harvests()->latest()->first();
    }

    /**
     * Obtener capacidad disponible
     */
    public function getAvailableCapacity(): float
    {
        return max(0, $this->capacity - $this->used_capacity);
    }

    /**
     * Verificar si tiene capacidad disponible
     */
    public function hasAvailableCapacity(float $quantity): bool
    {
        return $this->getAvailableCapacity() >= $quantity;
    }

    /**
     * Obtener porcentaje de ocupación
     */
    public function getOccupancyPercentage(): float
    {
        if ($this->capacity <= 0) {
            return 0;
        }
        return round(($this->used_capacity / $this->capacity) * 100, 2);
    }

    /**
     * Incrementar capacidad usada
     */
    public function incrementUsedCapacity(float $quantity): bool
    {
        if (!$this->hasAvailableCapacity($quantity)) {
            return false;
        }

        $this->used_capacity += $quantity;
        return $this->save();
    }

    /**
     * Decrementar capacidad usada
     */
    public function decrementUsedCapacity(float $quantity): bool
    {
        $this->used_capacity = max(0, $this->used_capacity - $quantity);
        return $this->save();
    }

    /**
     * Verificar si está vacío
     */
    public function isEmpty(): bool
    {
        return $this->used_capacity <= 0;
    }

    /**
     * Verificar si está lleno
     */
    public function isFull(): bool
    {
        return $this->used_capacity >= $this->capacity;
    }

    /**
     * Scope para contenedores disponibles (con capacidad)
     */
    public function scopeAvailable($query)
    {
        return $query->whereColumn('used_capacity', '<', 'capacity')
            ->where('archived', false);
    }

    /**
     * Scope para contenedores vacíos
     */
    public function scopeEmpty($query)
    {
        return $query->where('used_capacity', '<=', 0);
    }

    /**
     * Scope para contenedores llenos
     */
    public function scopeFull($query)
    {
        return $query->whereColumn('used_capacity', '>=', 'capacity');
    }

    /**
     * Scope para contenedores no archivados
     */
    public function scopeActive($query)
    {
        return $query->where('archived', false);
    }
}

