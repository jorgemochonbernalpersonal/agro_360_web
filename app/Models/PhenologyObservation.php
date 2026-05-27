<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhenologyObservation extends Model
{
    protected $fillable = [
        'plot_planting_id',
        'campaign_id',
        'viticulturist_id',
        'event',
        'obs_date',
        'source',
        'confidence',
        'degree_days_accumulated',
        'bbch_code',
        'notes',
        'active',
    ];

    protected $casts = [
        'obs_date'                => 'date',
        'confidence'              => 'integer',
        'degree_days_accumulated' => 'decimal:2',
        'bbch_code'               => 'integer',
        'active'                  => 'boolean',
    ];

    /** Orden cronológico natural de los estadios */
    public const EVENTS = [
        'budbreak'     => 'Desborre',
        'shoot_growth' => 'Crecimiento del pámpano',
        'flowering'    => 'Floración',
        'fruit_set'    => 'Cuajado',
        'veraison'     => 'Envero',
        'pre_harvest'  => 'Pre-vendimia',
        'harvest'      => 'Vendimia',
    ];

    public const SOURCES = [
        'manual' => 'Observación en campo',
        'sensor' => 'Sensor IoT',
        'model'  => 'Modelo predictivo',
        'auto'   => 'Detección automática',
    ];

    /** Códigos BBCH estándar para la vid */
    public const BBCH_CODES = [
        'budbreak'     => 7,
        'shoot_growth' => 16,
        'flowering'    => 65,
        'fruit_set'    => 71,
        'veraison'     => 81,
        'pre_harvest'  => 85,
        'harvest'      => 89,
    ];

    public function plotPlanting(): BelongsTo
    {
        return $this->belongsTo(PlotPlanting::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    public function getEventLabelAttribute(): string
    {
        return self::EVENTS[$this->event] ?? $this->event;
    }

    public function getSourceLabelAttribute(): string
    {
        return self::SOURCES[$this->source] ?? $this->source;
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Devuelve las observaciones ordenadas por el orden natural fenológico
     */
    public function scopeChronological($query)
    {
        $order = array_keys(self::EVENTS);
        return $query->orderByRaw('FIELD(event, ' . implode(',', array_map(fn($e) => "'{$e}'", $order)) . ')');
    }
}
