<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BiodiversityRecord extends Model
{
    protected $fillable = [
        'viticulturist_id', 'plot_id', 'campaign_id', 'record_type',
        'description', 'area_m2', 'species', 'record_date',
        'photo_path', 'notes',
    ];

    protected $casts = [
        'record_date' => 'date',
        'area_m2'     => 'decimal:2',
    ];

    public const RECORD_TYPES = [
        'cubierta_vegetal' => 'Cubierta Vegetal',
        'margen'           => 'Margen sin Labrar',
        'seto'             => 'Seto Vivo',
        'fauna_auxiliar'   => 'Fauna Auxiliar',
        'nido'             => 'Nido / Caja Nido',
        'hotel_insectos'   => 'Hotel de Insectos',
        'charca'           => 'Charca / Punto de Agua',
        'otro'             => 'Otro',
    ];

    public const RECORD_TYPE_ICONS = [
        'cubierta_vegetal' => 'leaf',
        'margen'           => 'map',
        'seto'             => 'tree',
        'fauna_auxiliar'   => 'bug-ant',
        'nido'             => 'home',
        'hotel_insectos'   => 'building-office',
        'charca'           => 'arrows-pointing-in',
        'otro'             => 'sparkles',
    ];

    public const RECORD_TYPE_COLORS = [
        'cubierta_vegetal' => 'green',
        'margen'           => 'amber',
        'seto'             => 'emerald',
        'fauna_auxiliar'   => 'blue',
        'nido'             => 'violet',
        'hotel_insectos'   => 'orange',
        'charca'           => 'cyan',
        'otro'             => 'zinc',
    ];

    // ── Relaciones ───────────────────────────────────────────────────────────

    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    // ── Accessors ────────────────────────────────────────────────────────────

    public function getRecordTypeLabelAttribute(): string
    {
        return self::RECORD_TYPES[$this->record_type] ?? ucfirst($this->record_type);
    }

    public function getRecordTypeIconAttribute(): string
    {
        return self::RECORD_TYPE_ICONS[$this->record_type] ?? 'sparkles';
    }

    public function getRecordTypeColorAttribute(): string
    {
        return self::RECORD_TYPE_COLORS[$this->record_type] ?? 'zinc';
    }
}
