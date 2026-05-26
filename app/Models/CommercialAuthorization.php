<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommercialAuthorization extends Model
{
    use HasFactory;

    const AUTHORIZATION_TYPES = [
        'do_registration'       => __('Inscripción Denominación de Origen'),
        'organic_certification' => __('Certificación Ecológica'),
        'planting_right'        => __('Derecho de Plantación'),
        'replanting_right'      => __('Derecho de Replantación'),
        'integrated_production' => __('Producción Integrada'),
        'other'                 => __('Otro'),
    ];

    protected $fillable = [
        'viticulturist_id',
        'exploitation_id',
        'authorization_type',
        'authorization_code',
        'description',
        'issuing_body',
        'issue_date',
        'expiry_date',
        'document_file',
        'notes',
        'active',
    ];

    protected $casts = [
        'issue_date'  => 'date',
        'expiry_date' => 'date',
        'active'      => 'boolean',
    ];

    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    public function exploitation(): BelongsTo
    {
        return $this->belongsTo(Exploitation::class);
    }

    public function getAuthorizationTypeLabelAttribute(): string
    {
        return self::AUTHORIZATION_TYPES[$this->authorization_type] ?? $this->authorization_type;
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isExpiringSoon(int $days = 60): bool
    {
        if (!$this->expiry_date) return false;
        return !$this->isExpired() && $this->expiry_date->lte(now()->addDays($days));
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeForViticulturist($query, int $viticulturistId)
    {
        return $query->where('viticulturist_id', $viticulturistId);
    }

    public function scopeExpiringSoon($query, int $days = 60)
    {
        return $query->whereNotNull('expiry_date')
            ->where('expiry_date', '>=', now())
            ->where('expiry_date', '<=', now()->addDays($days));
    }
}
