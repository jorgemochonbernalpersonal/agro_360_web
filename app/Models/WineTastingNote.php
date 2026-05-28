<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WineTastingNote extends Model
{
    const VISUAL_CLARITY = [
        'brilliant'     => 'Brillante',
        'clear'         => 'Limpio',
        'slightly_hazy' => 'Ligeramente turbio',
        'hazy'          => 'Turbio',
    ];

    const VISUAL_INTENSITY = [
        'pale'      => 'Pálido',
        'medium'    => 'Medio',
        'deep'      => 'Intenso',
        'very_deep' => 'Muy intenso',
    ];

    const AROMA_INTENSITY = [
        'light'     => 'Ligero',
        'medium'    => 'Medio',
        'pronounced'=> 'Pronunciado',
        'complex'   => 'Complejo',
    ];

    const PALATE_LEVEL = [
        'low'          => 'Bajo',
        'medium_minus' => 'Medio-',
        'medium'       => 'Medio',
        'medium_plus'  => 'Medio+',
        'high'         => 'Alto',
    ];

    const PALATE_BODY = [
        'light'  => 'Ligero',
        'medium' => 'Medio',
        'full'   => 'Pleno',
    ];

    const PALATE_FINISH = [
        'short'  => 'Corto',
        'medium' => 'Medio',
        'long'   => 'Largo',
    ];

    public static function visualClarityOptions(): array
    {
        return array_map(fn ($v) => __($v), static::VISUAL_CLARITY);
    }

    public static function visualIntensityOptions(): array
    {
        return array_map(fn ($v) => __($v), static::VISUAL_INTENSITY);
    }

    public static function aromaIntensityOptions(): array
    {
        return array_map(fn ($v) => __($v), static::AROMA_INTENSITY);
    }

    public static function palateLevelOptions(): array
    {
        return array_map(fn ($v) => __($v), static::PALATE_LEVEL);
    }

    public static function palateBodyOptions(): array
    {
        return array_map(fn ($v) => __($v), static::PALATE_BODY);
    }

    public static function palateFinishOptions(): array
    {
        return array_map(fn ($v) => __($v), static::PALATE_FINISH);
    }

    protected $fillable = [
        'user_id',
        'wine_id',
        'oenologist_id',
        'evaluation_date',
        'evaluator_name',
        'visual_color',
        'visual_clarity',
        'visual_intensity',
        'aroma_intensity',
        'aroma_descriptors',
        'palate_acidity',
        'palate_tannins',
        'palate_body',
        'palate_finish',
        'overall_score',
        'overall_conclusion',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'evaluation_date' => 'date',
        'overall_score'   => 'decimal:1',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wine(): BelongsTo
    {
        return $this->belongsTo(Wine::class);
    }

    public function oenologist(): BelongsTo
    {
        return $this->belongsTo(Oenologist::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    public function getScoreBadgeColorAttribute(): string
    {
        $score = (float) $this->overall_score;
        if ($score >= 90) return 'green';
        if ($score >= 80) return 'blue';
        if ($score >= 70) return 'yellow';
        return 'zinc';
    }

    public function getEvaluatorDisplayAttribute(): string
    {
        if ($this->oenologist) {
            return $this->oenologist->surname . ', ' . $this->oenologist->name;
        }
        return $this->evaluator_name ?? '—';
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
