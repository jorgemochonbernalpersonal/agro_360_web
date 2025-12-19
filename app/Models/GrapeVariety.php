<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrapeVariety extends Model
{
    protected $fillable = [
        'name',
        'code',
        'color',
        'description',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Plantaciones de esta variedad
     */
    public function plantings(): HasMany
    {
        return $this->hasMany(PlotPlanting::class);
    }

    /**
     * Scope para variedades activas
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope por color
     */
    public function scopeByColor($query, $color)
    {
        return $query->where('color', $color);
    }
}
