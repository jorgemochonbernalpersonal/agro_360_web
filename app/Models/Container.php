<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
     * Sala/Bodega donde está ubicado
     */
    public function containerRoom(): BelongsTo
    {
        return $this->belongsTo(ContainerRoom::class);
    }

    /**
     * Estado actual del contenedor
     */
    public function currentState(): HasOne
    {
        return $this->hasOne(ContainerCurrentState::class);
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
     * Obtener la cosecha actual del contenedor (helper method)
     * Nota: No es una relación Eloquent, es un método helper
     */
    public function getCurrentHarvest(): ?Harvest
    {
        // Primero intentar obtener desde currentState
        if ($this->relationLoaded('currentState') && $this->currentState && $this->currentState->harvest_id) {
            return $this->currentState->harvest;
        }

        // Si no, obtener la primera cosecha asociada
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

