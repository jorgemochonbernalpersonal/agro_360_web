<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SigpacCode extends Model
{
    protected $table = 'sigpac_code';

    protected $fillable = [
        'code',
        'description',
    ];

    public function plots(): BelongsToMany
    {
        return $this->belongsToMany(Plot::class, 'plot_sigpac_code', 'sigpac_code_id', 'plot_id');
    }

    public function multipartCoordinates(): HasMany
    {
        return $this->hasMany(MultipartPlotSigpac::class, 'sigpac_code_id');
    }
}
