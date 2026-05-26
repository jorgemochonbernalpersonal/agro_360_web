<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WinerySupply extends Model
{
    const SUPPLY_TYPES = [
        'cleaning'   => __('Limpieza'),
        'sulfiting'  => __('Sulfitado / SO₂'),
        'fining'     => __('Clarificación / Colaje'),
        'filtration' => __('Filtración'),
        'yeast'      => __('Levaduras'),
        'nutrient'   => __('Nutrientes de fermentación'),
        'enzyme'     => __('Enzimas'),
        'tannin'     => __('Taninos enológicos'),
        'acid'       => __('Acidificantes / Desacidificantes'),
        'sugar'      => __('Azúcares (chapitalización)'),
        'analysis'   => __('Análisis de laboratorio'),
        'packaging'  => __('Material de packaging'),
        'other'      => __('Otro'),
    ];

    protected $fillable = [
        'user_id',
        'name',
        'commercial_name',
        'supply_type',
        'unit_of_measurement_id',
        'current_stock',
        'min_stock_alert',
        'expiry_date',
        'notes',
        'active',
    ];

    protected $casts = [
        'current_stock'   => 'decimal:3',
        'min_stock_alert' => 'decimal:3',
        'expiry_date'     => 'date',
        'active'          => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function unitOfMeasurement(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasurement::class);
    }

    public function wineAdditives(): HasMany
    {
        return $this->hasMany(WineAdditive::class, 'winery_supply_id');
    }

    public function maintenanceSupplies(): HasMany
    {
        return $this->hasMany(ContainerMaintenanceSupply::class);
    }

    public function additiveSupplies(): HasMany
    {
        return $this->hasMany(ContainerAdditiveSupply::class);
    }

    public function bottlingSupplies(): HasMany
    {
        return $this->hasMany(WineBottlingSupply::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::SUPPLY_TYPES[$this->supply_type] ?? $this->supply_type;
    }

    public function isLowStock(): bool
    {
        return $this->min_stock_alert !== null && $this->current_stock <= $this->min_stock_alert;
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
