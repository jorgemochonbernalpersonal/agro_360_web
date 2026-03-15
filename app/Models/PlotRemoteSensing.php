<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlotRemoteSensing extends Model
{
    use HasFactory;

    protected $table = 'plot_remote_sensing';

    protected $fillable = [
        'plot_id',
        'multipart_plot_sigpac_id',
        'image_date',
        'ndvi_mean',
        'ndvi_min',
        'ndvi_max',
        'ndvi_stddev',
        'ndwi_mean',
        'ndwi_min',
        'ndwi_max',
        'evi_mean',
        'cloud_coverage',
        'image_source',
        'tile_id',
        'tile_path',
        'health_status',
        'health_notes',
        'ndvi_change',
        'trend',
        'metadata',
        // Weather
        'temperature',
        'temperature_min',
        'temperature_max',
        'precipitation',
        'humidity',
        'wind_speed',
        // Soil
        'soil_moisture',
        'soil_temperature',
        // Solar
        'solar_radiation',
        'et0',
        'sunshine_hours',
        'water_stress_status',
        // Advanced metrics
        'lai',
        'gndvi',
        'ndre',
        'chlorophyll_content',
        'maturity_index',
        'predicted_brix',
        'days_to_harvest',
        'anomaly_detected',
        'anomaly_severity',
        'anomaly_type',
        // LST and area data
        'lst_day',
        'lst_night',
        'lst_diff',
        'cwsi',
        'area_statistics',
        'data_source',
        'satellite',
        'pixel_reliability',
        // Spectral bands
        'red_band',
        'nir_band',
        'green_band',
        'blue_band',
        'msr',
        'ci_green',
        'arvi',
        // Official LAI
        'fpar',
        'lai_quality',
        // SMAP Soil Moisture
        'soil_moisture_surface_smap',
        'soil_moisture_rootzone_smap',
        // NASA ET
        'et_nasa',
        'pet_nasa',
    ];

    protected $casts = [
        'image_date' => 'date',
        'ndvi_mean' => 'decimal:4',
        'ndvi_min' => 'decimal:4',
        'ndvi_max' => 'decimal:4',
        'ndvi_stddev' => 'decimal:4',
        'ndwi_mean' => 'decimal:4',
        'ndwi_min' => 'decimal:4',
        'ndwi_max' => 'decimal:4',
        'evi_mean' => 'decimal:4',
        'ndvi_change' => 'decimal:4',
        'cloud_coverage' => 'integer',
        'metadata' => 'array',
        // Weather casts
        'temperature' => 'decimal:2',
        'temperature_min' => 'decimal:2',
        'temperature_max' => 'decimal:2',
        'precipitation' => 'decimal:2',
        'humidity' => 'decimal:2',
        'wind_speed' => 'decimal:2',
        'soil_moisture' => 'decimal:2',
        'soil_temperature' => 'decimal:2',
        'solar_radiation' => 'decimal:2',
        'et0' => 'decimal:2',
        'sunshine_hours' => 'decimal:1',
        // Advanced metrics casts
        'lai' => 'decimal:2',
        'gndvi' => 'decimal:4',
        'ndre' => 'decimal:4',
        'chlorophyll_content' => 'decimal:2',
        'maturity_index' => 'decimal:2',
        'predicted_brix' => 'decimal:2',
        'days_to_harvest' => 'integer',
        'anomaly_detected' => 'boolean',
        // LST and area casts
        'lst_day' => 'decimal:2',
        'lst_night' => 'decimal:2',
        'lst_diff' => 'decimal:2',
        'cwsi' => 'decimal:3',
        'area_statistics' => 'array',
        'pixel_reliability' => 'integer',
        // Spectral bands casts
        'red_band' => 'decimal:4',
        'nir_band' => 'decimal:4',
        'green_band' => 'decimal:4',
        'blue_band' => 'decimal:4',
        'msr' => 'decimal:4',
        'ci_green' => 'decimal:4',
        'arvi' => 'decimal:4',
        // Official LAI casts
        'fpar' => 'decimal:3',
        'lai_quality' => 'integer',
        // SMAP casts
        'soil_moisture_surface_smap' => 'decimal:2',
        'soil_moisture_rootzone_smap' => 'decimal:2',
        // NASA ET casts
        'et_nasa' => 'decimal:2',
        'pet_nasa' => 'decimal:2',
    ];

    /**
     * Relación con la parcela
     */
    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }

    /**
     * Obtener el color del badge según el estado de salud
     */
    public function getHealthColorAttribute(): string
    {
        return match ($this->health_status) {
            'excellent' => 'green',
            'good' => 'emerald',
            'moderate' => 'yellow',
            'poor' => 'orange',
            'critical' => 'red',
            default => 'gray',
        };
    }

    /**
     * Obtener el emoji según el estado de salud
     */
    public function getHealthEmojiAttribute(): string
    {
        return match ($this->health_status) {
            'excellent' => '🌿',
            'good' => '🌱',
            'moderate' => '🌾',
            'poor' => '🍂',
            'critical' => '🥀',
            default => '❓',
        };
    }

    /**
     * Obtener el texto del estado de salud en español
     */
    public function getHealthTextAttribute(): string
    {
        return match ($this->health_status) {
            'excellent' => 'Excelente',
            'good' => 'Bueno',
            'moderate' => 'Moderado',
            'poor' => 'Bajo',
            'critical' => 'Crítico',
            default => 'Sin datos',
        };
    }

    /**
     * Returns true when cloud coverage exceeds the quality threshold (>60 %).
     * Above this value NDVI readings are considered unreliable.
     */
    public function hasHighCloudCoverage(): bool
    {
        return ($this->cloud_coverage ?? 0) > 60;
    }

    /**
     * Human-readable cloud quality label in Spanish.
     */
    public function getCloudQualityLabelAttribute(): string
    {
        $coverage = $this->cloud_coverage ?? 0;

        return match (true) {
            $coverage <= 20 => 'Excelente',
            $coverage <= 40 => 'Buena',
            $coverage <= 60 => 'Aceptable',
            default         => 'Baja calidad',
        };
    }

    /**
     * Obtener el icono de tendencia
     */
    public function getTrendIconAttribute(): string
    {
        return match ($this->trend) {
            'increasing' => '↑',
            'stable' => '→',
            'decreasing' => '↓',
            default => '-',
        };
    }

    /**
     * Obtener el color de tendencia
     */
    public function getTrendColorAttribute(): string
    {
        return match ($this->trend) {
            'increasing' => 'text-green-600',
            'stable' => 'text-gray-600',
            'decreasing' => 'text-red-600',
            default => 'text-gray-400',
        };
    }

    /**
     * Obtener el porcentaje NDVI para visualización (0-100)
     */
    public function getNdviPercentageAttribute(): int
    {
        if ($this->ndvi_mean === null) {
            return 0;
        }
        
        // NDVI va de -1 a 1, normalizamos a 0-100
        // Valores típicos de vegetación saludable: 0.2 a 0.9
        $normalized = (($this->ndvi_mean + 1) / 2) * 100;
        return (int) min(100, max(0, $normalized));
    }

    /**
     * Scope para obtener el último registro por parcela
     */
    public function scopeLatestPerPlot($query)
    {
        return $query->whereIn('id', function ($subQuery) {
            $subQuery->selectRaw('MAX(id)')
                ->from('plot_remote_sensing')
                ->groupBy('plot_id');
        });
    }

    /**
     * Scope para filtrar por rango de fechas
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('image_date', [$startDate, $endDate]);
    }

    /**
     * Scope para parcelas con problemas
     */
    public function scopeWithIssues($query)
    {
        return $query->whereIn('health_status', ['poor', 'critical']);
    }

    /**
     * Calcular el estado de salud basado en el NDVI
     */
    public static function calculateHealthStatus(float $ndvi): string
    {
        return match (true) {
            $ndvi >= 0.7 => 'excellent',
            $ndvi >= 0.5 => 'good',
            $ndvi >= 0.3 => 'moderate',
            $ndvi >= 0.15 => 'poor',
            default => 'critical',
        };
    }

    /**
     * Calcular la tendencia comparando con el periodo anterior
     */
    public static function calculateTrend(float $current, float $previous): string
    {
        $change = $current - $previous;
        $threshold = 0.05; // 5% de cambio mínimo para considerar tendencia
        
        return match (true) {
            $change > $threshold => 'increasing',
            $change < -$threshold => 'decreasing',
            default => 'stable',
        };
    }
}
