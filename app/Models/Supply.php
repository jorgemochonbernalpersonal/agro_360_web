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
        'fertilizer'    => 'Fertilizante / Abono',
        'seed'          => 'Semilla / Planta',
        'postharvest'   => 'Post-cosecha',
        'other'         => 'Otro',
    ];

    protected $fillable = [
        'viticulturist_id',
        'warehouse_id',
        'name',
        'commercial_name',
        'registration_number',
        'supply_type',
        'unit_of_measurement',
        'initial_stock',
        'current_stock',
        'min_stock_alert',
        'expiry_date',
        'notes',
        'active',
    ];

    protected $casts = [
        'initial_stock'   => 'decimal:3',
        'current_stock'   => 'decimal:3',
        'min_stock_alert' => 'decimal:3',
        'expiry_date'     => 'date',
        'active'          => 'boolean',
    ];

    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
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

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        if (!$this->expiry_date || $this->expiry_date->isPast()) return false;
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
