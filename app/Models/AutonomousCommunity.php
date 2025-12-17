<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AutonomousCommunity extends Model
{
    protected $fillable = [
        'code',
        'name',
    ];

    /**
     * Provincias de esta comunidad autónoma
     */
    public function provinces(): HasMany
    {
        return $this->hasMany(Province::class, 'autonomous_community_id');
    }

    /**
     * Parcelas en esta comunidad autónoma
     */
    public function plots(): HasMany
    {
        return $this->hasMany(Plot::class, 'autonomous_community_id');
    }
}
