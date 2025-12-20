<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class SigpacCode extends Model
{
    protected $table = 'sigpac_code';

    protected $fillable = [
        'code_polygon',
        'code_plot',
        'code_enclosure',
        'code_aggregate',
        'code_province',
        'code_zone',
        'code',
        'code_municipality',
    ];

    /**
     * Parcelas que usan este código SIGPAC (relación antigua via plot_sigpac_code)
     */
    public function plotsOld(): BelongsToMany
    {
        return $this->belongsToMany(Plot::class, 'plot_sigpac_code', 'sigpac_code_id', 'plot_id');
    }

    /**
     * Parcelas que usan este código SIGPAC (nueva estructura via multiple_plot_sigpac)
     */
    public function plots(): BelongsToMany
    {
        return $this->belongsToMany(Plot::class, 'multipart_plot_sigpac', 'sigpac_code_id', 'plot_id')
            ->withPivot('plot_geometry_id')
            ->withTimestamps();
    }

    /**
     * Relaciones múltiples plot-sigpac
     */
    public function multiplePlotSigpacs(): HasMany
    {
        return $this->hasMany(MultipartPlotSigpac::class, 'sigpac_code_id');
    }

    /**
     * Coordenadas multiparte (estructura antigua)
     */
    public function multipartCoordinates(): HasMany
    {
        return $this->hasMany(MultipartPlotSigpac::class, 'sigpac_code_id');
    }

    /**
     * Obtener código completo formateado
     */
    public function getFullCodeAttribute(): string
    {
        if ($this->code) {
            return $this->code;
        }
        
        return trim(
            ($this->code_polygon ?? '') . 
            ($this->code_plot ?? '') . 
            ($this->code_enclosure ?? '') . 
            ($this->code_aggregate ?? '')
        ) ?: 'N/A';
    }
}
