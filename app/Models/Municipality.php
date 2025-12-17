<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Municipality extends Model
{
    protected $fillable = [
        'code',
        'name',
        'province_id',
    ];

    /**
     * Provincia a la que pertenece
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    /**
     * Parcelas en este municipio
     */
    public function plots(): HasMany
    {
        return $this->hasMany(Plot::class, 'municipality_id');
    }
}
