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
        // Campos PAC (Nutrición)
        'nitrogen_uf',
        'phosphorus_uf',
        'potassium_uf',
        'manure_type',
        'burial_date',
        'emission_reduction_method',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'area_applied' => 'decimal:3',
        'nitrogen_uf' => 'decimal:3',
        'phosphorus_uf' => 'decimal:3',
        'potassium_uf' => 'decimal:3',
        'burial_date' => 'date',
    ];

    /**
     * Actividad agrícola asociada
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(AgriculturalActivity::class, 'activity_id');
    }
}
