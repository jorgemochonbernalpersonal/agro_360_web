<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fertilization extends Model
{
    protected $fillable = [
        'activity_id',
        'fertilizer_type',
        'fertilizer_name',
        'quantity',
        'npk_ratio',
        'application_method',
        'area_applied',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'area_applied' => 'decimal:3',
    ];

    /**
     * Actividad agrícola asociada
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(AgriculturalActivity::class, 'activity_id');
    }
}
