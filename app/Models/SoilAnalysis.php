<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SoilAnalysis extends Model
{
    public const TEXTURE_CLASSES = [
        'arenoso' => 'Arenoso',
        'franco_arenoso' => 'Franco-Arenoso',
        'franco' => 'Franco',
        'franco_arcilloso' => 'Franco-Arcilloso',
        'arcilloso' => 'Arcilloso',
        'franco_limoso' => 'Franco-Limoso',
        'limoso' => 'Limoso',
    ];

    public const PH_RANGES = [
        'muy_acido' => ['label' => 'Muy ácido',      'min' => 0,    'max' => 5.5,  'color' => 'red'],
        'acido' => ['label' => 'Ácido',          'min' => 5.5,  'max' => 6.5,  'color' => 'amber'],
        'neutro' => ['label' => 'Neutro',         'min' => 6.5,  'max' => 7.5,  'color' => 'green'],
        'basico' => ['label' => 'Básico',         'min' => 7.5,  'max' => 8.5,  'color' => 'blue'],
        'muy_basico' => ['label' => 'Muy básico',     'min' => 8.5,  'max' => 14,   'color' => 'violet'],
    ];

    protected $table = 'soil_analyses';

    protected $fillable = [
        'viticulturist_id', 'plot_id', 'campaign_id', 'analysis_date',
        'laboratory', 'sample_depth_cm', 'ph', 'organic_matter',
        'nitrogen_total', 'phosphorus', 'potassium', 'calcium',
        'magnesium', 'texture_class', 'electrical_conductivity',
        'limestone', 'notes',
    ];

    protected $casts = [
        'analysis_date' => 'date',
        'sample_depth_cm' => 'integer',
        'ph' => 'decimal:2',
        'organic_matter' => 'decimal:2',
        'nitrogen_total' => 'decimal:2',
        'phosphorus' => 'decimal:2',
        'potassium' => 'decimal:2',
        'calcium' => 'decimal:2',
        'magnesium' => 'decimal:2',
        'electrical_conductivity' => 'decimal:2',
        'limestone' => 'decimal:2',
    ];

    public static function textureClassOptions(): array
    {
        return array_map(fn ($v) => __($v), static::TEXTURE_CLASSES);
    }

    public static function phRangeOptions(): array
    {
        return array_map(fn ($v) => array_merge($v, ['label' => __($v['label'])]), static::PH_RANGES);
    }

    // ── Relaciones ───────────────────────────────────────────────────────────

    /** @return BelongsTo<User, $this> */
    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    /** @return BelongsTo<Plot, $this> */
    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }

    /** @return BelongsTo<Campaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    // ── Accessors ────────────────────────────────────────────────────────────

    public function getTextureLabelAttribute(): string
    {
        return __(self::TEXTURE_CLASSES[$this->texture_class] ?? $this->texture_class ?? '—');
    }

    public function getPhRangeAttribute(): ?array
    {
        if ($this->ph === null) {
            return null;
        }
        foreach (self::PH_RANGES as $range) {
            if ($this->ph >= $range['min'] && $this->ph < $range['max']) {
                return $range;
            }
        }

        return null;
    }

    public function getPhColorAttribute(): string
    {
        return $this->ph_range['color'] ?? 'zinc';
    }

    public function getPhLabelAttribute(): string
    {
        return __($this->ph_range['label'] ?? '—');
    }
}
