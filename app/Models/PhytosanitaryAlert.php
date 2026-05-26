<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhytosanitaryAlert extends Model
{
    protected $fillable = [
        'viticulturist_id', 'title', 'source', 'alert_type', 'severity',
        'affected_area', 'description', 'recommendations', 'alert_date',
        'expiry_date', 'active',
    ];

    protected $casts = [
        'alert_date'  => 'date',
        'expiry_date' => 'date',
        'active'      => 'boolean',
    ];

    public const SOURCES = [
        'mapa'          => 'MAPA',
        'consejeria'    => __('Consejería Agricultura'),
        'denominacion'  => __('Denominación de Origen'),
        'cooperativa'   => __('Cooperativa'),
        'asesoria'      => __('Asesoría Técnica'),
        'otro'          => __('Otro'),
    ];

    public const ALERT_TYPES = [
        'plaga'         => __('Plaga'),
        'enfermedad'    => __('Enfermedad'),
        'climatologica' => __('Climatológica'),
        'normativa'     => __('Normativa'),
        'otro'          => __('Otro'),
    ];

    public const SEVERITIES = [
        'baja'    => __('Baja'),
        'media'   => __('Media'),
        'alta'    => __('Alta'),
        'critica' => __('Crítica'),
    ];

    public const SEVERITY_COLORS = [
        'baja'    => 'zinc',
        'media'   => 'blue',
        'alta'    => 'amber',
        'critica' => 'red',
    ];

    public const ALERT_TYPE_ICONS = [
        'plaga'         => 'bug-ant',
        'enfermedad'    => 'shield-exclamation',
        'climatologica' => 'cloud',
        'normativa'     => 'document-text',
        'otro'          => 'bell-alert',
    ];

    // ── Relaciones ───────────────────────────────────────────────────────────

    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    // ── Accessors ────────────────────────────────────────────────────────────

    public function getSourceLabelAttribute(): string
    {
        return self::SOURCES[$this->source] ?? ucfirst($this->source);
    }

    public function getAlertTypeLabelAttribute(): string
    {
        return self::ALERT_TYPES[$this->alert_type] ?? ucfirst($this->alert_type);
    }

    public function getSeverityLabelAttribute(): string
    {
        return self::SEVERITIES[$this->severity] ?? ucfirst($this->severity);
    }

    public function getSeverityColorAttribute(): string
    {
        return self::SEVERITY_COLORS[$this->severity] ?? 'zinc';
    }

    public function getAlertTypeIconAttribute(): string
    {
        return self::ALERT_TYPE_ICONS[$this->alert_type] ?? 'bell-alert';
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeCurrent($query)
    {
        return $query->active()->where(function ($q) {
            $q->whereNull('expiry_date')->orWhereDate('expiry_date', '>=', now());
        });
    }
}
