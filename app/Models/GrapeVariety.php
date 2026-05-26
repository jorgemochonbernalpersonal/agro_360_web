<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class GrapeVariety extends Model
{
    use HasTranslations;
    const CROP_TYPES = [
        'wine'  => __('Uva (vino)'),
        'olive' => __('Aceituna (aceite)'),
        'other' => __('Otro cultivo'),
    ];

    const CROP_TYPE_ICONS = [
        'wine'  => 'scissors',
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

    /**
     * Plantaciones de esta variedad
     */
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
        return self::CROP_TYPES[$this->crop_type] ?? $this->crop_type;
    }

    public function getCropTypeIconAttribute(): string
    {
        return self::CROP_TYPE_ICONS[$this->crop_type] ?? 'sparkles';
    }
}
