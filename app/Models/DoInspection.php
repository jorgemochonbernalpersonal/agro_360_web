<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoInspection extends Model
{
    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const RESULT_COMPLIANT = 'compliant';

    public const RESULT_NON_COMPLIANT = 'non_compliant';

    public const RESULT_PENDING = 'pending';

    public const STATUS_LABELS = [
        'scheduled' => 'Programada',
        'in_progress' => 'En curso',
        'completed' => 'Completada',
        'cancelled' => 'Cancelada',
    ];

    public const RESULT_LABELS = [
        'compliant' => 'Conforme',
        'non_compliant' => 'No conforme',
        'pending' => 'Pendiente',
    ];

    public const RESULT_COLORS = [
        'compliant' => 'green',
        'non_compliant' => 'red',
        'pending' => 'zinc',
    ];

    protected $table = 'do_inspections';

    protected $fillable = [
        'supervisor_id',
        'subject_type',
        'subject_id',
        'inspection_date',
        'status',
        'result',
        'findings',
        'notes',
        'reference_number',
    ];

    protected $casts = [
        'inspection_date' => 'date',
    ];

    public static function statusLabelOptions(): array
    {
        return array_map(fn ($v) => __($v), static::STATUS_LABELS);
    }

    public static function resultLabelOptions(): array
    {
        return array_map(fn ($v) => __($v), static::RESULT_LABELS);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subject_id');
    }

    public function scopeForSupervisor($query, int $supervisorId)
    {
        return $query->where('supervisor_id', $supervisorId);
    }

    public function scopeForWinery($query, int $wineryId)
    {
        return $query->where('subject_type', 'winery')->where('subject_id', $wineryId);
    }
}
