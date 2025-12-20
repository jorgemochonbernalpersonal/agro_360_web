<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContainerState extends Model
{
    protected $fillable = [
        'container_id',
        'content_type',
        'harvest_id',
        'wine_id',
        'wine_process_id',
        'external_grape_id',
        'total_quantity',
        'available_qty',
        'reserved_qty',
        'sold_qty',
        'has_subproducts',
        'location',
        'last_movement_at',
        'last_movement_by',
    ];

    protected $casts = [
        'total_quantity' => 'decimal:3',
        'available_qty' => 'decimal:3',
        'reserved_qty' => 'decimal:3',
        'sold_qty' => 'decimal:3',
        'has_subproducts' => 'boolean',
        'last_movement_at' => 'datetime',
    ];

    /**
     * Contenedor
     */
    public function container(): BelongsTo
    {
        return $this->belongsTo(HarvestContainer::class, 'container_id');
    }

    /**
     * Cosecha actual (si content_type = 'harvest')
     */
    public function harvest(): BelongsTo
    {
        return $this->belongsTo(Harvest::class);
    }

    /**
     * Usuario que realizó el último movimiento
     */
    public function lastMovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_movement_by');
    }

    /**
     * Scope para contenedores vacíos
     */
    public function scopeEmpty($query)
    {
        return $query->where('content_type', 'empty')
            ->orWhere('total_quantity', 0);
    }

    /**
     * Scope para contenedores con cosecha
     */
    public function scopeWithHarvest($query)
    {
        return $query->where('content_type', 'harvest');
    }

    /**
     * Scope para contenedores disponibles (tiene stock disponible)
     */
    public function scopeAvailable($query)
    {
        return $query->where('available_qty', '>', 0);
    }

    /**
     * Verificar si está vacío
     */
    public function isEmpty(): bool
    {
        return $this->content_type === 'empty' || $this->total_quantity <= 0;
    }

    /**
     * Verificar si tiene stock disponible
     */
    public function hasAvailableStock(float $quantity = null): bool
    {
        if ($quantity === null) {
            return $this->available_qty > 0;
        }

        return $this->available_qty >= $quantity;
    }

    /**
     * Obtener porcentaje de ocupación
     */
    public function getOccupancyPercentage(): float
    {
        $containerCapacity = $this->container?->weight ?? 0;
        
        if ($containerCapacity <= 0) {
            return 0;
        }

        return round(($this->total_quantity / $containerCapacity) * 100, 2);
    }

    /**
     * Obtener distribución del stock
     */
    public function getStockDistribution(): array
    {
        $total = $this->total_quantity;

        if ($total <= 0) {
            return [
                'available_percentage' => 0,
                'reserved_percentage' => 0,
                'sold_percentage' => 0,
            ];
        }

        return [
            'available_percentage' => round(($this->available_qty / $total) * 100, 2),
            'reserved_percentage' => round(($this->reserved_qty / $total) * 100, 2),
            'sold_percentage' => round(($this->sold_qty / $total) * 100, 2),
        ];
    }

    /**
     * Marcar como vacío
     */
    public function markAsEmpty(): void
    {
        $this->update([
            'content_type' => 'empty',
            'total_quantity' => 0,
            'available_qty' => 0,
            'reserved_qty' => 0,
            'sold_qty' => 0,
            'harvest_id' => null,
            'wine_id' => null,
            'wine_process_id' => null,
            'external_grape_id' => null,
            'has_subproducts' => false,
        ]);
    }

    /**
     * Actualizar ubicación
     */
    public function updateLocation(string $location, ?int $userId = null): void
    {
        $this->update([
            'location' => $location,
            'last_movement_at' => now(),
            'last_movement_by' => $userId,
        ]);
    }
}
