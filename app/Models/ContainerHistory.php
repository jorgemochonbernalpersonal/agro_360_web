<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class ContainerHistory extends Model
{
    protected $fillable = [
        'container_id',
        'wine_id',
        'wine_process_detail_id',
        'harvest_id',
        'external_grape_id',
        'has_subproducts',
        'field_activity_id',
        'operation_type',
        'created_by',
        'quantity',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'has_subproducts' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    /**
     * Contenedor
     */
    public function container(): BelongsTo
    {
        return $this->belongsTo(Container::class);
    }

    /**
     * Cosecha
     */
    public function harvest(): BelongsTo
    {
        return $this->belongsTo(Harvest::class);
    }

    /**
     * Vino
     * Nota: La tabla wines aún no existe, esta relación es para futura integración
     */
    // public function wine(): BelongsTo
    // {
    //     return $this->belongsTo(Wine::class);
    // }

    /**
     * Usuario que creó el movimiento
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Actividad de campo relacionada
     */
    public function fieldActivity(): BelongsTo
    {
        return $this->belongsTo(AgriculturalActivity::class, 'field_activity_id');
    }

    /**
     * Verificar si es una entrada (cantidad positiva)
     */
    public function isInbound(): bool
    {
        return $this->quantity > 0;
    }

    /**
     * Verificar si es una salida (cantidad negativa)
     */
    public function isOutbound(): bool
    {
        return $this->quantity < 0;
    }

    /**
     * Obtener descripción legible del tipo de operación
     */
    public function getOperationDescription(): string
    {
        return match ($this->operation_type) {
            'fill' => 'Llenado',
            'empty' => 'Vaciamiento',
            'transfer' => 'Transferencia',
            'sale' => 'Venta',
            'adjustment' => 'Ajuste',
            'maintenance' => 'Mantenimiento',
            default => 'Operación desconocida',
        };
    }
}
