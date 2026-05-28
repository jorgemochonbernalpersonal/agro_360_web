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
        'consejeria'    => 'Consejería Agricultura',
        'denominacion'  => 'Denominación de Origen',
        'cooperativa'   => 'Cooperativa',
        'asesoria'      => 'Asesoría Técnica',
        'otro'          => 'Otro',
    ];

    public static function sourceOptions(): array
    {
        return array_map(fn ($v) => __($v), static::SOURCES);
    }

    public const ALERT_TYPES = [
        'plaga'         => 'Plaga',
        'enfermedad'    => 'Enfermedad',
        'climatologica' => 'Climatológica',
        'normativa'     => 'Normativa',
        'otro'          => 'Otro',
    ];

    public static function alertTypeOptions(): array
    {
        return array_map(fn ($v) => __($v), static::ALERT_TYPES);
    }

    public const SEVERITIES = [
        'baja'    => 'Baja',
        'media'   => 'Media',
        'alta'    => 'Alta',
        'critica' => 'Crítica',
    ];

    public static function severityOptions(): array
    {
        return array_map(fn ($v) => __($v), static::SEVERITIES);
    }

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
        return __(self::SOURCES[$this->source] ?? ucfirst($this->source));
    }

    public function getAlertTypeLabelAttribute(): string
    {
        return __(self::ALERT_TYPES[$this->alert_type] ?? ucfirst($this->alert_type));
    }

    public function getSeverityLabelAttribute(): string
    {
        return __(self::SEVERITIES[$this->severity] ?? ucfirst($this->severity));
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
