<?php

namespace App\Services\RemoteSensing;

use Illuminate\Support\Facades\Log;

/**
 * Servicio para calcular índices vegetativos a partir de bandas espectrales
 */
class NdviCalculator
{
    /**
     * Calcular NDVI (Normalized Difference Vegetation Index)
     * NDVI = (NIR - RED) / (NIR + RED)
     * 
     * @param float $nir Valor de banda NIR (B08 en Sentinel-2)
     * @param float $red Valor de banda RED (B04 en Sentinel-2)
     * @return float Valor NDVI entre -1 y 1
     */
    public function calculateNdvi(float $nir, float $red): float
    {
        $sum = $nir + $red;
        
        if ($sum === 0.0) {
            return 0.0;
        }
        
        return ($nir - $red) / $sum;
    }

    /**
     * Calcular NDWI (Normalized Difference Water Index)
     * NDWI = (GREEN - NIR) / (GREEN + NIR)
     * 
     * @param float $green Valor de banda GREEN (B03 en Sentinel-2)
     * @param float $nir Valor de banda NIR (B08 en Sentinel-2)
     * @return float Valor NDWI entre -1 y 1
     */
    public function calculateNdwi(float $green, float $nir): float
    {
        $sum = $green + $nir;
        
        if ($sum === 0.0) {
            return 0.0;
        }
        
        return ($green - $nir) / $sum;
    }

    /**
     * Calcular EVI (Enhanced Vegetation Index)
     * EVI = 2.5 * (NIR - RED) / (NIR + 6*RED - 7.5*BLUE + 1)
     * 
     * @param float $nir Valor de banda NIR (B08 en Sentinel-2)
     * @param float $red Valor de banda RED (B04 en Sentinel-2)
     * @param float $blue Valor de banda BLUE (B02 en Sentinel-2)
     * @return float Valor EVI
     */
    public function calculateEvi(float $nir, float $red, float $blue): float
    {
        $denominator = $nir + (6.0 * $red) - (7.5 * $blue) + 1.0;
        
        if ($denominator === 0.0) {
            return 0.0;
        }
        
        return 2.5 * (($nir - $red) / $denominator);
    }

    /**
     * Calcular SAVI (Soil Adjusted Vegetation Index)
     * SAVI = ((NIR - RED) / (NIR + RED + L)) * (1 + L)
     * L = 0.5 (factor de corrección del suelo)
     * 
     * @param float $nir Valor de banda NIR
     * @param float $red Valor de banda RED
     * @param float $l Factor de corrección del suelo (default 0.5)
     * @return float Valor SAVI
     */
    public function calculateSavi(float $nir, float $red, float $l = 0.5): float
    {
        $sum = $nir + $red + $l;
        
        if ($sum === 0.0) {
            return 0.0;
        }
        
        return (($nir - $red) / $sum) * (1.0 + $l);
    }

    /**
     * Calcular NDMI (Normalized Difference Moisture Index)
     * NDMI = (NIR - SWIR) / (NIR + SWIR)
     * 
     * @param float $nir Valor de banda NIR (B08 en Sentinel-2)
     * @param float $swir Valor de banda SWIR (B11 en Sentinel-2)
     * @return float Valor NDMI entre -1 y 1
     */
    public function calculateNdmi(float $nir, float $swir): float
    {
        $sum = $nir + $swir;
        
        if ($sum === 0.0) {
            return 0.0;
        }
        
        return ($nir - $swir) / $sum;
    }

    /**
     * Calcular todos los índices a partir de las bandas espectrales
     * 
     * @param array $bands Array con las bandas: ['B02' => blue, 'B03' => green, 'B04' => red, 'B08' => nir, 'B11' => swir]
     * @return array Índices calculados
     */
    public function calculateAllIndices(array $bands): array
    {
        $nir = $bands['B08'] ?? 0;
        $red = $bands['B04'] ?? 0;
        $green = $bands['B03'] ?? 0;
        $blue = $bands['B02'] ?? 0;
        $swir = $bands['B11'] ?? 0;

        return [
            'ndvi' => $this->calculateNdvi($nir, $red),
            'ndwi' => $this->calculateNdwi($green, $nir),
            'evi' => $this->calculateEvi($nir, $red, $blue),
            'savi' => $this->calculateSavi($nir, $red),
            'ndmi' => $swir > 0 ? $this->calculateNdmi($nir, $swir) : null,
        ];
    }

    /**
     * Clasificar el estado de la vegetación basado en NDVI
     * 
     * @param float $ndvi Valor NDVI
     * @return array Clasificación con estado, color y descripción
     */
    public function classifyVegetationHealth(float $ndvi): array
    {
        return match (true) {
            $ndvi >= 0.7 => [
                'status' => 'excellent',
                'color' => '#22c55e',
                'label' => 'Excelente',
                'description' => 'Vegetación muy densa y vigorosa',
            ],
            $ndvi >= 0.5 => [
                'status' => 'good',
                'color' => '#84cc16',
                'label' => 'Bueno',
                'description' => 'Vegetación saludable',
            ],
            $ndvi >= 0.3 => [
                'status' => 'moderate',
                'color' => '#eab308',
                'label' => 'Moderado',
                'description' => 'Vegetación moderada o en crecimiento',
            ],
            $ndvi >= 0.15 => [
                'status' => 'poor',
                'color' => '#f97316',
                'label' => 'Bajo',
                'description' => 'Vegetación escasa o con estrés',
            ],
            $ndvi >= 0 => [
                'status' => 'critical',
                'color' => '#ef4444',
                'label' => 'Crítico',
                'description' => 'Sin vegetación o vegetación muy dañada',
            ],
            default => [
                'status' => 'water_or_snow',
                'color' => '#3b82f6',
                'label' => 'Agua/Nieve',
                'description' => 'Superficie de agua o nieve',
            ],
        };
    }

    /**
     * Generar paleta de colores NDVI para visualización en mapa
     * 
     * @return array Paleta de colores con rangos de NDVI
     */
    public function getNdviColorPalette(): array
    {
        return [
            ['min' => -1.0, 'max' => 0.0, 'color' => '#0000FF', 'label' => 'Agua'],
            ['min' => 0.0, 'max' => 0.1, 'color' => '#A52A2A', 'label' => 'Suelo desnudo'],
            ['min' => 0.1, 'max' => 0.2, 'color' => '#D2691E', 'label' => 'Vegetación muy escasa'],
            ['min' => 0.2, 'max' => 0.3, 'color' => '#FFD700', 'label' => 'Vegetación escasa'],
            ['min' => 0.3, 'max' => 0.4, 'color' => '#ADFF2F', 'label' => 'Vegetación moderada'],
            ['min' => 0.4, 'max' => 0.5, 'color' => '#7CFC00', 'label' => 'Vegetación media'],
            ['min' => 0.5, 'max' => 0.6, 'color' => '#32CD32', 'label' => 'Vegetación densa'],
            ['min' => 0.6, 'max' => 0.7, 'color' => '#228B22', 'label' => 'Vegetación muy densa'],
            ['min' => 0.7, 'max' => 0.8, 'color' => '#006400', 'label' => 'Vegetación exuberante'],
            ['min' => 0.8, 'max' => 1.0, 'color' => '#004000', 'label' => 'Máximo vigor'],
        ];
    }

    /**
     * Obtener color para un valor NDVI específico
     * 
     * @param float $ndvi Valor NDVI
     * @return string Color hexadecimal
     */
    public function getNdviColor(float $ndvi): string
    {
        foreach ($this->getNdviColorPalette() as $range) {
            if ($ndvi >= $range['min'] && $ndvi < $range['max']) {
                return $range['color'];
            }
        }
        
        return '#808080'; // Gris por defecto
    }

    /**
     * Calcular estadísticas de un conjunto de valores NDVI
     * 
     * @param array $values Array de valores NDVI
     * @return array Estadísticas: mean, min, max, stddev
     */
    public function calculateStatistics(array $values): array
    {
        if (empty($values)) {
            return [
                'mean' => null,
                'min' => null,
                'max' => null,
                'stddev' => null,
            ];
        }

        $count = count($values);
        $mean = array_sum($values) / $count;
        $min = min($values);
        $max = max($values);

        // Calcular desviación estándar
        $variance = 0;
        foreach ($values as $value) {
            $variance += pow($value - $mean, 2);
        }
        $stddev = sqrt($variance / $count);

        return [
            'mean' => round($mean, 4),
            'min' => round($min, 4),
            'max' => round($max, 4),
            'stddev' => round($stddev, 4),
        ];
    }
}
