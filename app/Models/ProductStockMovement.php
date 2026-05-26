<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductStockMovement extends Model
{
    protected $fillable = [
        'stock_id',
        'user_id',
        'treatment_id',
        'movement_type',
        'quantity_change',
        'quantity_before',
        'quantity_after',
        'unit_price',
        'reference_number',
        'notes',
    ];

    protected $casts = [
        'quantity_change' => 'decimal:3',
        'quantity_before' => 'decimal:3',
        'quantity_after' => 'decimal:3',
        'unit_price' => 'decimal:2',
    ];

    public function stock(): BelongsTo
    {
        return $this->belongsTo(ProductStock::class, 'stock_id');
    }

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(PhytosanitaryTreatment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isInbound(): bool
    {
        return in_array($this->movement_type, ['purchase', 'transfer_in', 'adjustment_in']);
    }

    public function isOutbound(): bool
    {
        return in_array($this->movement_type, ['consumption', 'transfer_out', 'expired', 'damaged', 'adjustment_out']);
    }

    public function getMovementDescription(): string
    {
        return match($this->movement_type) {
            'purchase' => __('Compra/Entrada'),
            'consumption' => __('Consumo por tratamiento'),
            'adjustment_in' => __('Ajuste positivo'),
            'adjustment_out' => __('Ajuste negativo'),
            'transfer_in' => __('Transferencia entrada'),
            'transfer_out' => __('Transferencia salida'),
            'expired' => __('Caducado'),
            'damaged' => __('Dañado/Pérdida'),
            default => __('Movimiento desconocido'),
        };
    }
}
