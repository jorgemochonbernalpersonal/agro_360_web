<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\TrainingSystem;

class PlotPlanting extends Model
{
    protected $fillable = [
        'plot_id',
        'grape_variety_id',
        'area_planted',
        'planting_year',
        'planting_date',
        'vine_count',
        'density',
        'row_spacing',
        'vine_spacing',
        'rootstock',
        'training_system',
        'training_system_id',
        'irrigated',
        'status',
        'notes',
    ];

    protected $casts = [
        'area_planted' => 'decimal:3',
        'row_spacing' => 'decimal:3',
        'vine_spacing' => 'decimal:3',
        'planting_date' => 'date',
        'irrigated' => 'boolean',
        'planting_year' => 'integer',
        'vine_count' => 'integer',
        'density' => 'integer',
    ];

    /**
     * Relación con la parcela
     */
    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }

    /**
     * Relación con la variedad de uva
     */
    public function grapeVariety(): BelongsTo
    {
        return $this->belongsTo(GrapeVariety::class);
    }

    /**
     * Sistema de conducción (catálogo).
     */
    public function trainingSystem(): BelongsTo
    {
        return $this->belongsTo(TrainingSystem::class, 'training_system_id');
    }

    /**
     * Scope para plantaciones activas
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope para plantaciones con riego
     */
    public function scopeIrrigated($query)
    {
        return $query->where('irrigated', true);
    }
}
