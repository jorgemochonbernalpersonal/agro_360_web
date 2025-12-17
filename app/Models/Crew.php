<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

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

    /**
     * Actividades agrícolas realizadas por esta cuadrilla
     */
    public function activities(): HasMany
    {
        return $this->hasMany(AgriculturalActivity::class, 'crew_id');
    }

    /**
     * Scope para filtrar por viticultor líder
     */
    public function scopeForViticulturist($query, $viticulturistId)
    {
        return $query->where('viticulturist_id', $viticulturistId);
    }

    /**
     * Scope para filtrar por bodega
     */
    public function scopeForWinery($query, $wineryId)
    {
        return $query->where('winery_id', $wineryId);
    }

    /**
     * Obtener el número de miembros
     */
    public function getMembersCountAttribute(): int
    {
        return $this->members()->count();
    }

    /**
     * Obtener el número de actividades
     */
    public function getActivitiesCountAttribute(): int
    {
        return $this->activities()->count();
    }
}
