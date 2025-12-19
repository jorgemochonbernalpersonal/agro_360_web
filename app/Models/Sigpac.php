<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sigpac extends Model
{
    protected $table = 'sigpacs';
    
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
     * Parcelas que usan este código SIGPAC
     */
    public function plots(): BelongsToMany
    {
        return $this->belongsToMany(Plot::class, 'multiple_plot_sigpac', 'sigpac_id', 'plot_id')
            ->withPivot('plot_geometry_id')
            ->withTimestamps();
    }
    
    /**
     * Relaciones múltiples plot-sigpac
     */
    public function multiplePlotSigpacs(): HasMany
    {
        return $this->hasMany(MultiplePlotSigpac::class, 'sigpac_id');
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

