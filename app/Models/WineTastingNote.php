<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WineTastingNote extends Model
{
    const VISUAL_CLARITY = [
        'brilliant'     => __('Brillante'),
        'clear'         => __('Limpio'),
        'slightly_hazy' => __('Ligeramente turbio'),
        'hazy'          => __('Turbio'),
    ];

    const VISUAL_INTENSITY = [
        'pale'      => __('Pálido'),
        'medium'    => __('Medio'),
        'deep'      => __('Intenso'),
        'very_deep' => __('Muy intenso'),
    ];

    const AROMA_INTENSITY = [
        'light'     => __('Ligero'),
        'medium'    => __('Medio'),
        'pronounced'=> __('Pronunciado'),
        'complex'   => __('Complejo'),
    ];

    const PALATE_LEVEL = [
        'low'          => __('Bajo'),
        'medium_minus' => __('Medio-'),
        'medium'       => __('Medio'),
        'medium_plus'  => __('Medio+'),
        'high'         => __('Alto'),
    ];

    const PALATE_BODY = [
        'light'  => __('Ligero'),
        'medium' => __('Medio'),
        'full'   => __('Pleno'),
    ];

    const PALATE_FINISH = [
        'short'  => __('Corto'),
        'medium' => __('Medio'),
        'long'   => __('Largo'),
    ];

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
