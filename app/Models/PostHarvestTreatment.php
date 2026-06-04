<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostHarvestTreatment extends Model
{
    use HasFactory;

    const APPLICATION_TYPES = [
        'copper_treatment' => 'Tratamiento con cobre',
        'sulfur_treatment' => 'Tratamiento con azufre',
        'wound_sealing' => 'Sellado de heridas de poda',
        'foliar_application' => 'Aplicación foliar',
        'trunk_treatment' => 'Tratamiento de troncos',
        'other' => 'Otro',
    ];

    protected $fillable = [
        'activity_id',
        'product_id',
        'application_type',
        'treated_area_ha',
        'dose_per_hectare',
        'dose_unit',
        'water_volume_liters',
        'reentry_interval_hours',
        'notes',
    ];

    protected $casts = [
        'treated_area_ha' => 'decimal:4',
        'dose_per_hectare' => 'decimal:4',
        'water_volume_liters' => 'decimal:2',
        'reentry_interval_hours' => 'integer',
    ];

    public static function applicationTypeOptions(): array
    {
        return array_map(fn ($v) => __($v), static::APPLICATION_TYPES);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(AgriculturalActivity::class, 'activity_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(PhytosanitaryProduct::class, 'product_id');
    }

    public function getApplicationTypeLabelAttribute(): string
    {
        return __(self::APPLICATION_TYPES[$this->application_type] ?? $this->application_type);
    }
}
