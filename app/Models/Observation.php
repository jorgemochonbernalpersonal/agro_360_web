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
        'affected_area_percentage',
        'threshold_exceeded',
        'follow_up_date',
        'action_taken',
    ];

    protected $casts = [
        'photos' => 'array',
        'affected_area_percentage' => 'decimal:2',
        'threshold_exceeded' => 'boolean',
        'follow_up_date' => 'date',
    ];

    /**
     * Actividad agrícola asociada
     */
    /** @return BelongsTo<AgriculturalActivity, $this> */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(AgriculturalActivity::class, 'activity_id');
    }

    /**
     * Plaga asociada (si la observación es sobre una plaga)
     */
    /** @return BelongsTo<Pest, $this> */
    public function pest(): BelongsTo
    {
        return $this->belongsTo(Pest::class, 'pest_id');
    }
}
