<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Irrigation extends Model
{
    protected $fillable = [
        'activity_id',
        'water_volume',
        'irrigation_method',
        'duration_minutes',
        'soil_moisture_before',
        'soil_moisture_after',
        // Campos PAC
        'water_source',
        'water_concession',
        'flow_rate',
    ];

    protected $casts = [
        'water_volume' => 'decimal:3',
        'soil_moisture_before' => 'decimal:2',
        'soil_moisture_after' => 'decimal:2',
        'flow_rate' => 'decimal:2',
    ];

    /**
     * Actividad agrícola asociada
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(AgriculturalActivity::class, 'activity_id');
    }
}
