<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FieldApplicator extends Model
{
    public const CATEGORIES = [
        'basic' => 'Básico',
        'qualified' => 'Cualificado',
        'fumigator' => 'Fumigador',
        'pilot' => 'Piloto',
    ];

    protected $fillable = [
        'viticulturist_id',
        'campaign_id',
        'name',
        'ropo_number',
        'ropo_category',
        'ropo_expiry_date',
        'is_advisor',
        'advisor_license',
        'phone',
        'email',
        'active',
    ];

    protected $casts = [
        'ropo_expiry_date' => 'date',
        'is_advisor' => 'boolean',
        'active' => 'boolean',
    ];

    public static function categoryOptions(): array
    {
        return array_map(fn ($v) => __($v), static::CATEGORIES);
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

    public function isRopoExpired(): bool
    {
        return $this->ropo_expiry_date && $this->ropo_expiry_date->isPast();
    }

    public function isRopoExpiringSoon(int $days = 30): bool
    {
        return $this->ropo_expiry_date
            && ! $this->ropo_expiry_date->isPast()
            && $this->ropo_expiry_date->diffInDays(now()) <= $days;
    }

    public function getCategoryLabelAttribute(): string
    {
        return __(self::CATEGORIES[$this->ropo_category] ?? $this->ropo_category);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeForViticulturist($query, int $userId)
    {
        return $query->where('viticulturist_id', $userId);
    }
}
