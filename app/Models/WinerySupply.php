<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WinerySupply extends Model
{
    const SUPPLY_TYPES = [
        'cleaning' => 'Limpieza',
        'sulfiting' => 'Sulfitado / SO₂',
        'fining' => 'Clarificación / Colaje',
        'filtration' => 'Filtración',
        'yeast' => 'Levaduras',
        'nutrient' => 'Nutrientes de fermentación',
        'enzyme' => 'Enzimas',
        'tannin' => 'Taninos enológicos',
        'acid' => 'Acidificantes / Desacidificantes',
        'sugar' => 'Azúcares (chapitalización)',
        'analysis' => 'Análisis de laboratorio',
        'packaging' => 'Material de packaging',
        'other' => 'Otro',
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
        'current_stock' => 'decimal:3',
        'min_stock_alert' => 'decimal:3',
        'expiry_date' => 'date',
        'active' => 'boolean',
    ];

    public static function supplyTypeOptions(): array
    {
        return array_map(fn ($v) => __($v), static::SUPPLY_TYPES);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<UnitOfMeasurement, $this> */
    public function unitOfMeasurement(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasurement::class);
    }

    /** @return HasMany<WineAdditive, $this> */
    public function wineAdditives(): HasMany
    {
        return $this->hasMany(WineAdditive::class, 'winery_supply_id');
    }

    /** @return HasMany<ContainerMaintenanceSupply, $this> */
    public function maintenanceSupplies(): HasMany
    {
        return $this->hasMany(ContainerMaintenanceSupply::class);
    }

    /** @return HasMany<ContainerAdditiveSupply, $this> */
    public function additiveSupplies(): HasMany
    {
        return $this->hasMany(ContainerAdditiveSupply::class);
    }

    /** @return HasMany<WineBottlingSupply, $this> */
    public function bottlingSupplies(): HasMany
    {
        return $this->hasMany(WineBottlingSupply::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return __(self::SUPPLY_TYPES[$this->supply_type] ?? $this->supply_type);
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
