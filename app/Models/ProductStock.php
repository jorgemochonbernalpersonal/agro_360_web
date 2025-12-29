<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class ProductStock extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'warehouse_id',
        'batch_number',
        'expiry_date',
        'manufacturing_date',
        'quantity',
        'unit',
        'unit_price',
        'supplier',
        'invoice_number',
        'notes',
        'active',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'manufacturing_date' => 'date',
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(PhytosanitaryProduct::class, 'product_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(ProductStockMovement::class, 'stock_id');
    }

    /**
     * Obtener stock disponible (excluyendo caducados)
     */
    public function getAvailableQuantity(): float
    {
        if ($this->expiry_date && $this->expiry_date->isPast()) {
            return 0;
        }
        return (float) $this->quantity;
    }

    /**
     * Verificar si está próximo a caducar (30 días)
     */
    public function isExpiringSoon(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        if ($this->expiry_date->isPast()) {
            return false;
        }
        return now()->diffInDays($this->expiry_date, false) <= 30;
    }

    /**
     * Verificar si está caducado
     */
    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    /**
     * Registrar consumo (descontar stock) - Similar a HarvestStock
     */
    public function consume(float $quantity, ?PhytosanitaryTreatment $treatment = null, ?string $notes = null): ProductStockMovement
    {
        $quantityBefore = (float) $this->quantity;
        $newQuantity = max(0, $quantityBefore - $quantity);
        
        $this->quantity = $newQuantity;
        $this->save();

        return $this->movements()->create([
            'user_id' => Auth::id() ?? $this->user_id,
            'treatment_id' => $treatment?->id,
            'movement_type' => 'consumption',
            'quantity_change' => -$quantity,
            'quantity_before' => $quantityBefore,
            'quantity_after' => $newQuantity,
            'notes' => $notes ?? 'Consumo por tratamiento fitosanitario',
        ]);
    }

    /**
     * Registrar compra/entrada - Similar a HarvestStock
     */
    public function addStock(float $quantity, array $attributes = []): ProductStockMovement
    {
        $quantityBefore = (float) $this->quantity;
        $newQuantity = $quantityBefore + $quantity;
        
        if (isset($attributes['unit_price'])) {
            $this->unit_price = $attributes['unit_price'];
        }
        
        $this->quantity = $newQuantity;
        $this->save();

        return $this->movements()->create([
            'user_id' => Auth::id() ?? $this->user_id,
            'movement_type' => 'purchase',
            'quantity_change' => $quantity,
            'quantity_before' => $quantityBefore,
            'quantity_after' => $newQuantity,
            'unit_price' => $attributes['unit_price'] ?? $this->unit_price,
            'reference_number' => $attributes['invoice_number'] ?? null,
            'notes' => $attributes['notes'] ?? null,
        ]);
    }

    /**
     * Scope para obtener stock disponible de un producto
     */
    public function scopeAvailableForProduct($query, int $productId, int $userId)
    {
        return $query->where('product_id', $productId)
            ->where('user_id', $userId)
            ->where('active', true)
            ->where(function($q) {
                $q->whereNull('expiry_date')
                  ->orWhere('expiry_date', '>', now());
            })
            ->where('quantity', '>', 0)
            ->orderBy('expiry_date', 'asc'); // FIFO: primero los que caducan antes
    }
}
