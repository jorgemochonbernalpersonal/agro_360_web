<?php

namespace App\Services\RemoteSensing;

use App\Models\Plot;
use App\Models\PlotRemoteSensing;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * Service for generating NDVI/Remote Sensing PDF Reports
 */
class RemoteSensingReportService
{
    /**
     * Generate NDVI report for a single plot
     */
    public function generatePlotReport(Plot $plot, int $days = 90): array
    {
        $user = auth()->user();
        
        // Get historical data
        $service = new NasaEarthdataService();
        $historicalData = $service->getHistoricalData($plot, $days);
        $latestData = $service->getLatestData($plot);
        
        // Calculate statistics
        $stats = $this->calculateStats($historicalData);
        
        // Get GDD data
        $phenologyService = new PhenologyService();
        $gdd = $phenologyService->calculateGdd($plot);
        
        // Generate PDF
        $pdfPath = $this->generatePDF($plot, $user, $latestData, $historicalData, $stats, $gdd);
        
        return [
            'success' => true,
            'pdf_path' => $pdfPath,
            'plot_name' => $plot->name,
            'stats' => $stats,
        ];
    }
    
    /**
     * Generate NDVI report for all user plots
     */
    public function generateGlobalReport(User $user, int $days = 90): array
    {
        $plots = Plot::forUser($user)->get();
        $service = new SentinelHubService();
        
        $plotsData = [];
        $globalStats = [
            'total_plots' => $plots->count(),
            'total_ndvi' => 0,
            'excellent' => 0,
            'good' => 0,
            'moderate' => 0,
            'poor' => 0,
            'critical' => 0,
        ];
        
        foreach ($plots as $plot) {
            $latestData = $service->getLatestData($plot);
            
            if ($latestData) {
                $globalStats['total_ndvi'] += $latestData->ndvi_mean;
                
                match ($latestData->health_status) {
                    'excellent' => $globalStats['excellent']++,
                    'good' => $globalStats['good']++,
                    'moderate' => $globalStats['moderate']++,
                    'poor' => $globalStats['poor']++,
                    'critical' => $globalStats['critical']++,
                    default => null,
                };
                
                $plotsData[] = [
                    'plot' => $plot,
                    'data' => $latestData,
                    'historical' => $service->getHistoricalData($plot, $days),
                ];
            }
        }
        
        $globalStats['average_ndvi'] = $plots->count() > 0 
            ? round($globalStats['total_ndvi'] / $plots->count(), 3) 
            : 0;
        
        // Generate PDF
        $pdfPath = $this->generateGlobalPDF($user, $plotsData, $globalStats, $days);
        
        return [
            'success' => true,
            'pdf_path' => $pdfPath,
            'stats' => $globalStats,
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
                'data_points' => 0,
            ];
        }
        
        $values = $historicalData->pluck('ndvi_mean')->filter();
        
        // Calculate trend (compare first half to second half)
        $halfCount = (int) ceil($values->count() / 2);
        $firstHalf = $values->take($halfCount)->avg();
        $secondHalf = $values->skip($halfCount)->avg();
        
        $trend = 'stable';
        if ($secondHalf > $firstHalf + 0.05) {
            $trend = 'increasing';
        } elseif ($secondHalf < $firstHalf - 0.05) {
            $trend = 'decreasing';
        }
        
        return [
            'average' => round($values->avg(), 3),
            'min' => round($values->min(), 3),
            'max' => round($values->max(), 3),
            'trend' => $trend,
            'data_points' => $values->count(),
        ];
    }
    
    /**
     * Generate PDF for a single plot
     */
    private function generatePDF(Plot $plot, User $user, $latestData, $historicalData, array $stats, array $gdd = []): string
    {
        $filename = sprintf(
            'teledeteccion_%s_%s.pdf',
            str_replace(' ', '_', $plot->name),
            Carbon::now()->format('Y-m-d_His')
        );
        
        $path = 'reports/remote-sensing/' . $user->id . '/' . $filename;
        
        // Chart data for the view
        $chartData = $historicalData->map(fn($item) => [
            'date' => $item->image_date->format('d/m'),
            'ndvi' => $item->ndvi_mean,
        ])->values()->toArray();
        
        $pdf = Pdf::loadView('reports.remote-sensing-plot', [
            'plot' => $plot,
            'user' => $user,
            'latestData' => $latestData,
            'historicalData' => $historicalData,
            'chartData' => $chartData,
            'stats' => $stats,
            'gdd' => $gdd,
            'generatedAt' => Carbon::now(),
        ]);
        
        $pdf->setPaper('A4', 'portrait');
        
        Storage::disk('public')->put($path, $pdf->output());
        
        Log::info('Generated NDVI plot report', [
            'plot_id' => $plot->id,
            'user_id' => $user->id,
            'path' => $path,
        ]);
        
        return $path;
    }
    
    /**
     * Generate global PDF for all plots
     */
    private function generateGlobalPDF(User $user, array $plotsData, array $stats, int $days): string
    {
        $filename = sprintf(
            'teledeteccion_global_%s.pdf',
            Carbon::now()->format('Y-m-d_His')
        );
        
        $path = 'reports/remote-sensing/' . $user->id . '/' . $filename;
        
        $pdf = Pdf::loadView('reports.remote-sensing-global', [
            'user' => $user,
            'plotsData' => $plotsData,
            'stats' => $stats,
            'days' => $days,
            'generatedAt' => Carbon::now(),
        ]);
        
        $pdf->setPaper('A4', 'portrait');
        
        Storage::disk('public')->put($path, $pdf->output());
        
        Log::info('Generated global NDVI report', [
            'user_id' => $user->id,
            'plots_count' => count($plotsData),
            'path' => $path,
        ]);
        
        return $path;
    }
    
    /**
     * Download a generated report
     */
    public function downloadReport(string $path)
    {
        if (!Storage::disk('public')->exists($path)) {
            throw new \Exception('El informe no existe');
        }
        
        return Storage::disk('public')->download($path);
    }
}
