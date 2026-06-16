<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhytosanitaryTreatment extends Model
{
    protected $fillable = [
        'activity_id',
        'field_applicator_id',
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
        // Caldo, zona tampón y asesoramiento
        'water_volume_liters_ha',
        'buffer_zone_respected',
        'distance_to_water_m',
        'under_advisory',
        'advisory_recommendation_date',
        // Flags IPM — Gestión Integrada de Plagas (RD 1311/2012)
        'prior_non_chemical_methods',
        'plague_monitoring',
        'manual_mechanical_control',
        'biological_control',
        'cultural_preventions',
    ];

    protected $casts = [
        'dose_per_hectare' => 'decimal:3',
        'total_dose' => 'decimal:3',
        'area_treated' => 'decimal:3',
        'wind_speed' => 'decimal:2',
        'humidity' => 'decimal:2',
        'spray_volume' => 'decimal:2',
        'water_volume_liters_ha' => 'decimal:2',
        'distance_to_water_m' => 'decimal:2',
        'reentry_period_days' => 'integer',
        'advisory_recommendation_date' => 'date',
        'under_advisory' => 'boolean',
        'buffer_zone_respected' => 'boolean',
        'prior_non_chemical_methods' => 'boolean',
        'plague_monitoring' => 'boolean',
        'manual_mechanical_control' => 'boolean',
        'biological_control' => 'boolean',
        'cultural_preventions' => 'boolean',
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
     * Producto fitosanitario utilizado
     */
    /** @return BelongsTo<PhytosanitaryProduct, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(PhytosanitaryProduct::class, 'product_id');
    }

    /**
     * Plaga objetivo del tratamiento
     */
    /** @return BelongsTo<Pest, $this> */
    public function pest(): BelongsTo
    {
        return $this->belongsTo(Pest::class, 'pest_id');
    }

    /**
     * Aplicador ROPO vinculado (FieldApplicator registrado)
     */
    /** @return BelongsTo<FieldApplicator, $this> */
    public function fieldApplicator(): BelongsTo
    {
        return $this->belongsTo(FieldApplicator::class, 'field_applicator_id');
    }

    /**
     * Calcular fecha de recolección segura (fecha de tratamiento + plazo de seguridad)
     */
    protected function safeHarvestDate(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->product || ! $this->product->withdrawal_period_days) {
                    return null;
                }
                $activityDate = $this->relationLoaded('activity')
                    ? $this->activity?->activity_date
                    : $this->activity()->value('activity_date');

                if (! $activityDate) {
                    return null;
                }

                return \Carbon\Carbon::parse($activityDate)->addDays($this->product->withdrawal_period_days);
            }
        );
    }
}
