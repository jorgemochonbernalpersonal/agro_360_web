<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoQualification extends Model
{
    public const RESULT_QUALIFIED = 'qualified';

    public const RESULT_DISQUALIFIED = 'disqualified';

    public const RESULT_PENDING = 'pending';

    public const RESULT_LABELS = [
        'qualified' => 'Calificado',
        'disqualified' => 'Descalificado',
        'pending' => 'Pendiente',
    ];

    public const RESULT_COLORS = [
        'qualified' => 'agro',
        'disqualified' => 'red',
        'pending' => 'yellow',
    ];

    public const COLOR_LABELS = [
        'tinto' => 'Tinto',
        'blanco' => 'Blanco',
        'rosado' => 'Rosado',
        'espumoso' => 'Espumoso',
        'dulce' => 'Dulce',
        'otro' => 'Otro',
    ];

    protected $table = 'do_qualifications';

    protected $fillable = [
        'supervisor_id',
        'winery_id',
        'vintage',
        'wine_name',
        'color',
        'alcohol_percentage',
        'brix_degree',
        'acidity_level',
        'ph_level',
        'visual_score',
        'aroma_score',
        'taste_score',
        'overall_score',
        'result',
        'tasting_notes',
        'qualification_date',
        'qualified_by',
    ];

    protected $casts = [
        'qualification_date' => 'date',
        'vintage' => 'integer',
        'alcohol_percentage' => 'decimal:2',
        'brix_degree' => 'decimal:2',
        'acidity_level' => 'decimal:2',
        'ph_level' => 'decimal:2',
    ];

    public static function resultLabelOptions(): array
    {
        return array_map(fn ($v) => __($v), static::RESULT_LABELS);
    }

    public static function colorLabelOptions(): array
    {
        return array_map(fn ($v) => __($v), static::COLOR_LABELS);
    }

    /** @return BelongsTo<User, $this> */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /** @return BelongsTo<User, $this> */
    public function winery(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winery_id');
    }

    /** @return BelongsTo<User, $this> */
    public function qualifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'qualified_by');
    }

    public function scopeForSupervisor($query, int $supervisorId)
    {
        return $query->where('supervisor_id', $supervisorId);
    }

    public function scopeForWinery($query, int $wineryId)
    {
        return $query->where('winery_id', $wineryId);
    }
}
