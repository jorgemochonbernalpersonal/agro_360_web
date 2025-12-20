<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'harvest_id',
        'name',
        'description',
        'sku',
        'concept_type',
        'quantity',
        'unit_price',
        'discount_percentage',
        'discount_amount',
        'tax_id',
        'tax_name',
        'tax_rate',
        'tax_base',
        'tax_amount',
        'subtotal',
        'total',
        'status',
        'payment_status',
        'delivery_status',
        'variations',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:4',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:3',
        'tax_rate' => 'decimal:2',
        'tax_base' => 'decimal:3',
        'tax_amount' => 'decimal:3',
        'subtotal' => 'decimal:3',
        'total' => 'decimal:3',
        'variations' => 'array',
    ];

    /**
     * Factura a la que pertenece este item
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Cosecha relacionada (si aplica)
     */
    public function harvest(): BelongsTo
    {
        return $this->belongsTo(Harvest::class);
    }

    /**
     * Impuesto aplicado
     */
    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }

    /**
     * Calcular totales automáticamente
     */
    protected static function booted()
    {
        static::saving(function ($item) {
            // Calcular subtotal (cantidad * precio - descuento)
            $subtotal = $item->quantity * $item->unit_price;
            $discount = $subtotal * ($item->discount_percentage / 100);
            $item->discount_amount = round($discount, 3);
            $item->subtotal = round($subtotal - $discount, 3);
            
            // Calcular base imponible y monto de impuesto
            $item->tax_base = $item->subtotal;
            $item->tax_amount = round($item->tax_base * ($item->tax_rate / 100), 3);
            
            // Calcular total (subtotal + impuesto)
            $item->total = round($item->subtotal + $item->tax_amount, 3);
        });
    }

    /**
     * Verificar si está relacionado con una cosecha
     */
    public function hasHarvest(): bool
    {
        return !is_null($this->harvest_id);
    }

    /**
     * Scope para items activos
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope para items de cosecha
     */
    public function scopeHarvest($query)
    {
        return $query->where('concept_type', 'harvest');
    }
}
