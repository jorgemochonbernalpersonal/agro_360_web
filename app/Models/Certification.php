<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certification extends Model
{
    protected $fillable = [
        'viticulturist_id', 'certification_type', 'certifying_body',
        'certificate_number', 'issue_date', 'expiry_date', 'scope',
        'audit_date', 'notes', 'active',
    ];

    protected $casts = [
        'issue_date'  => 'date',
        'expiry_date' => 'date',
        'audit_date'  => 'date',
        'active'      => 'boolean',
    ];

    const CERTIFICATION_TYPES = [
        'ecologico'              => __('Agricultura Ecológica'),
        'produccion_integrada'   => __('Producción Integrada'),
        'globalgap'              => __('GlobalG.A.P.'),
        'rainforest'             => __('Rainforest Alliance'),
        'denominacion_origen'    => __('Denominación de Origen'),
        'indicacion_geografica'  => __('Indicación Geográfica Protegida'),
        'otro'                   => __('Otra certificación'),
    ];

    const TYPE_COLORS = [
        'ecologico'              => 'green',
        'produccion_integrada'   => 'teal',
        'globalgap'              => 'blue',
        'rainforest'             => 'emerald',
        'denominacion_origen'    => 'agro',
        'indicacion_geografica'  => 'violet',
        'otro'                   => 'zinc',
    ];

    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    public function getCertificationTypeLabelAttribute(): string
    {
        return self::CERTIFICATION_TYPES[$this->certification_type] ?? $this->certification_type;
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        return $this->expiry_date
            && !$this->expiry_date->isPast()
            && now()->diffInDays($this->expiry_date) <= 60;
    }

    public function getTypeColorAttribute(): string
    {
        return self::TYPE_COLORS[$this->certification_type] ?? 'zinc';
    }
}
