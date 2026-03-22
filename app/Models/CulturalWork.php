<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CulturalWork extends Model
{
    protected $fillable = [
        'activity_id',
        'work_type',
        'pruning_type',
        'productive_buds_per_hectare',
        'residue_management',
        'defoliation_face',
        'topping_height_cm',
        'hours_worked',
        'workers_count',
        'description',
    ];

    protected $casts = [
        'hours_worked' => 'decimal:2',
        'productive_buds_per_hectare' => 'integer',
    ];

    /**
     * Actividad agrícola asociada
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(AgriculturalActivity::class, 'activity_id');
    }
}
