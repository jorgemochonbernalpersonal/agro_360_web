<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BottlingAuthorization extends Model
{
    const AUTHORIZATION_TYPES = [
        'standard' => __('Estándar'),
        'export'   => __('Exportación'),
        'organic'  => __('Ecológico'),
        'other'    => __('Otro'),
    ];

    const STATUSES = [
        'active'    => __('Activa'),
        'used'      => __('Consumida'),
        'expired'   => __('Caducada'),
        'cancelled' => __('Anulada'),
    ];

    protected $fillable = [
        'user_id',
        'authorization_number',
        'authorization_type',
        'wine_id',
        'authorized_volume_liters',
        'valid_from',
        'valid_until',
        'issuing_authority',
        'status',
        'conditions',
        'notes',
    ];

    protected $casts = [
        'valid_from'               => 'date',
        'valid_until'              => 'date',
        'authorized_volume_liters' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wine(): BelongsTo
    {
        return $this->belongsTo(Wine::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::AUTHORIZATION_TYPES[$this->authorization_type] ?? $this->authorization_type;
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
