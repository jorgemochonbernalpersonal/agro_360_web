<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FieldApplicator extends Model
{
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
        'is_advisor'       => 'boolean',
        'active'           => 'boolean',
    ];

    public const CATEGORIES = [
        'basic'     => __('Básico'),
        'qualified' => __('Cualificado'),
        'fumigator' => __('Fumigador'),
        'pilot'     => __('Piloto'),
    ];

    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

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
            && !$this->ropo_expiry_date->isPast()
            && $this->ropo_expiry_date->diffInDays(now()) <= $days;
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->ropo_category] ?? $this->ropo_category;
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
