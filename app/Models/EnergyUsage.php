<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnergyUsage extends Model
{
    use HasFactory;

    const ENERGY_TYPES = [
        'diesel' => 'Gasóleo agrícola',
        'gasoline' => 'Gasolina',
        'electricity' => 'Electricidad',
        'lpg' => 'GLP (Gas licuado del petróleo)',
        'natural_gas' => 'Gas natural',
        'water_pump' => 'Bombeo de agua',
        'other' => 'Otro',
    ];

    const UNITS = [
        'liters' => 'Litros (L)',
        'kwh' => 'kWh',
        'm3' => 'm³',
        'kg' => 'kg',
    ];

    // kg CO₂ equivalente por unidad
    const CO2_FACTORS = [
        'diesel' => 2.640,
        'gasoline' => 2.392,
        'electricity' => 0.250,
        'lpg' => 1.512,
        'natural_gas' => 2.020,
        'water_pump' => 0.250,
        'other' => 0.000,
    ];

    protected $fillable = [
        'campaign_id',
        'viticulturist_id',
        'activity_id',
        'machinery_id',
        'date',
        'energy_type',
        'unit',
        'quantity',
        'cost_per_unit',
        'total_cost',
        'co2_kg_equivalent',
        'usage_description',
        'notes',
        'active',
    ];

    protected $casts = [
        'date' => 'date',
        'quantity' => 'decimal:3',
        'cost_per_unit' => 'decimal:4',
        'total_cost' => 'decimal:2',
        'co2_kg_equivalent' => 'decimal:3',
        'active' => 'boolean',
    ];

    public static function energyTypeOptions(): array
    {
        return array_map(fn ($v) => __($v), static::ENERGY_TYPES);
    }

    public static function unitOptions(): array
    {
        return array_map(fn ($v) => __($v), static::UNITS);
    }

    /** @return BelongsTo<Campaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /** @return BelongsTo<User, $this> */
    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    /** @return BelongsTo<AgriculturalActivity, $this> */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(AgriculturalActivity::class, 'activity_id');
    }

    /** @return BelongsTo<Machinery, $this> */
    public function machinery(): BelongsTo
    {
        return $this->belongsTo(Machinery::class);
    }

    public function getEnergyTypeLabelAttribute(): string
    {
        return __(self::ENERGY_TYPES[$this->energy_type]);
    }

    public function getUnitLabelAttribute(): string
    {
        return __(self::UNITS[$this->unit]);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeForViticulturist($query, int $viticulturistId)
    {
        return $query->where('viticulturist_id', $viticulturistId);
    }

    public function scopeForCampaign($query, int $campaignId)
    {
        return $query->where('campaign_id', $campaignId);
    }

    protected static function booted(): void
    {
        static::saving(function (EnergyUsage $usage) {
            // Auto-calcular CO₂ equivalente
            $factor = self::CO2_FACTORS[$usage->energy_type];
            $usage->co2_kg_equivalent = round($usage->quantity * $factor, 3);

            // Auto-calcular coste total
            if ($usage->cost_per_unit && $usage->quantity) {
                $usage->total_cost = round($usage->quantity * $usage->cost_per_unit, 2);
            }
        });
    }
}
