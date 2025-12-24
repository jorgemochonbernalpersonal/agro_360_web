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
    ];

    protected $casts = [
        'current_quantity' => 'decimal:2',
        'has_subproducts' => 'boolean',
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
