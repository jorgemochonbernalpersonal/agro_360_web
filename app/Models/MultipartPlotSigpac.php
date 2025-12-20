<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MultipartPlotSigpac extends Model
{
    protected $table = 'multipart_plot_sigpac';
    
    protected $fillable = [
        'plot_id',
        'sigpac_code_id',
        'plot_geometry_id',
    ];
    
    /**
     * Parcela relacionada
     */
    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }
    
    /**
     * Código SIGPAC relacionado
     */
    public function sigpacCode(): BelongsTo
    {
        return $this->belongsTo(SigpacCode::class, 'sigpac_code_id');
    }
    
    /**
     * Geometría de la parcela
     */
    public function plotGeometry(): BelongsTo
    {
        return $this->belongsTo(PlotGeometry::class, 'plot_geometry_id');
    }
}
