<?php

namespace App\Services\RemoteSensing;

use App\Models\Plot;
use App\Models\PlotRemoteSensing;
use App\Repositories\PlotRemoteSensingRepository;
use App\Services\RemoteSensing\Calculators\ChlorophyllCalculator;
use App\Services\RemoteSensing\Calculators\LAICalculator;
use App\Services\RemoteSensing\Calculators\MaturityCalculator;
use App\Services\RemoteSensing\Calculators\PhenologyCalculator;
use App\Services\RemoteSensing\Detectors\AnomalyDetector;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Advanced Remote Sensing Analysis Service
 *
 * Aggregates all advanced functionalities:
 * - LAI (Leaf Area Index) for yield prediction
 * - GNDVI/Chlorophyll for nitrogen status
 * - Maturity Index for harvest prediction
 * - Anomaly Detection for early disease/pest detection
 */
class AdvancedAnalysisService
{
    public function __construct(
        private PlotRemoteSensingRepository $repository,
        private LAICalculator $laiCalculator,
        private ChlorophyllCalculator $chlorophyllCalculator,
        private MaturityCalculator $maturityCalculator,
        private PhenologyCalculator $phenologyCalculator,
        private AnomalyDetector $anomalyDetector,
        private RemoteSensingCacheService $cacheService
    ) {}

    /**
     * Perform complete advanced analysis on plot
     *
     * @param bool $forceRefresh Skip cache
     *
     * @return array Complete analysis results
     */
    public function analyzeAdvanced(Plot $plot, bool $forceRefresh = false): array
    {
        // Check cache first
        if (! $forceRefresh) {
            $cached = $this->cacheService->get(
                sprintf('advanced_analysis_plot_%d', $plot->id)
            );

            if ($cached) {
                return $cached;
            }
        }

        try {
            // Get current and historical data
            $current = $this->repository->getLatestForPlot($plot);

            if (! $current) {
                return $this->getEmptyAnalysis('No hay datos disponibles');
            }

            $historical = $this->repository->getHistoricalForPlot(
                $plot,
                90  // días
            );

            // Perform all analyses
            $analysis = [
                'plot_id' => $plot->id,
                'plot_name' => $plot->name,
                'analyzed_at' => now()->toIso8601String(),
                'data_date' => $current->image_date->toIso8601String(),

                // 1. LAI Analysis
                'lai' => $this->analyzeLAI($plot, $current, $historical),

                // 2. Chlorophyll/Nitrogen Analysis
                'chlorophyll' => $this->analyzeChlorophyll($current),

                // 3. Maturity Analysis
                'maturity' => $this->analyzeMaturity($plot, $current, $historical),

                // 4. Anomaly Detection
                'anomalies' => $this->detectAnomalies($current, $historical),

                // Summary and recommendations
                'summary' => [],
                'priority_actions' => [],
            ];

            // Generate summary
            $analysis['summary'] = $this->generateSummary($analysis);
            $analysis['priority_actions'] = $this->generatePriorityActions($analysis);

            // Update database with calculated metrics
            $this->updateAdvancedMetrics($current, $analysis);

            // Cache results (15 minutes)
            $this->cacheService->put(
                sprintf('advanced_analysis_plot_%d', $plot->id),
                $analysis,
                900
            );

            return $analysis;

        } catch (\Exception $e) {
            Log::error('Advanced analysis failed', [
                'plot_id' => $plot->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->getEmptyAnalysis('Error en el análisis: '.$e->getMessage());
        }
    }

    /**
     * Clear cache for a plot
     */
    public function clearCache(Plot $plot): void
    {
        $this->cacheService->forget(
            sprintf('advanced_analysis_plot_%d', $plot->id)
        );
    }

    /**
     * Analyze LAI and yield prediction
     *
     * @param mixed $historical
     */
    private function analyzeLAI(Plot $plot, PlotRemoteSensing $current, $historical): array
    {
        $lai = $this->laiCalculator->calculateFromNDVI($current->ndvi_mean);
        $classification = $this->laiCalculator->classifyLAI($lai);

        // Get last year data if available
        $lastYearData = $historical->first(function ($record) {
            return $record->image_date->between(
                now()->subYear()->subWeeks(2),
                now()->subYear()->addWeeks(2)
            );
        });

        $comparison = null;
        if ($lastYearData) {
            $analysis = $this->laiCalculator->getCompleteAnalysis($current, $lastYearData);
            $comparison = $analysis['year_comparison'];
        }

        // Estimate yield
        $varietyType = $plot->variety_type ?? 'red'; // You might need to add this to Plot model
        $yieldEstimation = $this->laiCalculator->estimateYield(
            $lai,
            $plot->area_ha ?? 1.0,
            $varietyType
        );

        // Get recommendations
        $recommendations = $this->laiCalculator->getManagementRecommendations(
            $lai,
            now()->month
        );

        return [
            'value' => $lai,
            'classification' => $classification,
            'year_comparison' => $comparison,
            'yield_estimation' => $yieldEstimation,
            'recommendations' => $recommendations,
        ];
    }

    /**
     * Analyze chlorophyll and nitrogen status
     */
    private function analyzeChlorophyll(PlotRemoteSensing $current): array
    {
        // For now, we estimate GNDVI from NDVI (simplified)
        // In production with Sentinel-2, you'd use actual green band
        $gndvi = $current->ndvi_mean * 0.95; // GNDVI is typically slightly lower

        $chlorophyllPercent = $this->chlorophyllCalculator->estimateChlorophyllContent($gndvi);
        $diagnosis = $this->chlorophyllCalculator->diagnoseNutritionalStatus(
            $gndvi,
            $current->ndvi_mean
        );

        $nitrogenNeed = $this->chlorophyllCalculator->calculateNitrogenNeed($gndvi);

        return [
            'gndvi' => round($gndvi, 4),
            'chlorophyll_percent' => $chlorophyllPercent,
            'diagnosis' => $diagnosis,
            'nitrogen_need' => $nitrogenNeed,
        ];
    }

    /**
     * Analyze maturity and harvest prediction
     *
     * @param mixed $historical
     */
    private function analyzeMaturity(Plot $plot, PlotRemoteSensing $current, $historical): array
    {
        // Calculate simple accumulated GDD from temperature data
        $gdd = 0;
        foreach ($historical as $record) {
            if ($record->temperature) {
                $dailyGDD = max(0, $record->temperature - 10); // Base temp 10°C for grapes
                $gdd += $dailyGDD;
            }
        }

        // Estimate veraison date (typically mid-July to early August)
        $veraison = Carbon::create(now()->year, 7, 20);

        // Get historical NDVI array
        $historicalNDVI = $historical->pluck('ndvi_mean')->filter()->toArray();

        // Get weather history for quality assessment
        $weatherHistory = $historical->map(function ($record) {
            return [
                'temp_mean' => $record->temperature ?? 20,
                'temp_min' => $record->temperature_min ?? 15,
                'temp_max' => $record->temperature_max ?? 25,
                'precipitation' => $record->precipitation ?? 0,
            ];
        })->toArray();

        $maturityAnalysis = $this->maturityCalculator->calculateMaturityIndex(
            $current,
            $historicalNDVI,
            $gdd,
            $veraison
        );

        $qualityPotential = $this->maturityCalculator->calculateQualityPotential(
            $maturityAnalysis['maturity_index'],
            $weatherHistory,
            $current->ndvi_mean
        );

        return array_merge($maturityAnalysis, [
            'gdd_accumulated' => round($gdd, 1),
            'quality_potential' => $qualityPotential,
        ]);
    }

    /**
     * Detect anomalies
     *
     * @param mixed $historical
     */
    private function detectAnomalies(PlotRemoteSensing $current, $historical): array
    {
        return $this->anomalyDetector->detectAnomalies($current, $historical);
    }

    /**
     * Generate executive summary
     */
    private function generateSummary(array $analysis): array
    {
        $summary = [];

        // LAI Summary
        $lai = $analysis['lai'];
        $summary[] = [
            'category' => __('Vigor y Producción'),
            'icon' => $lai['classification']['icon'],
            'status' => $lai['classification']['label'],
            'text' => sprintf(
                'LAI: %.1f - %s. Producción estimada: %.0f kg/ha',
                $lai['value'],
                $lai['classification']['description'],
                $lai['yield_estimation']['yield_per_ha']
            ),
        ];

        // Chlorophyll Summary
        $chloro = $analysis['chlorophyll'];
        $summary[] = [
            'category' => __('Estado Nutricional'),
            'icon' => $chloro['diagnosis']['icon'],
            'status' => $chloro['diagnosis']['label'],
            'text' => sprintf(
                'Clorofila: %.0f%% - %s',
                $chloro['chlorophyll_percent'],
                $chloro['diagnosis']['diagnosis']
            ),
        ];

        // Maturity Summary
        $maturity = $analysis['maturity'];
        $summary[] = [
            'category' => __('Maduración'),
            'icon' => $maturity['classification']['icon'],
            'status' => $maturity['classification']['label'],
            'text' => sprintf(
                'Índice: %.0f%% - °Brix estimado: %.1f - %s días para vendimia',
                $maturity['maturity_index'],
                $maturity['predicted_brix']['value'],
                $maturity['estimated_days_to_harvest'] ?? 'N/A'
            ),
        ];

        // Anomalies Summary
        $anomalies = $analysis['anomalies'];
        if ($anomalies['has_anomalies']) {
            $summary[] = [
                'category' => __('Alertas'),
                'icon' => '🚨',
                'status' => strtoupper($anomalies['risk_level']),
                'text' => sprintf(
                    '%d anomalía(s) detectada(s) - Nivel de riesgo: %s',
                    $anomalies['count'],
                    $anomalies['risk_level']
                ),
            ];
        }

        return $summary;
    }

    /**
     * Generate priority actions
     */
    private function generatePriorityActions(array $analysis): array
    {
        $actions = [];

        // Critical anomalies first
        if ($analysis['anomalies']['has_anomalies']) {
            foreach ($analysis['anomalies']['anomalies'] as $anomaly) {
                if (in_array($anomaly['severity'], ['critical', 'high'])) {
                    $actions[] = [
                        'priority' => $anomaly['severity'] === 'critical' ? 1 : 2,
                        'category' => __('Anomalía'),
                        'icon' => $anomaly['icon'],
                        'title' => $anomaly['title'],
                        'description' => $anomaly['description'],
                        'action' => $anomaly['recommended_actions'][0] ?? 'Revisar parcela',
                    ];
                }
            }
        }

        // Nitrogen deficiency
        if ($analysis['chlorophyll']['nitrogen_need']['nitrogen_kg_ha'] > 0) {
            $actions[] = [
                'priority' => $analysis['chlorophyll']['nitrogen_need']['status'] === 'high' ? 2 : 3,
                'category' => __('Fertilización'),
                'icon' => '🌱',
                'title' => __('Necesidad de Nitrógeno'),
                'description' => $analysis['chlorophyll']['nitrogen_need']['recommendation'],
                'action' => sprintf(
                    'Aplicar %.0f kg N/ha',
                    $analysis['chlorophyll']['nitrogen_need']['nitrogen_kg_ha']
                ),
            ];
        }

        // Harvest timing
        if ($analysis['maturity']['maturity_index'] >= 80) {
            $actions[] = [
                'priority' => $analysis['maturity']['maturity_index'] >= 90 ? 1 : 2,
                'category' => __('Vendimia'),
                'icon' => '🍇',
                'title' => __('Momento de Vendimia'),
                'description' => $analysis['maturity']['classification']['description'],
                'action' => $analysis['maturity']['estimated_days_to_harvest'] === 0
                    ? 'Vendimiar ahora'
                    : sprintf('Vendimiar en %d días', $analysis['maturity']['estimated_days_to_harvest']),
            ];
        }

        // LAI-based actions
        foreach ($analysis['lai']['recommendations'] as $rec) {
            $actions[] = [
                'priority' => $rec['type'] === 'warning' ? 2 : 3,
                'category' => __('Manejo del Dosel'),
                'icon' => $rec['icon'],
                'title' => $rec['title'],
                'description' => $rec['text'],
                'action' => __('Ver recomendaciones'),
            ];
        }

        // Sort by priority
        usort($actions, fn ($a, $b) => $a['priority'] <=> $b['priority']);

        return array_slice($actions, 0, 5); // Top 5 actions
    }

    /**
     * Update database with calculated metrics
     */
    private function updateAdvancedMetrics(PlotRemoteSensing $current, array $analysis): void
    {
        try {
            $current->update([
                'lai' => $analysis['lai']['value'],
                'gndvi' => $analysis['chlorophyll']['gndvi'],
                'chlorophyll_content' => $analysis['chlorophyll']['chlorophyll_percent'],
                'maturity_index' => $analysis['maturity']['maturity_index'],
                'predicted_brix' => $analysis['maturity']['predicted_brix']['value'],
                'days_to_harvest' => $analysis['maturity']['estimated_days_to_harvest'],
                'anomaly_detected' => $analysis['anomalies']['has_anomalies'],
                'anomaly_severity' => $analysis['anomalies']['risk_level'],
                'anomaly_type' => $analysis['anomalies']['has_anomalies']
                    ? $analysis['anomalies']['anomalies'][0]['type']
                    : null,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update advanced metrics', [
                'plot_remote_sensing_id' => $current->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get empty analysis structure
     */
    private function getEmptyAnalysis(string $reason): array
    {
        return [
            'plot_id' => null,
            'plot_name' => null,
            'analyzed_at' => now()->toIso8601String(),
            'data_date' => null,
            'error' => $reason,
            'lai' => null,
            'chlorophyll' => null,
            'maturity' => null,
            'anomalies' => [
                'has_anomalies' => false,
                'count' => 0,
                'anomalies' => [],
                'risk_level' => 'none',
            ],
            'summary' => [],
            'priority_actions' => [],
        ];
    }
}
