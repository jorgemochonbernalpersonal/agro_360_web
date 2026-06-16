<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FieldEquipment extends Model
{
    public const TYPES = [
        'sprayer' => 'Pulverizador',
        'spreader' => 'Abonadora/Esparcidora',
        'irrigation' => 'Equipo de riego',
        'tractor' => 'Tractor',
        'harvester' => 'Vendimidora',
        'pruner' => 'Podadora',
        'mower' => 'Segadora/Trituradora',
        'other' => 'Otro',
    ];

    protected $fillable = [
        'viticulturist_id',
        'name',
        'equipment_type',
        'registration_number',
        'purchase_date',
        'last_inspection_date',
        'next_inspection_date',
        'inspection_entity',
        'active',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'last_inspection_date' => 'date',
        'next_inspection_date' => 'date',
        'active' => 'boolean',
    ];

    public static function typeOptions(): array
    {
        return array_map(fn ($v) => __($v), static::TYPES);
    }

    /** @return BelongsTo<User, $this> */
    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return __(self::TYPES[$this->equipment_type] ?? $this->equipment_type);
    }

    public function isInspectionDue(int $days = 30): bool
    {
        return $this->next_inspection_date
            && $this->next_inspection_date->diffInDays(now(), false) >= -$days;
    }

    public function isInspectionOverdue(): bool
    {
        return $this->next_inspection_date && $this->next_inspection_date->isPast();
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeForViticulturist($query, int $userId)
    {
        return $query->where('viticulturist_id', $userId);
    }

    public function scopeSpayers($query)
    {
        return $query->where('equipment_type', 'sprayer');
    }
}
