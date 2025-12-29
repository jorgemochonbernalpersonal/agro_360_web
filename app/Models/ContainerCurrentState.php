<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class ContainerCurrentState extends Model
{
    protected $fillable = [
        'container_id',
        'wine_id',
        'wine_process_detail_id',
        'harvest_id',
        'external_grape_id',
        'has_subproducts',
        'current_quantity',
        'available_qty',
        'reserved_qty',
        'sold_qty',
        'location',
        'last_movement_at',
        'last_movement_by',
    ];

    protected $casts = [
        'current_quantity' => 'decimal:2',
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
        return $this->belongsTo(Container::class);
    }

    /**
     * Cosecha actual
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
     * Vino actual (si aplica)
     * Nota: La tabla wines aún no existe, esta relación es para futura integración
     */
    // public function wine(): BelongsTo
    // {
    //     return $this->belongsTo(Wine::class);
    // }

    /**
     * Actualizar cantidad y sincronizar con contenedor
     */
    public function updateQuantity(float $quantity): void
    {
        $oldQuantity = $this->current_quantity ?? 0;
        $difference = $quantity - $oldQuantity;

        $this->current_quantity = $quantity;
        $this->save();

        // Sincronizar con used_capacity del contenedor
        if ($difference != 0 && $this->container) {
            if ($difference > 0) {
                $this->container->incrementUsedCapacity($difference);
            } else {
                $this->container->decrementUsedCapacity(abs($difference));
            }
        }
    }

    /**
     * Verificar si está vacío
     */
    public function isEmpty(): bool
    {
        return $this->current_quantity <= 0;
    }
}
