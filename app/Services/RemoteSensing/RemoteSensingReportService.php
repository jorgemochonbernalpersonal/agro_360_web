<?php

namespace App\Services\RemoteSensing;

use App\Models\Plot;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class RemoteSensingReportService
{
    /**
     * Generate PDF report for a plot
     */
    public function generatePlotReport(Plot $plot, int $days = 30): array
    {
        $service = new NasaEarthdataService(
            app(\App\Repositories\PlotRemoteSensingRepository::class),
            app(RemoteSensingCacheService::class),
            app(RateLimitService::class)
        );

        $latestData = $service->getLatestData($plot);
        $historicalData = $service->getHistoricalData($plot, $days);

        if (!$latestData) {
            return ['success' => false, 'error' => __('No data available')];
        }

        $data = [
            'plot' => $plot,
            'latest' => $latestData,
            'historical' => $historicalData,
            'stats' => $this->calculateStats($historicalData),
            'generated_at' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('reports.remote-sensing-plot', $data)
            ->setPaper('a4', 'portrait');

        $filename = sprintf('remote_sensing_%s_%s.pdf', $plot->id, now()->format('Y-m-d_His'));
        $path = storage_path('app/reports/' . $filename);

        // Ensure directory exists
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $pdf->save($path);

        return [
            'success' => true,
            'pdf_path' => $path,
            'filename' => $filename,
        ];
    }

    /**
     * Generate period report with custom data
     */
    public function generatePeriodReport(Plot $plot, array $historicalData, array $stats): array
    {
        $data = [
            'plot' => $plot,
            'historical' => $historicalData,
            'stats' => $stats,
            'generated_at' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('reports.remote-sensing-period', $data)
            ->setPaper('a4', 'landscape');

        $filename = sprintf('period_analysis_%s_%s.pdf', $plot->id, now()->format('Y-m-d_His'));
        $path = storage_path('app/reports/' . $filename);

        // Ensure directory exists
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $pdf->save($path);

        return [
            'success' => true,
            'pdf_path' => $path,
            'filename' => $filename,
        ];
    }

    /**
     * Calculate statistics from historical data
     */
    private function calculateStats($historicalData): array
    {
        if ($historicalData->isEmpty()) {
            return [
                'average' => 0,
                'min' => 0,
                'max' => 0,
                'trend' => 'stable',
            ];
        }

        $values = $historicalData->pluck('ndvi_mean')->filter()->values();

        if ($values->isEmpty()) {
            return [
                'average' => 0,
                'min' => 0,
                'max' => 0,
                'trend' => 'stable',
            ];
        }

        return [
            'average' => round($values->avg(), 3),
            'min' => round($values->min(), 3),
            'max' => round($values->max(), 3),
            'stddev' => round($values->count() > 1 ? $this->standardDeviation($values->toArray()) : 0, 3),
            'trend' => $this->determineTrend($values->toArray()),
        ];
    }

    /**
     * Calculate standard deviation
     */
    private function standardDeviation(array $values): float
    {
        $count = count($values);
        if ($count <= 1) return 0;

        $mean = array_sum($values) / $count;
        $variance = array_sum(array_map(fn($x) => pow($x - $mean, 2), $values)) / $count;

        return sqrt($variance);
    }

    /**
     * Determine trend from values
     */
    private function determineTrend(array $values): string
    {
        $count = count($values);
        if ($count < 2) return 'stable';

        $first = array_slice($values, 0, (int)($count / 3));
        $last = array_slice($values, -1 * (int)($count / 3));

        $avgFirst = array_sum($first) / count($first);
        $avgLast = array_sum($last) / count($last);

        $change = $avgLast - $avgFirst;

        if ($change > 0.05) return 'increasing';
        if ($change < -0.05) return 'decreasing';
        return 'stable';
    }

    /**
     * Generate comparison report for two plots
     */
    public function generateComparisonReport(Plot $plot1, Plot $plot2, int $days = 30): array
    {
        $service = new NasaEarthdataService(
            app(\App\Repositories\PlotRemoteSensingRepository::class),
            app(RemoteSensingCacheService::class),
            app(RateLimitService::class)
        );

        $data1 = $service->getHistoricalData($plot1, $days);
        $data2 = $service->getHistoricalData($plot2, $days);

        $data = [
            'plot1' => $plot1,
            'plot2' => $plot2,
            'data1' => $data1,
            'data2' => $data2,
            'stats1' => $this->calculateStats($data1),
            'stats2' => $this->calculateStats($data2),
            'generated_at' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('reports.remote-sensing-comparison', $data)
            ->setPaper('a4', 'landscape');

        $filename = sprintf('comparison_%s_vs_%s_%s.pdf', $plot1->id, $plot2->id, now()->format('Y-m-d_His'));
        $path = storage_path('app/reports/' . $filename);

        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $pdf->save($path);

        return [
            'success' => true,
            'pdf_path' => $path,
            'filename' => $filename,
        ];
    }

    /**
     * Download report
     */
    public function downloadReport(string $path)
    {
        if (!file_exists($path)) {
            throw new \Exception(__('Report file not found'));
        }

        return response()->download($path)->deleteFileAfterSend(true);
    }
}
