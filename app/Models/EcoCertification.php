<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EcoCertification extends Model
{
    const CERTIFICATION_TYPES = [
        'organic'     => __('Producción Ecológica'),
        'biodynamic'  => __('Biodinámica'),
        'sustainable' => __('Viticultura Sostenible'),
        'vegan'       => __('Apto Vegano'),
        'fair_trade'  => __('Comercio Justo'),
        'other'       => __('Otro'),
    ];

    const STATUSES = [
        'active'    => __('Activo'),
        'expired'   => __('Caducado'),
        'pending'   => __('Pendiente'),
        'suspended' => __('Suspendido'),
    ];

    protected $fillable = [
        'user_id',
        'name',
        'certification_type',
        'certifying_body',
        'certificate_number',
        'valid_from',
        'valid_until',
        'status',
        'notes',
    ];

    protected $casts = [
        'valid_from'  => 'date',
        'valid_until' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::CERTIFICATION_TYPES[$this->certification_type] ?? $this->certification_type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function isExpiringSoon(): bool
    {
        if ($this->valid_until === null) {
            return false;
        }

        return $this->valid_until->isFuture()
            && $this->valid_until->diffInDays(now()) <= 30;
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
