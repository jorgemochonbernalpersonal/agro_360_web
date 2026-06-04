<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductLot extends Model
{
    protected $table = 'wine_lots';

    protected $fillable = [
        'user_id',
        'wine_id',
        'name',
        'vintage',
        'wine_type',
        'aging_type',
        'agingtime',
        'alcohol',
        'quantity',
        'initial_quantity',
        'unit',
        'available_quantity',
        'reserved_quantity',
        'sold_quantity',
        'price_per_unit',
        'cost_price',
        'notes',
        'archived',
        // Analíticos
        'residual_sugar',
        'total_acidity',
        'volatile_acidity',
        'ph',
        // Formato / comercial
        'ean',
        'bottle_format',
        'units_per_case',
        // Winemaking
        'winemaker',
        'harvest_method',
        'fermentation_vessel',
        'oak_type',
        'oak_months',
        // Viñedo
        'vine_age',
        'altitude',
        'soil_type',
        // Certificaciones
        'is_vegan',
        'is_biodynamic',
        // Marketing
        'awards_notes',
        'production_quantity',
        'bottling_date',
        'release_date',
        // Identificación
        'sku',
        // Descripción comercial
        'description',
        'pairing',
        'tasting_notes',
        'consumption_recommendation',
        'recommended_temperature_min',
        'recommended_temperature_max',
        'tags',
        // Certificaciones adicionales
        'sulfites',
        'ecological',
    ];

    protected $casts = [
        'vintage' => 'integer',
        'agingtime' => 'integer',
        'alcohol' => 'decimal:2',
        'quantity' => 'decimal:3',
        'initial_quantity' => 'decimal:3',
        'available_quantity' => 'decimal:3',
        'reserved_quantity' => 'decimal:3',
        'sold_quantity' => 'decimal:3',
        'price_per_unit' => 'decimal:4',
        'cost_price' => 'decimal:4',
        'archived' => 'boolean',
        // Analíticos
        'residual_sugar' => 'decimal:2',
        'total_acidity' => 'decimal:2',
        'volatile_acidity' => 'decimal:2',
        'ph' => 'decimal:2',
        // Formato
        'units_per_case' => 'integer',
        // Winemaking
        'oak_months' => 'integer',
        // Viñedo
        'vine_age' => 'integer',
        'altitude' => 'integer',
        // Certificaciones
        'is_vegan' => 'boolean',
        'is_biodynamic' => 'boolean',
        // Marketing
        'production_quantity' => 'integer',
        'bottling_date' => 'date',
        'release_date' => 'date',
        // Descripción comercial
        'recommended_temperature_min' => 'decimal:1',
        'recommended_temperature_max' => 'decimal:1',
        // Certificaciones adicionales
        'sulfites' => 'boolean',
        'ecological' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wine(): BelongsTo
    {
        return $this->belongsTo(Wine::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'wine_lot_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(InvoiceStockMovement::class, 'wine_lot_id');
    }

    public function grapeVarieties(): BelongsToMany
    {
        return $this->belongsToMany(GrapeVariety::class, 'wine_lot_grape_varieties', 'wine_lot_id', 'grape_variety_id')
            ->withPivot('percentage')
            ->withTimestamps();
    }

    public function taxes(): BelongsToMany
    {
        return $this->belongsToMany(Tax::class, 'wine_lot_taxes')
            ->withTimestamps();
    }

    public function getFillPercentAttribute(): float
    {
        if ($this->quantity <= 0) {
            return 0;
        }

        return round((float) $this->sold_quantity / (float) $this->quantity * 100, 1);
    }

    /**
     * Returns true when available + reserved + sold == quantity.
     * Any deviation indicates a billing bug or manual inconsistency.
     */
    public function isBalanced(): bool
    {
        return abs($this->stockDrift()) < 0.001;
    }

    /**
     * quantity − (available + reserved + sold).
     * Should always be 0. Positive means unaccounted stock; negative means over-committed.
     */
    public function stockDrift(): float
    {
        $sum = (float) $this->available_quantity
             + (float) $this->reserved_quantity
             + (float) $this->sold_quantity;

        return round((float) $this->quantity - $sum, 3);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeActive($query)
    {
        return $query->where('archived', false);
    }
}
