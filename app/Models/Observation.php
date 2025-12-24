<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Observation extends Model
{
    protected $fillable = [
        'activity_id',
        'pest_id',
        'observation_type',
        'description',
        'photos',
        'severity',
        'action_taken',
    ];

    protected $casts = [
        'photos' => 'array',
    ];

    /**
     * Actividad agrícola asociada
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(AgriculturalActivity::class, 'activity_id');
    }

    /**
     * Plaga asociada (si la observación es sobre una plaga)
     */
    public function pest(): BelongsTo
    {
        return $this->belongsTo(Pest::class, 'pest_id');
    }
}
