<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EcoCertification extends Model
{
    const CERTIFICATION_TYPES = [
        'organic' => 'Producción Ecológica',
        'biodynamic' => 'Biodinámica',
        'sustainable' => 'Viticultura Sostenible',
        'vegan' => 'Apto Vegano',
        'fair_trade' => 'Comercio Justo',
        'other' => 'Otro',
    ];

    const STATUSES = [
        'active' => 'Activo',
        'expired' => 'Caducado',
        'pending' => 'Pendiente',
        'suspended' => 'Suspendido',
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
        'valid_from' => 'date',
        'valid_until' => 'date',
    ];

    public static function certificationTypeOptions(): array
    {
        return array_map(fn ($v) => __($v), static::CERTIFICATION_TYPES);
    }

    public static function statusOptions(): array
    {
        return array_map(fn ($v) => __($v), static::STATUSES);
    }

    /** @return BelongsTo<User, $this> */
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
