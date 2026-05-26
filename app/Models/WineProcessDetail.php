<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WineProcessDetail extends Model
{
    const PROCESS_TYPES = [
        'destemming_crushing' => __('Despalillado / Estrujado'),
        'pressing'            => __('Prensado'),
        'settling'            => __('Desfangado'),
        'fermentation'        => __('Fermentación alcohólica'),
        'maceration'          => __('Maceración'),
        'malolactic'          => __('Fermentación maloláctica'),
        'aging'               => __('Crianza'),
        'racking'             => __('Trasiego'),
        'blending'            => __('Mezcla / Coupage'),
        'fining'              => __('Clarificación / Colaje'),
        'filtration'          => __('Filtrado'),
        'cold_stabilization'  => __('Estabilización tartárica'),
        'bottling'            => __('Embotellado'),
        'enrichment'          => __('Enriquecimiento (chaptalización)'),
        'concentration'       => __('Concentración de mosto'),
        'acidification'       => __('Acidificación'),
        'desacidification'    => __('Desacidificación'),
        'sulfitation'         => __('Sulfitado'),
        'other'               => __('Otro'),
    ];

    protected $fillable = [
        'wine_id',
        'container_id',
        'oenologist_id',
        'process_type',
        'start_date',
        'end_date',
        'quantity',
        'unit_of_measurement_id',
        'observations',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'quantity'   => 'decimal:3',
    ];

    public function wine(): BelongsTo
    {
        return $this->belongsTo(Wine::class);
    }

    public function container(): BelongsTo
    {
        return $this->belongsTo(Container::class);
    }

    public function unitOfMeasurement(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasurement::class);
    }

    public function oenologist(): BelongsTo
    {
        return $this->belongsTo(Oenologist::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Contenedores adicionales involucrados en este proceso (blending, trasvase, etc.)
     */
    public function containers(): BelongsToMany
    {
        return $this->belongsToMany(Container::class, 'wine_process_detail_containers')
            ->withPivot(['quantity', 'unit_of_measurement_id'])
            ->withTimestamps();
    }

    public function getProcessTypeLabelAttribute(): string
    {
        return self::PROCESS_TYPES[$this->process_type] ?? $this->process_type;
    }

    public function isCompleted(): bool
    {
        return $this->end_date !== null;
    }
}
