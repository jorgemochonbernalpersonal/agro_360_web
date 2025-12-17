<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MultipartPlotSigpac extends Model
{
    protected $table = 'multipart_plot_sigpac';

    protected $fillable = [
        'plot_id',
        'coordinates',
        'sigpac_code_id',
    ];

    /**
     * Parcela a la que pertenecen estas coordenadas
     */
    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class, 'plot_id');
    }

    /**
     * Código SIGPAC asociado (opcional)
     */
    public function sigpacCode(): BelongsTo
    {
        return $this->belongsTo(SigpacCode::class, 'sigpac_code_id');
    }
}
