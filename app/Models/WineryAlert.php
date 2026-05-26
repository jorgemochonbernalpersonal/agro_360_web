<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WineryAlert extends Model
{
    const ALERT_TYPES = [
        'maintenance'   => __('Mantenimiento'),
        'expiry'        => __('Caducidad'),
        'stock'         => __('Stock'),
        'fermentation'  => __('Fermentación'),
        'dispute'       => __('Disputa'),
        'label'         => __('Etiquetado'),
        'certification' => __('Certificación'),
        'custom'        => __('Personalizada'),
    ];

    const SEVERITIES = [
        'info'     => __('Informativa'),
        'warning'  => __('Aviso'),
        'critical' => __('Crítica'),
    ];

    protected $fillable = [
        'user_id',
        'alert_type',
        'severity',
        'title',
        'message',
        'reference_type',
        'reference_id',
        'is_read',
        'read_at',
        'auto_generated',
        'triggered_at',
        'expires_at',
    ];

    protected $casts = [
        'is_read'        => 'boolean',
        'auto_generated' => 'boolean',
        'read_at'        => 'datetime',
        'triggered_at'   => 'datetime',
        'expires_at'     => 'datetime',
    ];

    // ── Relations ──────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo('reference');
    }

    // ── Accessors ──────────────────────────────────────────────────────────────

    public function getTypeLabelAttribute(): string
    {
        return self::ALERT_TYPES[$this->alert_type] ?? $this->alert_type;
    }

    public function getSeverityLabelAttribute(): string
    {
        return self::SEVERITIES[$this->severity] ?? $this->severity;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }

    public function scopeBySeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }
}
