<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HarvestStock extends Model
{
    protected $fillable = [
        'harvest_id',
        'container_id',
        'user_id',
        'invoice_item_id',
        'movement_type',
        'quantity_change',
        'quantity_after',
        'available_qty',
        'reserved_qty',
        'sold_qty',
        'gifted_qty',
        'lost_qty',
        'notes',
        'reference_number',
    ];

    protected $casts = [
        'quantity_change' => 'decimal:3',
        'quantity_after' => 'decimal:3',
        'available_qty' => 'decimal:3',
        'reserved_qty' => 'decimal:3',
        'sold_qty' => 'decimal:3',
        'gifted_qty' => 'decimal:3',
        'lost_qty' => 'decimal:3',
    ];

    /**
     * Cosecha origen
     */
    public function harvest(): BelongsTo
    {
        return $this->belongsTo(Harvest::class);
    }

    /**
     * Contenedor afectado
     */
    public function container(): BelongsTo
    {
        return $this->belongsTo(HarvestContainer::class, 'container_id');
    }

    /**
     * Usuario que realizó el movimiento
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Item de factura relacionado
     */
    public function invoiceItem(): BelongsTo
    {
        return $this->belongsTo(InvoiceItem::class);
    }

    /**
     * Scope para filtrar por tipo de movimiento
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('movement_type', $type);
    }

    /**
     * Scope para movimientos iniciales
     */
    public function scopeInitial($query)
    {
        return $query->where('movement_type', 'initial');
    }

    /**
     * Scope para ventas
     */
    public function scopeSales($query)
    {
        return $query->where('movement_type', 'sale');
    }

    /**
     * Scope para reservas
     */
    public function scopeReservations($query)
    {
        return $query->where('movement_type', 'reserve');
    }

    /**
     * Scope para ajustes
     */
    public function scopeAdjustments($query)
    {
        return $query->where('movement_type', 'adjustment');
    }

    /**
     * Verificar si es un movimiento de entrada (incrementa stock)
     */
    public function isInbound(): bool
    {
        return in_array($this->movement_type, ['initial', 'return']) 
            || ($this->movement_type === 'adjustment' && $this->quantity_change > 0);
    }

    /**
     * Verificar si es un movimiento de salida (decrementa stock)
     */
    public function isOutbound(): bool
    {
        return in_array($this->movement_type, ['sale', 'gift', 'loss'])
            || ($this->movement_type === 'adjustment' && $this->quantity_change < 0);
    }

    /**
     * Verificar si es un cambio de estado (no cambia cantidad total)
     */
    public function isStateChange(): bool
    {
        return in_array($this->movement_type, ['reserve', 'unreserve']) 
            && $this->quantity_change == 0;
    }

    /**
     * Obtener descripción legible del movimiento
     */
    public function getMovementDescription(): string
    {
        return match($this->movement_type) {
            'initial' => 'Registro inicial de cosecha',
            'adjustment' => $this->quantity_change > 0 ? 'Ajuste positivo (+)' : 'Ajuste negativo (-)',
            'reserve' => 'Reservado para venta',
            'sale' => 'Venta confirmada',
            'unreserve' => 'Reserva cancelada',
            'gift' => 'Donación/Regalo',
            'loss' => 'Pérdida/Merma',
            'return' => 'Devolución',
            default => 'Movimiento desconocido',
        };
    }
}
