<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HarvestDeclaration extends Model
{
    const STATUSES = [
        'draft' => 'Borrador',
        'submitted' => 'Presentada',
        'accepted' => 'Aceptada',
        'rejected' => 'Rechazada',
    ];

    const STATUS_COLORS = [
        'draft' => 'zinc',
        'submitted' => 'blue',
        'accepted' => 'green',
        'rejected' => 'red',
    ];

    protected $fillable = [
        'viticulturist_id',
        'campaign_id',
        'declaration_year',
        'declaration_date',
        'submission_date',
        'authority',
        'reference_number',
        'total_surface_ha',
        'total_kg',
        'declaration_lines',
        'status',
        'rejection_reason',
        'notes',
        'active',
    ];

    protected $casts = [
        'declaration_date' => 'date',
        'submission_date' => 'date',
        'declaration_year' => 'integer',
        'total_surface_ha' => 'decimal:4',
        'total_kg' => 'decimal:2',
        'declaration_lines' => 'array',
        'active' => 'boolean',
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

    public function scopeForViticulturist($query, int $id)
    {
        return $query->where('viticulturist_id', $id);
    }

    public function scopeForCampaign($query, int $id)
    {
        return $query->where('campaign_id', $id);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
