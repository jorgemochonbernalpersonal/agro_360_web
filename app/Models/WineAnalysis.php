<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WineAnalysis extends Model
{
    const ANALYSIS_TYPES = [
        'own'      => 'Análisis propio',
        'external' => 'Laboratorio externo',
    ];

    protected $fillable = [
        'wine_id',
        'container_id',
        'oenologist_id',
        'analysis_date',
        'analysis_type',
        'laboratory_name',
        'alcohol',
        'residual_sugar',
        'total_acidity',
        'volatile_acidity',
        'ph',
        'so2_free',
        'so2_total',
        'density',
        'turbidity',
        'color_intensity',
        'malic_acid',
        'document',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'analysis_date'   => 'date',
        'alcohol'         => 'decimal:2',
        'residual_sugar'  => 'decimal:2',
        'total_acidity'   => 'decimal:2',
        'volatile_acidity'=> 'decimal:2',
        'ph'              => 'decimal:2',
        'so2_free'        => 'decimal:2',
        'so2_total'       => 'decimal:2',
        'density'         => 'decimal:4',
        'turbidity'       => 'decimal:2',
        'color_intensity' => 'decimal:3',
        'malic_acid'      => 'decimal:2',
    ];

    public function wine(): BelongsTo
    {
        return $this->belongsTo(Wine::class);
    }

    public function container(): BelongsTo
    {
        return $this->belongsTo(Container::class);
    }

    public function oenologist(): BelongsTo
    {
        return $this->belongsTo(Oenologist::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getAnalysisTypeLabelAttribute(): string
    {
        return self::ANALYSIS_TYPES[$this->analysis_type] ?? $this->analysis_type;
    }

    /**
     * Indica si el SO₂ libre está por debajo del umbral mínimo de protección (~20 mg/L).
     */
    public function isSo2Freelow(): bool
    {
        return $this->so2_free !== null && (float) $this->so2_free < 20;
    }
}
