<?php

namespace App\Services\RemoteSensing;

use App\Models\Plot;
use App\Models\PlotRemoteSensing;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class ImageHistoryService
{
    /**
     * Get historical images/data for a plot
     */
    public function getHistory(Plot $plot, int $months = 12): Collection
    {
        return PlotRemoteSensing::where('plot_id', $plot->id)
            ->where('image_date', '>=', now()->subMonths($months))
            ->orderBy('image_date', 'desc')
            ->get()
            ->map(function ($record) {
                return [
                    'id' => $record->id,
                    'date' => $record->image_date->format('d/m/Y'),
                    'date_sort' => $record->image_date->format('Y-m-d'),
                    'month' => $record->image_date->locale('es')->monthName,
                    'year' => $record->image_date->year,
                    'ndvi' => $record->ndvi_mean,
                    'ndvi_formatted' => $record->ndvi_mean !== null ? number_format($record->ndvi_mean, 3) : 'N/A',
                    'ndwi' => $record->ndwi_mean,
                    'ndwi_formatted' => $record->ndwi_mean !== null ? number_format($record->ndwi_mean, 3) : 'N/A',
                    'health_status' => $record->health_status,
                    'health_text' => $record->health_text,
                    'health_color' => $record->health_color,
                    'health_emoji' => $record->health_emoji,
                    'trend' => $record->trend,
                    'trend_icon' => $record->trend_icon,
                    'cloud_coverage' => $record->cloud_coverage,
                    'temperature' => $record->temperature,
                    'precipitation' => $record->precipitation,
                    'image_source' => $record->image_source ?? 'MODIS',
                    'tile_path' => $record->tile_path,
                    // Generate NDVI color for visualization
                    'ndvi_color' => $this->getNdviColor($record->ndvi_mean),
                ];
            });
    }

    /**
     * Get grouped history by month
     */
    public function getHistoryByMonth(Plot $plot, int $months = 12): Collection
    {
        $history = $this->getHistory($plot, $months);

        return $history->groupBy(function ($item) {
            return $item['year'] . '-' . str_pad($item['date_sort'], 2, '0', STR_PAD_LEFT);
        })->map(function ($records, $key) {
            $first = $records->first();
            return [
                'month' => $first['month'],
                'year' => $first['year'],
                'records' => $records,
                'avg_ndvi' => $records->avg('ndvi'),
                'best_record' => $records->sortByDesc('ndvi')->first(),
            ];
        })->sortByDesc(function ($item) {
            return $item['year'] . '-' . $item['month'];
        });
    }

    /**
     * Get NDVI color for visualization
     */
    private function getNdviColor(?float $ndvi): string
    {
        if ($ndvi === null) return '#6b7280'; // gray

        return match (true) {
            $ndvi >= 0.7 => '#166534', // dark green (excellent)
            $ndvi >= 0.5 => '#16a34a', // green (good)
            $ndvi >= 0.3 => '#84cc16', // lime (moderate)
            $ndvi >= 0.15 => '#eab308', // yellow (poor)
            $ndvi >= 0 => '#f97316', // orange (very poor)
            default => '#dc2626', // red (critical/negative)
        };
    }

    /**
     * Get timeline events for visualization
     */
    public function getTimelineEvents(Plot $plot, int $months = 12): array
    {
        $history = $this->getHistory($plot, $months);
        
        $events = [];
        foreach ($history as $record) {
            $events[] = [
                'date' => $record['date'],
                'ndvi' => $record['ndvi'],
                'color' => $record['ndvi_color'],
                'status' => $record['health_text'],
            ];
        }

        return $events;
    }
}
