<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WineryDocument extends Model
{
    const DOCUMENT_TYPES = [
        'license'     => 'Licencia',
        'permit'      => 'Permiso',
        'certificate' => 'Certificado',
        'insurance'   => 'Seguro',
        'contract'    => 'Contrato',
        'other'       => 'Otro',
    ];

    public static function documentTypeOptions(): array
    {
        return array_map(fn ($v) => __($v), static::DOCUMENT_TYPES);
    }

    protected $fillable = [
        'user_id',
        'title',
        'document_type',
        'reference_number',
        'issue_date',
        'expiry_date',
        'issuing_authority',
        'notes',
        'active',
    ];

    protected $casts = [
        'issue_date'  => 'date',
        'expiry_date' => 'date',
        'active'      => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return __(self::DOCUMENT_TYPES[$this->document_type] ?? $this->document_type);
    }

    public function isExpiringSoon(): bool
    {
        if ($this->expiry_date === null) {
            return false;
        }

        return $this->expiry_date->isFuture()
            && $this->expiry_date->diffInDays(now()) <= 30;
    }

    public function isExpired(): bool
    {
        if ($this->expiry_date === null) {
            return false;
        }

        return $this->expiry_date->isPast();
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
