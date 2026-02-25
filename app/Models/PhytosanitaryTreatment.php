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
        'pest_id',
        'dose_per_hectare',
        'total_dose',
        'area_treated',
        'application_method',
        'wind_speed',
        'humidity',
        // Campos PAC obligatorios
        'treatment_justification',
        'applicator_ropo_number',
        'reentry_period_days',
        'spray_volume',
        // Caldo y asesoramiento
        'water_volume_liters_ha',
        'under_advisory',
        'advisory_action_date',
        // Flags IPM — Gestión Integrada de Plagas (RD 1311/2012)
        'prior_non_chemical_methods',
        'plague_monitoring',
        'manual_mechanical_control',
        'biological_control',
        'cultural_preventions',
    ];

    protected $casts = [
        'dose_per_hectare'           => 'decimal:3',
        'total_dose'                 => 'decimal:3',
        'area_treated'               => 'decimal:3',
        'wind_speed'                 => 'decimal:2',
        'humidity'                   => 'decimal:2',
        'spray_volume'               => 'decimal:2',
        'water_volume_liters_ha'     => 'decimal:2',
        'reentry_period_days'        => 'integer',
        'advisory_action_date'       => 'date',
        'under_advisory'             => 'boolean',
        'prior_non_chemical_methods' => 'boolean',
        'plague_monitoring'          => 'boolean',
        'manual_mechanical_control'  => 'boolean',
        'biological_control'         => 'boolean',
        'cultural_preventions'       => 'boolean',
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
     * Plaga objetivo del tratamiento
     */
    public function pest(): BelongsTo
    {
        return $this->belongsTo(Pest::class, 'pest_id');
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
