<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FieldEquipment extends Model
{
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
        'purchase_date'         => 'date',
        'last_inspection_date'  => 'date',
        'next_inspection_date'  => 'date',
        'active'                => 'boolean',
    ];

    public const TYPES = [
        'sprayer'    => __('Pulverizador'),
        'spreader'   => __('Abonadora/Esparcidora'),
        'irrigation' => __('Equipo de riego'),
        'tractor'    => __('Tractor'),
        'harvester'  => __('Vendimidora'),
        'pruner'     => __('Podadora'),
        'mower'      => __('Segadora/Trituradora'),
        'other'      => __('Otro'),
    ];

    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->equipment_type] ?? $this->equipment_type;
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
