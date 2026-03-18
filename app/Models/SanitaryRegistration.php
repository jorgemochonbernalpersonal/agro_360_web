<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SanitaryRegistration extends Model
{
    const REGISTRATION_TYPES = [
        'rgseaa' => 'RGSEAA (Alimentario)',
        'resa'   => 'RESA (Actividad Alimentaria)',
        'rpo'    => 'RPO (Operador)',
        'other'  => 'Otro',
    ];

    const STATUSES = [
        'active'    => 'Activo',
        'expired'   => 'Caducado',
        'suspended' => 'Suspendido',
        'cancelled' => 'Cancelado',
    ];

    protected $fillable = [
        'user_id',
        'registration_number',
        'registration_type',
        'activity_description',
        'registration_date',
        'renewal_date',
        'issuing_authority',
        'status',
        'notes',
    ];

    protected $casts = [
        'registration_date' => 'date',
        'renewal_date'      => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::REGISTRATION_TYPES[$this->registration_type] ?? $this->registration_type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function isExpiringSoon(): bool
    {
        if ($this->renewal_date === null) {
            return false;
        }

        return $this->renewal_date->isFuture()
            && $this->renewal_date->diffInDays(now()) <= 30;
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
