<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgriInsurance extends Model
{
    use HasFactory;

    public const COVERAGE_TYPES = [
        'frost' => 'Heladas',
        'hail' => 'Pedrisco',
        'drought' => 'Sequía',
        'flood' => 'Inundación',
        'fire' => 'Incendio',
        'pest' => 'Plagas',
        'comprehensive' => 'Seguro integral (multirriesgo)',
        'other' => 'Otro',
    ];

    public const STATUSES = [
        'pending' => 'Pendiente',
        'active' => 'Activo',
        'expired' => 'Vencido',
        'cancelled' => 'Cancelado',
    ];

    public const STATUS_COLORS = [
        'pending' => 'amber',
        'active' => 'green',
        'expired' => 'zinc',
        'cancelled' => 'red',
    ];

    protected $fillable = [
        'viticulturist_id',
        'policy_number',
        'insurance_company',
        'coverage_type',
        'start_date',
        'end_date',
        'insured_amount',
        'premium',
        'subsidy_amount',
        'status',
        'agent_name',
        'agent_phone',
        'covered_plots',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'insured_amount' => 'decimal:2',
        'premium' => 'decimal:2',
        'subsidy_amount' => 'decimal:2',
    ];

    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    public static function coverageTypeOptions(): array
    {
        return array_map(fn ($v) => __($v), static::COVERAGE_TYPES);
    }

    public static function statusOptions(): array
    {
        return array_map(fn ($v) => __($v), static::STATUSES);
    }

    public function getCoverageTypeLabelAttribute(): string
    {
        return __(self::COVERAGE_TYPES[$this->coverage_type] ?? $this->coverage_type);
    }

    public function getStatusLabelAttribute(): string
    {
        return __(self::STATUSES[$this->status] ?? $this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'zinc';
    }

    public function isExpiringSoon(): bool
    {
        return $this->status === 'active' && $this->end_date->diffInDays(now()) <= 30 && $this->end_date->isFuture();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
