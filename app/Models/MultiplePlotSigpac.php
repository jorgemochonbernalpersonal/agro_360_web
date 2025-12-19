<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MultiplePlotSigpac extends Model
{
    protected $table = 'multiple_plot_sigpac';
    
    protected $fillable = [
        'plot_id',
        'sigpac_id',
        'plot_geometry_id',
    ];
    
    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }
    
    public function sigpac(): BelongsTo
    {
        return $this->belongsTo(Sigpac::class, 'sigpac_id');
    }
    
    public function plotGeometry(): BelongsTo
    {
        return $this->belongsTo(PlotGeometry::class, 'plot_geometry_id');
    }
}

