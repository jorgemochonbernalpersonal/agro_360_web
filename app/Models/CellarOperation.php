<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CellarOperation extends Model
{
    const OPERATION_TYPES = [
        'racking' => 'Trasiego',
        'clarification' => 'Clarificación / Colaje',
        'filtration' => 'Filtración',
        'stabilization' => 'Estabilización',
        'sulfiting' => 'Sulfitado',
        'blending' => 'Cupaje / Mezcla',
        'other' => 'Otra operación',
    ];

    const STATUSES = [
        'planned' => 'Planificada',
        'in_progress' => 'En curso',
        'completed' => 'Completada',
        'cancelled' => 'Cancelada',
    ];

    protected $fillable = [
        'user_id',
        'operation_type',
        'operation_date',
        'source_container_id',
        'target_container_id',
        'volume_liters',
        'responsible_person',
        'status',
        'notes',
    ];

    protected $casts = [
        'operation_date' => 'date',
        'volume_liters' => 'decimal:2',
    ];

    public static function operationTypeOptions(): array
    {
        return array_map(fn ($v) => __($v), static::OPERATION_TYPES);
    }

    public static function statusOptions(): array
    {
        return array_map(fn ($v) => __($v), static::STATUSES);
    }

    // ── Relations ──────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sourceContainer(): BelongsTo
    {
        return $this->belongsTo(Container::class, 'source_container_id');
    }

    public function targetContainer(): BelongsTo
    {
        return $this->belongsTo(Container::class, 'target_container_id');
    }

    // ── Accessors ──────────────────────────────────────────────────────────

    public function getTypeLabelAttribute(): string
    {
        return self::OPERATION_TYPES[$this->operation_type] ?? $this->operation_type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'cancelled');
    }
}
