<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupervisorWineryNote extends Model
{
    public const TYPE_VISIT = 'visit';

    public const TYPE_CALL = 'call';

    public const TYPE_WARNING = 'warning';

    public const TYPE_NOTE = 'note';

    public const TYPE_LABELS = [
        'visit' => 'Visita',
        'call' => 'Llamada',
        'warning' => 'Aviso',
        'note' => 'Nota',
    ];

    public const TYPE_ICONS = [
        'visit' => 'building-office-2',
        'call' => 'phone',
        'warning' => 'exclamation-triangle',
        'note' => 'pencil-square',
    ];

    public const TYPE_COLORS = [
        'visit' => 'blue',
        'call' => 'green',
        'warning' => 'amber',
        'note' => 'zinc',
    ];

    protected $table = 'supervisor_winery_notes';

    protected $fillable = [
        'supervisor_id',
        'winery_id',
        'type',
        'note_date',
        'content',
    ];

    protected $casts = [
        'note_date' => 'date',
    ];

    public static function typeLabelOptions(): array
    {
        return array_map(fn ($v) => __($v), static::TYPE_LABELS);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function winery(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winery_id');
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
