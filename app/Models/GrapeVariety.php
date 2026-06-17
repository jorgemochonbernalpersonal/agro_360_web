<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

/**
 * @property mixed $pivot
 */
class GrapeVariety extends Model
{
    use HasTranslations;

    const CROP_TYPES = [
        'wine' => 'Uva (vino)',
        'olive' => 'Aceituna (aceite)',
        'other' => 'Otro cultivo',
    ];

    const CROP_TYPE_ICONS = [
        'wine' => 'scissors',
        'olive' => 'sun',
        'other' => 'sparkles',
    ];

    public array $translatable = ['name', 'description'];

    protected $fillable = [
        'name',
        'code',
        'color',
        'crop_type',
        'description',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public static function cropTypeOptions(): array
    {
        return array_map(fn ($v) => __($v), static::CROP_TYPES);
    }

    public function useFallbackLocale(): bool
    {
        return true;
    }

    /**
     * Plantaciones de esta variedad
     */
    /** @return HasMany<PlotPlanting, $this> */
    public function plantings(): HasMany
    {
        return $this->hasMany(PlotPlanting::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeByColor($query, $color)
    {
        return $query->where('color', $color);
    }

    public function scopeWine($query)
    {
        return $query->where('crop_type', 'wine');
    }

    public function scopeByCropType($query, string $type)
    {
        return $query->where('crop_type', $type);
    }

    public function getCropTypeLabelAttribute(): string
    {
        return __(self::CROP_TYPES[$this->crop_type] ?? $this->crop_type);
    }

    public function getCropTypeIconAttribute(): string
    {
        return self::CROP_TYPE_ICONS[$this->crop_type] ?? 'sparkles';
    }
}
