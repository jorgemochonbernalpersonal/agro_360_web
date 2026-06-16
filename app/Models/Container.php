<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Container extends Model
{
    use Auditable;
    use HasFactory;

    /**
     * Campos excluidos de la auditoría de configuración.
     * El stock (uva en kg, vino en litros) se audita en container_histories
     * vía los services de stock; aquí solo auditamos metadatos del depósito.
     */
    protected $auditExclude = [
        'used_capacity',
        'wine_volume_liters',
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'user_id',
        'container_room_id',
        'name',
        'description',
        'photos',
        'thumbnail_img',
        'capacity',
        'unit',
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
        'wine_volume_liters',
    ];

    protected $casts = [
        'capacity' => 'decimal:2',
        'unit' => 'string',
        'used_capacity' => 'decimal:2',
        'wine_volume_liters' => 'decimal:3',
        'quantity' => 'integer',
        'purchase_date' => 'date',
        'next_maintenance_date' => 'datetime',
        'archived' => 'boolean',
        'photos' => 'array',
    ];

    /**
     * Usuario propietario
     */
    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Tipo de contenedor
     */
    /** @return BelongsTo<ContainerType, $this> */
    public function containerType(): BelongsTo
    {
        return $this->belongsTo(ContainerType::class, 'type_id');
    }

    /**
     * Material del contenedor
     */
    /** @return BelongsTo<ContainerMaterial, $this> */
    public function containerMaterial(): BelongsTo
    {
        return $this->belongsTo(ContainerMaterial::class, 'material_id');
    }

    /**
     * Unidad de medida
     */
    /** @return BelongsTo<UnitOfMeasurement, $this> */
    public function unitOfMeasurement(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasurement::class);
    }

    /**
     * Sala/Bodega donde está ubicado
     */
    /** @return BelongsTo<ContainerRoom, $this> */
    public function containerRoom(): BelongsTo
    {
        return $this->belongsTo(ContainerRoom::class);
    }

    /**
     * Todos los estados activos del contenedor (uno por cosecha).
     */
    /** @return HasMany<ContainerCurrentState, $this> */
    public function currentStates(): HasMany
    {
        return $this->hasMany(ContainerCurrentState::class);
    }

    /**
     * Estado más reciente del contenedor (para compatibilidad con vistas legacy).
     * Para multi-cosecha usa currentStates().
     */
    /** @return HasOne<ContainerCurrentState, $this> */
    public function currentState(): HasOne
    {
        return $this->hasOne(ContainerCurrentState::class)->latestOfMany();
    }

    /**
     * Historial de movimientos
     */
    /** @return HasMany<ContainerHistory, $this> */
    public function histories(): HasMany
    {
        return $this->hasMany(ContainerHistory::class)->orderBy('start_date', 'desc');
    }

    /**
     * Cosechas que usan este contenedor
     */
    /** @return HasMany<Harvest, $this> */
    public function harvests(): HasMany
    {
        return $this->hasMany(Harvest::class, 'container_id');
    }

    /**
     * Mantenimientos del contenedor
     */
    /** @return HasMany<ContainerMaintenance, $this> */
    public function maintenances(): HasMany
    {
        return $this->hasMany(ContainerMaintenance::class)->orderByDesc('scheduled_date');
    }

    /**
     * Aditivos enológicos aplicados al contenido activo
     */
    /** @return HasMany<ContainerAdditiveSupply, $this> */
    public function additiveSupplies(): HasMany
    {
        return $this->hasMany(ContainerAdditiveSupply::class)->orderByDesc('additive_date');
    }

    /**
     * Procesos de vinificación en los que participa este contenedor
     */
    /** @return BelongsToMany<WineProcessDetail, $this> */
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
     * Total ocupado (uva kg + vino litros, misma unidad de referencia = capacidad)
     */
    public function getTotalUsed(): float
    {
        return (float) $this->used_capacity + (float) $this->wine_volume_liters;
    }

    /**
     * Obtener capacidad disponible
     */
    public function getAvailableCapacity(): float
    {
        return max(0, (float) $this->capacity - $this->getTotalUsed());
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

        return round(($this->getTotalUsed() / (float) $this->capacity) * 100, 2);
    }

    /**
     * Incrementar capacidad usada
     */
    public function incrementUsedCapacity(float $quantity): bool
    {
        if (! $this->hasAvailableCapacity($quantity)) {
            return false;
        }

        $this->increment('used_capacity', $quantity);
        $this->refresh();

        return true;
    }

    /**
     * Decrementar capacidad usada (atómico)
     */
    public function decrementUsedCapacity(float $quantity): bool
    {
        $available = (float) $this->used_capacity;
        $actual = min($quantity, $available);

        // El truncamiento evita valores negativos, pero también puede ocultar
        // un descuadre de stock (se pidió descontar más de lo que había).
        // Lo dejamos registrado en lugar de silenciarlo.
        if ($quantity - $available > 0.001) {
            \Illuminate\Support\Facades\Log::warning('[Container] decrementUsedCapacity truncado: posible descuadre de stock', [
                'container_id' => $this->id,
                'requested' => $quantity,
                'available' => $available,
                'applied' => $actual,
            ]);
        }

        if ($actual > 0) {
            $this->decrement('used_capacity', $actual);
            $this->refresh();
        }

        return ($quantity - $actual) <= 0.001;
    }

    /**
     * Verificar si está vacío (sin uva ni vino)
     */
    public function isEmpty(): bool
    {
        return $this->getTotalUsed() <= 0;
    }

    /**
     * Verificar si está lleno
     */
    public function isFull(): bool
    {
        return $this->getTotalUsed() >= (float) $this->capacity;
    }

    /**
     * Scope para contenedores disponibles (con capacidad libre)
     *
     * @param mixed $query
     */
    public function scopeAvailable($query)
    {
        return $query->whereRaw('(used_capacity + wine_volume_liters) < capacity')
            ->where('archived', false);
    }

    /**
     * Scope para contenedores vacíos (sin uva ni vino)
     *
     * @param mixed $query
     */
    public function scopeEmpty($query)
    {
        return $query->whereRaw('(used_capacity + wine_volume_liters) <= 0');
    }

    /**
     * Scope para contenedores llenos
     *
     * @param mixed $query
     */
    public function scopeFull($query)
    {
        return $query->whereRaw('(used_capacity + wine_volume_liters) >= capacity');
    }

    /**
     * Scope para contenedores no archivados
     *
     * @param mixed $query
     */
    public function scopeActive($query)
    {
        return $query->where('archived', false);
    }
}
