<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FertilizationPlan extends Model
{
    const STATUSES = [
        'draft' => 'Borrador',
        'active' => 'Activo',
        'archived' => 'Archivado',
    ];

    const STATUS_COLORS = [
        'draft' => 'zinc',
        'active' => 'green',
        'archived' => 'amber',
    ];

    protected $fillable = [
        'viticulturist_id', 'campaign_id', 'plan_year', 'nitrate_zone',
        'prepared_by', 'approval_date',
        'total_surface_ha', 'total_n_kg_ha', 'total_p_kg_ha', 'total_k_kg_ha',
        'plan_lines', 'status', 'notes', 'active',
    ];

    protected $casts = [
        'approval_date' => 'date',
        'nitrate_zone' => 'boolean',
        'active' => 'boolean',
        'plan_lines' => 'array',
        'total_surface_ha' => 'decimal:4',
        'total_n_kg_ha' => 'decimal:3',
        'total_p_kg_ha' => 'decimal:3',
        'total_k_kg_ha' => 'decimal:3',
    ];

    public static function statusOptions(): array
    {
        return array_map(fn ($v) => __($v), static::STATUSES);
    }

    /** @return BelongsTo<User, $this> */
    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    /** @return BelongsTo<Campaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'zinc';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }
}
