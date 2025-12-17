<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class PhytosanitaryTreatment extends Model
{
    protected $fillable = [
        'activity_id',
        'product_id',
        'dose_per_hectare',
        'total_dose',
        'area_treated',
        'application_method',
        'target_pest',
        'wind_speed',
        'humidity',
    ];

    protected $casts = [
        'dose_per_hectare' => 'decimal:3',
        'total_dose' => 'decimal:3',
        'area_treated' => 'decimal:3',
        'wind_speed' => 'decimal:2',
        'humidity' => 'decimal:2',
    ];

    /**
     * Actividad agrícola asociada
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(AgriculturalActivity::class, 'activity_id');
    }

    /**
     * Producto fitosanitario utilizado
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(PhytosanitaryProduct::class, 'product_id');
    }

    /**
     * Calcular fecha de recolección segura (fecha de tratamiento + plazo de seguridad)
     */
    protected function safeHarvestDate(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->product || !$this->product->withdrawal_period_days) {
                    return null;
                }
                return $this->activity->activity_date->addDays($this->product->withdrawal_period_days);
            }
        );
    }
}
