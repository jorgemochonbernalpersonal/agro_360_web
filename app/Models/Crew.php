<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Crew extends Model
{
    protected $fillable = [
        'name',
        'description',
        'viticulturist_id',
        'winery_id',
    ];

    /**
     * Líder de la cuadrilla
     */
    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    /**
     * Bodega contexto
     */
    public function winery(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winery_id');
    }

    /**
     * Miembros de la cuadrilla
     */
    public function members(): HasMany
    {
        return $this->hasMany(CrewMember::class, 'crew_id');
    }
}
