<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_id',
        'harvest_id',
        'container_id',
        'marketed_harvest_id',
        'wine_lot_id',
        'name',
        'description',
        'sku',
        'concept_type',
        'quantity',
        'unit',
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
    /** @return BelongsTo<Invoice, $this> */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Lote de producto relacionado (si aplica)
     */
    /** @return BelongsTo<ProductLot, $this> */
    public function wineLot(): BelongsTo
    {
        return $this->belongsTo(ProductLot::class, 'wine_lot_id');
    }

    /**
     * Cosecha relacionada (si aplica)
     */
    /** @return BelongsTo<Harvest, $this> */
    public function harvest(): BelongsTo
    {
        return $this->belongsTo(Harvest::class);
    }

    /**
     * Albarán de venta de cosecha relacionado (si aplica)
     */
    /** @return BelongsTo<MarketedHarvest, $this> */
    public function marketedHarvest(): BelongsTo
    {
        return $this->belongsTo(MarketedHarvest::class, 'marketed_harvest_id');
    }

    /**
     * Impuesto aplicado
     */
    /** @return BelongsTo<Tax, $this> */
    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }

    /**
     * Verificar si está relacionado con una cosecha
     */
    public function hasHarvest(): bool
    {
        return ! is_null($this->harvest_id);
    }

    /**
     * Scope para items activos
     *
     * @param mixed $query
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope para items de cosecha
     *
     * @param mixed $query
     */
    public function scopeHarvest($query)
    {
        return $query->where('concept_type', 'harvest');
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
}
