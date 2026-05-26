<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgriInsurance extends Model
{
    use HasFactory;

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
        'start_date'      => 'date',
        'end_date'        => 'date',
        'insured_amount'  => 'decimal:2',
        'premium'         => 'decimal:2',
        'subsidy_amount'  => 'decimal:2',
    ];

    public const COVERAGE_TYPES = [
        'frost'         => __('Heladas'),
        'hail'          => __('Pedrisco'),
        'drought'       => __('Sequía'),
        'flood'         => __('Inundación'),
        'fire'          => __('Incendio'),
        'pest'          => __('Plagas'),
        'comprehensive' => __('Seguro integral (multirriesgo)'),
        'other'         => __('Otro'),
    ];

    public const STATUSES = [
        'pending'   => __('Pendiente'),
        'active'    => __('Activo'),
        'expired'   => __('Vencido'),
        'cancelled' => __('Cancelado'),
    ];

    public const STATUS_COLORS = [
        'pending'   => 'amber',
        'active'    => 'green',
        'expired'   => 'zinc',
        'cancelled' => 'red',
    ];

    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    public function getCoverageTypeLabelAttribute(): string
    {
        return self::COVERAGE_TYPES[$this->coverage_type] ?? $this->coverage_type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
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
