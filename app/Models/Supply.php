<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supply extends Model
{
    use HasFactory;

    const SUPPLY_TYPES = [
        'phytosanitary' => 'Fitosanitario',
        'fertilizer'    => 'Fertilizante / Abono',
        'seed'          => 'Semilla / Planta',
        'postharvest'   => 'Post-cosecha',
        'other'         => 'Otro',
    ];

    // Factores de emisión CO₂ por tipo de energía (referencia)
    const CO2_FACTORS = [
        'diesel'      => 2.640, // kg CO₂/L
        'gasoline'    => 2.392,
        'electricity' => 0.250, // kg CO₂/kWh (mix español)
        'lpg'         => 1.512,
        'natural_gas' => 2.020,
        'water_pump'  => 0.250,
        'other'       => 0.000,
    ];

    protected $fillable = [
        'viticulturist_id',
        'phytosanitary_product_id',
        'name',
        'commercial_name',
        'registration_number',
        'supply_type',
        'unit_of_measurement',
        'initial_stock',
        'current_stock',
        'min_stock_alert',
        'expiry_date',
        'nutrient_n',
        'nutrient_p2o5',
        'nutrient_k2o',
        'nutrient_cao',
        'nutrient_mgo',
        'nutrient_so3',
        'organic_matter',
        'notes',
        'active',
    ];

    protected $casts = [
        'initial_stock'   => 'decimal:3',
        'current_stock'   => 'decimal:3',
        'min_stock_alert' => 'decimal:3',
        'expiry_date'     => 'date',
        'nutrient_n'      => 'decimal:2',
        'nutrient_p2o5'   => 'decimal:2',
        'nutrient_k2o'    => 'decimal:2',
        'nutrient_cao'    => 'decimal:2',
        'nutrient_mgo'    => 'decimal:2',
        'nutrient_so3'    => 'decimal:2',
        'organic_matter'  => 'decimal:2',
        'active'          => 'boolean',
    ];

    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    public function phytosanitaryProduct(): BelongsTo
    {
        return $this->belongsTo(PhytosanitaryProduct::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(SupplyPurchase::class);
    }

    public function getSupplyTypeLabelAttribute(): string
    {
        return self::SUPPLY_TYPES[$this->supply_type] ?? $this->supply_type;
    }

    public function isLowStock(): bool
    {
        if ($this->min_stock_alert === null) return false;
        return $this->current_stock <= $this->min_stock_alert;
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        if (!$this->expiry_date) return false;
        return $this->expiry_date->lte(now()->addDays($days));
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeForViticulturist($query, int $viticulturistId)
    {
        return $query->where('viticulturist_id', $viticulturistId);
    }

    public function scopeLowStock($query)
    {
        return $query->whereNotNull('min_stock_alert')
            ->whereColumn('current_stock', '<=', 'min_stock_alert');
    }
}
