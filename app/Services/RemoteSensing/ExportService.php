<?php

namespace App\Services\RemoteSensing;

use App\Models\Plot;
use App\Models\PlotRemoteSensing;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class ExportService
{
    /**
     * Export remote sensing data to PDF
     */
    public function exportToPdf(Plot $plot, ?Carbon $startDate = null, ?Carbon $endDate = null): \Barryvdh\DomPDF\PDF
    {
        $data = $this->getExportData($plot, $startDate, $endDate);

        return Pdf::loadView('exports.remote-sensing-pdf', [
            'plot' => $plot,
            'data' => $data['records'],
            'summary' => $data['summary'],
            'startDate' => $startDate ?? now()->subMonths(3),
            'endDate' => $endDate ?? now(),
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape');
    }

    /**
     * Get data for export
     */
    public function getExportData(Plot $plot, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->subMonths(3);
        $endDate = $endDate ?? now();

        $records = PlotRemoteSensing::where('plot_id', $plot->id)
            ->whereBetween('image_date', [$startDate, $endDate])
            ->orderBy('image_date', 'desc')
            ->get();

        $summary = $this->calculateSummary($records);

        return [
            'records' => $records,
            'summary' => $summary,
        ];
    }

    /**
     * Get data formatted for Excel export
     */
    public function getExcelData(Plot $plot, ?Carbon $startDate = null, ?Carbon $endDate = null): Collection
    {
        $startDate = $startDate ?? now()->subMonths(3);
        $endDate = $endDate ?? now();

        return PlotRemoteSensing::where('plot_id', $plot->id)
            ->whereBetween('image_date', [$startDate, $endDate])
            ->orderBy('image_date', 'desc')
            ->get()
            ->map(function ($record) {
                return [
                    'Fecha' => $record->image_date->format('d/m/Y'),
                    'NDVI' => number_format($record->ndvi_mean, 4),
                    'NDVI Mín' => number_format($record->ndvi_min, 4),
                    'NDVI Máx' => number_format($record->ndvi_max, 4),
                    'NDWI' => $record->ndwi_mean ? number_format($record->ndwi_mean, 4) : 'N/A',
                    'Estado' => $record->health_text,
                    'Tendencia' => $record->trend ?? 'N/A',
                    'Temperatura (°C)' => $record->temperature ? number_format($record->temperature, 1) : 'N/A',
                    'Precipitación (mm)' => $record->precipitation ? number_format($record->precipitation, 1) : 'N/A',
                    'Humedad (%)' => $record->humidity ? number_format($record->humidity, 0) : 'N/A',
                    'Humedad Suelo (%)' => $record->soil_moisture ? number_format($record->soil_moisture, 0) : 'N/A',
                ];
            });
    }

    /**
     * Calculate summary statistics
     */
    private function calculateSummary(Collection $records): array
    {
        if ($records->isEmpty()) {
            return [
                'avg_ndvi' => null,
                'min_ndvi' => null,
                'max_ndvi' => null,
                'avg_ndwi' => null,
                'total_records' => 0,
                'health_distribution' => [],
            ];
        }

        $healthDistribution = $records->groupBy('health_status')
            ->map(fn($group) => $group->count())
            ->toArray();

        return [
            'avg_ndvi' => $records->avg('ndvi_mean'),
            'min_ndvi' => $records->min('ndvi_mean'),
            'max_ndvi' => $records->max('ndvi_mean'),
            'avg_ndwi' => $records->whereNotNull('ndwi_mean')->avg('ndwi_mean'),
            'avg_temperature' => $records->whereNotNull('temperature')->avg('temperature'),
            'total_precipitation' => $records->sum('precipitation'),
            'total_records' => $records->count(),
            'health_distribution' => $healthDistribution,
        ];
    }
}
