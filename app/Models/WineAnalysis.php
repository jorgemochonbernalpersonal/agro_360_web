<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WineAnalysis extends Model
{
    // ─── Constants ─────────────────────────────────────────────────────────────

    const ANALYSIS_TYPES = [
        'standard' => 'Estándar',
        'complete' => 'Completo',
        'organic' => 'Ecológico',
        'custom' => 'Personalizado',
    ];

    const RESULTS = [
        'pending' => 'Pendiente',
        'passed' => 'Conforme',
        'failed' => 'No conforme',
    ];

    // ─── Fillable ──────────────────────────────────────────────────────────────

    protected $fillable = [
        'user_id',
        'wine_id',
        'container_id',
        'analysis_date',
        'analysis_type',
        'laboratory',
        'laboratory_name',
        'sample_reference',
        'alcoholic_strength',
        'total_acidity',
        'volatile_acidity',
        'residual_sugar',
        'free_so2',
        'total_so2',
        'ph',
        'density',
        'turbidity',
        'color_intensity',
        'malic_acid',
        'alcohol',
        'so2_free',
        'so2_total',
        'document',
        'result',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'analysis_date' => 'date',
        'alcoholic_strength' => 'decimal:2',
        'alcohol' => 'decimal:2',
        'residual_sugar' => 'decimal:2',
        'total_acidity' => 'decimal:2',
        'volatile_acidity' => 'decimal:2',
        'ph' => 'decimal:2',
        'free_so2' => 'decimal:1',
        'total_so2' => 'decimal:1',
        'so2_free' => 'decimal:2',
        'so2_total' => 'decimal:2',
        'density' => 'decimal:4',
        'turbidity' => 'decimal:2',
        'color_intensity' => 'decimal:3',
        'malic_acid' => 'decimal:2',
    ];

    public static function analysisTypeOptions(): array
    {
        return array_map(fn ($v) => __($v), static::ANALYSIS_TYPES);
    }

    public static function resultOptions(): array
    {
        return array_map(fn ($v) => __($v), static::RESULTS);
    }

    // ─── Relations ─────────────────────────────────────────────────────────────

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Wine, $this> */
    public function wine(): BelongsTo
    {
        return $this->belongsTo(Wine::class);
    }

    /** @return BelongsTo<Container, $this> */
    public function container(): BelongsTo
    {
        return $this->belongsTo(Container::class);
    }

    /** @return BelongsTo<Oenologist, $this> */
    public function oenologist(): BelongsTo
    {
        return $this->belongsTo(Oenologist::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─── Accessors ─────────────────────────────────────────────────────────────

    public function getTypeLabelAttribute(): string
    {
        return self::ANALYSIS_TYPES[$this->analysis_type];
    }

    public function getResultLabelAttribute(): string
    {
        return self::RESULTS[$this->result ?? 'pending'] ?? ($this->result ?? 'Pendiente');
    }

    /** @deprecated kept for backward-compat with Wine::Show */
    public function getAnalysisTypeLabelAttribute(): string
    {
        return $this->getTypeLabelAttribute();
    }

    // ─── Scopes ────────────────────────────────────────────────────────────────

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForWine(Builder $query, int $wineId): Builder
    {
        return $query->where('wine_id', $wineId);
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Indicates if free SO₂ is below the minimum protection threshold (~20 mg/L).
     * Works with both old (so2_free) and new (free_so2) column names.
     */
    public function isSo2FreeLow(): bool
    {
        $value = $this->free_so2 ?? $this->so2_free;

        return $value !== null && (float) $value < 20;
    }
}
