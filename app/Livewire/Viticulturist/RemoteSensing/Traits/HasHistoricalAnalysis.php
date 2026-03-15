<?php

namespace App\Livewire\Viticulturist\RemoteSensing\Traits;

use App\Models\PlotRemoteSensing;
use App\Repositories\PlotRemoteSensingRepository;
use App\Services\RemoteSensing\NasaEarthdataService;

/**
 * Historical data loading, period alerts, trend prediction, and comparison logic.
 * Extracted from Dashboard.php to reduce its size.
 */
trait HasHistoricalAnalysis
{
    // ndviBaseline is declared in Dashboard.php as public array $ndviBaseline = []

    private function loadHistoricalData(): void
    {
        if (!$this->selectedPlot) return;

        $nasaService = app(NasaEarthdataService::class);

        $startDate = null;
        $endDate   = now();

        switch ($this->historyPeriod) {
            case '7_days':
                $this->historyDays = 7;
                break;
            case '30_days':
                $this->historyDays = 30;
                break;
            case '90_days':
                $this->historyDays = 90;
                break;
            case 'current_season':
                $currentYear = now()->year;
                $startDate   = \Carbon\Carbon::create($currentYear, 4, 1);
                $endDate     = now();
                if (now()->month < 4) {
                    $startDate = \Carbon\Carbon::create($currentYear - 1, 4, 1);
                }
                $this->historyDays = $startDate->diffInDays($endDate);
                break;
            case 'last_season':
                $currentYear = now()->year;
                $startDate   = \Carbon\Carbon::create($currentYear - 1, 4, 1);
                $endDate     = \Carbon\Carbon::create($currentYear - 1, 10, 31);
                $this->historyDays = $startDate->diffInDays($endDate);
                break;
            case '1_year':
                $this->historyDays = 365;
                break;
            case 'custom':
                if ($this->customStartDate && $this->customEndDate) {
                    $startDate = \Carbon\Carbon::parse($this->customStartDate);
                    $endDate   = \Carbon\Carbon::parse($this->customEndDate);
                    $this->historyDays = $startDate->diffInDays($endDate);
                } else {
                    $this->historyDays = 90;
                }
                break;
            default:
                $this->historyDays = 90;
        }

        if ($startDate && $endDate && $this->historyPeriod === 'custom') {
            $historical = PlotRemoteSensing::where('plot_id', $this->selectedPlot->id)
                ->whereBetween('image_date', [$startDate, $endDate])
                ->orderBy('image_date', 'asc')
                ->get();
        } else {
            $historical = $nasaService->getHistoricalData($this->selectedPlot, $this->historyDays);
        }

        $this->historicalData = $historical->map(fn($item) => [
            'date'           => $item->image_date->format('d/m'),
            'ndvi'           => $item->ndvi_mean,
            'fullDate'       => $item->image_date->format('d/m/Y'),
            'health_status'  => $item->health_status,
            'cloud_coverage' => $item->cloud_coverage ?? 0,
            'high_clouds'    => $item->hasHighCloudCoverage(),
        ])->values()->toArray();

        $this->detectPeriodAlerts();
        $this->calculateTrendPrediction();

        if ($this->showComparison) {
            $this->loadComparisonPeriod();
        }

        // Improvement #8: load weekly NDVI baseline for the history chart
        $repository = app(PlotRemoteSensingRepository::class);
        $baseline   = $repository->getNdviWeeklyBaseline($this->selectedPlot, $this->selectedSigpacId);

        $this->ndviBaseline = collect($baseline)->map(fn($row) => [
            'week'   => (int) $row['week'],
            'mean'   => round((float) $row['mean'], 3),
            'stddev' => round((float) ($row['stddev'] ?? 0), 3),
            'count'  => (int) $row['count'],
        ])->values()->toArray();
    }

    public function updatedHistoryPeriod(): void
    {
        $this->loadHistoricalData();
    }

    public function applyCustomDateRange(): void
    {
        if (!$this->customStartDate || !$this->customEndDate) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Por favor, selecciona ambas fechas']);
            return;
        }

        $start = \Carbon\Carbon::parse($this->customStartDate);
        $end   = \Carbon\Carbon::parse($this->customEndDate);

        if ($start->gt($end)) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'La fecha inicial debe ser anterior a la final']);
            return;
        }

        if ($start->diffInDays($end) > 730) {
            $this->dispatch('notify', ['type' => 'warning', 'message' => 'El rango máximo es de 2 años']);
            return;
        }

        $this->historyPeriod = 'custom';
        $this->loadHistoricalData();
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Rango personalizado aplicado']);
    }

    public function exportCSV()
    {
        if (empty($this->historicalData) || !$this->selectedPlot) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'No hay datos para exportar']);
            return;
        }

        $filename = sprintf('ndvi_%s_%s.csv', str_replace(' ', '_', $this->selectedPlot->name), now()->format('Y-m-d'));
        $csv      = "Fecha,NDVI,Estado,Tendencia\n";
        foreach ($this->historicalData as $record) {
            $csv .= sprintf("%s,%s,%s,%s\n", $record['fullDate'], $record['ndvi'], $record['health_status'] ?? 'N/A', $record['trend'] ?? 'N/A');
        }

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function exportPDF()
    {
        if (empty($this->historicalData) || !$this->selectedPlot) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'No hay datos para exportar']);
            return;
        }

        try {
            $service    = new \App\Services\RemoteSensing\RemoteSensingReportService();
            $ndviValues = array_column($this->historicalData, 'ndvi');
            $stats      = [
                'avg'    => array_sum($ndviValues) / count($ndviValues),
                'max'    => max($ndviValues),
                'min'    => min($ndviValues),
                'count'  => count($ndviValues),
                'period' => $this->historyDays,
            ];

            $result = $service->generatePeriodReport($this->selectedPlot, $this->historicalData, $stats);

            if ($result['success']) {
                return response()->download($result['pdf_path'])->deleteFileAfterSend(true);
            }

            throw new \Exception('Error al generar PDF');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('PDF export error', ['error' => $e->getMessage()]);
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Error al generar PDF']);
        }
    }

    public function toggleComparison(): void
    {
        $this->showComparison = !$this->showComparison;
        if ($this->showComparison) {
            $this->loadComparisonPeriod();
        }
    }

    private function loadComparisonPeriod(): void
    {
        if (!$this->selectedPlot) return;

        $startDate = null;
        $endDate   = null;

        switch ($this->comparisonPeriod) {
            case 'last_year':
                $startDate = now()->subYear()->subDays($this->historyDays);
                $endDate   = now()->subYear();
                break;
            case 'last_season':
                $year      = now()->year - 1;
                $startDate = \Carbon\Carbon::create($year, 4, 1);
                $endDate   = \Carbon\Carbon::create($year, 10, 31);
                break;
            case 'same_month_last_year':
                $startDate = now()->subYear()->startOfMonth();
                $endDate   = now()->subYear()->endOfMonth();
                break;
        }

        if ($startDate && $endDate) {
            $comparison = PlotRemoteSensing::where('plot_id', $this->selectedPlot->id)
                ->whereBetween('image_date', [$startDate, $endDate])
                ->orderBy('image_date', 'asc')
                ->get();

            $this->comparisonData = $comparison->map(fn($item) => [
                'date'          => $item->image_date->format('d/m'),
                'ndvi'          => $item->ndvi_mean,
                'fullDate'      => $item->image_date->format('d/m/Y'),
                'health_status' => $item->health_status,
            ])->values()->toArray();
        }
    }

    public function updatedComparisonPeriod(): void
    {
        if ($this->showComparison) {
            $this->loadComparisonPeriod();
        }
    }

    private function detectPeriodAlerts(): void
    {
        $this->periodAlerts = [];

        if (empty($this->historicalData)) return;

        foreach ($this->historicalData as $index => $record) {
            if ($record['ndvi'] < $this->ndviThreshold) {
                $this->periodAlerts[] = [
                    'type'     => 'low_ndvi',
                    'severity' => $record['ndvi'] < 0.15 ? 'critical' : 'warning',
                    'date'     => $record['fullDate'],
                    'ndvi'     => $record['ndvi'],
                    'message'  => sprintf('NDVI bajo (%s) el %s', number_format($record['ndvi'], 3), $record['fullDate']),
                ];
            }

            if ($index > 0) {
                $prevNdvi = $this->historicalData[$index - 1]['ndvi'];
                $decline  = $prevNdvi - $record['ndvi'];

                if ($decline > 0.15) {
                    $this->periodAlerts[] = [
                        'type'     => 'rapid_decline',
                        'severity' => 'warning',
                        'date'     => $record['fullDate'],
                        'decline'  => $decline,
                        'message'  => sprintf('Caída rápida de NDVI (-%s) el %s', number_format($decline, 3), $record['fullDate']),
                    ];
                }
            }
        }
    }

    private function calculateTrendPrediction(): void
    {
        $this->trendPrediction = [];

        if (count($this->historicalData) < 5) return;

        $n        = count($this->historicalData);
        $xValues  = range(1, $n);
        $yValues  = array_column($this->historicalData, 'ndvi');

        $sumX  = array_sum($xValues);
        $sumY  = array_sum($yValues);
        $sumXY = 0;
        $sumX2 = 0;

        for ($i = 0; $i < $n; $i++) {
            $sumXY += $xValues[$i] * $yValues[$i];
            $sumX2 += $xValues[$i] * $xValues[$i];
        }

        $m = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        $b = ($sumY - $m * $sumX) / $n;

        $predictions = [];
        for ($i = 1; $i <= 7; $i++) {
            $x             = $n + $i;
            $predictedNdvi = $m * $x + $b;
            $predictions[] = ['day' => $i, 'ndvi' => max(0, min(1, $predictedNdvi))];
        }

        $this->trendPrediction = [
            'slope'       => $m,
            'intercept'   => $b,
            'trend'       => $m > 0.001 ? 'improving' : ($m < -0.001 ? 'declining' : 'stable'),
            'predictions' => $predictions,
            'confidence'  => $this->calculateConfidence($yValues, $m, $b),
        ];
    }

    private function calculateConfidence(array $yValues, float $m, float $b): float
    {
        $n      = count($yValues);
        $yMean  = array_sum($yValues) / $n;
        $ssTotal    = 0;
        $ssResidual = 0;

        for ($i = 0; $i < $n; $i++) {
            $yPredicted  = $m * ($i + 1) + $b;
            $ssTotal    += pow($yValues[$i] - $yMean, 2);
            $ssResidual += pow($yValues[$i] - $yPredicted, 2);
        }

        return max(0, min(1, $ssTotal > 0 ? 1 - ($ssResidual / $ssTotal) : 0));
    }

    public function updatedNdviThreshold(): void
    {
        $this->detectPeriodAlerts();
    }
}
