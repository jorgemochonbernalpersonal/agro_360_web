<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Province extends Model
{
    protected $fillable = [
        'code',
        'name',
        'autonomous_community_id',
    ];

    /**
     * Comunidad autónoma a la que pertenece
     */
    /** @return BelongsTo<AutonomousCommunity, $this> */
    public function autonomousCommunity(): BelongsTo
    {
        return $this->belongsTo(AutonomousCommunity::class, 'autonomous_community_id');
    }

    /**
     * Municipios de esta provincia
     */
    /** @return HasMany<Municipality, $this> */
    public function municipalities(): HasMany
    {
        return $this->hasMany(Municipality::class, 'province_id');
    }

    /**
     * Parcelas en esta provincia
     */
    /** @return HasMany<Plot, $this> */
    public function plots(): HasMany
    {
        return $this->hasMany(Plot::class, 'province_id');
    }
}
