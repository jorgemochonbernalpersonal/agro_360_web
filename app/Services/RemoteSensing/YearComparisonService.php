<?php

namespace App\Services\RemoteSensing;

use App\Models\Plot;
use App\Models\PlotRemoteSensing;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class YearComparisonService
{
    /**
     * Get year-over-year comparison data
     */
    public function compareYears(Plot $plot, int $year1, int $year2): array
    {
        $data1 = $this->getYearlyData($plot, $year1);
        $data2 = $this->getYearlyData($plot, $year2);

        return [
            'year1' => [
                'year' => $year1,
                'data' => $data1,
                'summary' => $this->calculateSummary($data1),
            ],
            'year2' => [
                'year' => $year2,
                'data' => $data2,
                'summary' => $this->calculateSummary($data2),
            ],
            'comparison' => $this->calculateComparison($data1, $data2),
        ];
    }

    /**
     * Get monthly averages for a year
     */
    public function getYearlyData(Plot $plot, int $year): Collection
    {
        $records = PlotRemoteSensing::where('plot_id', $plot->id)
            ->whereYear('image_date', $year)
            ->orderBy('image_date')
            ->get();

        // Group by month
        return $records->groupBy(function ($record) {
            return $record->image_date->month;
        })->map(function ($monthRecords, $month) {
            return [
                'month' => $month,
                'month_name' => Carbon::create()->month($month)->locale('es')->monthName,
                'ndvi_avg' => $monthRecords->avg('ndvi_mean'),
                'ndvi_min' => $monthRecords->min('ndvi_mean'),
                'ndvi_max' => $monthRecords->max('ndvi_mean'),
                'ndwi_avg' => $monthRecords->whereNotNull('ndwi_mean')->avg('ndwi_mean'),
                'temperature_avg' => $monthRecords->whereNotNull('temperature')->avg('temperature'),
                'precipitation_sum' => $monthRecords->sum('precipitation'),
                'records_count' => $monthRecords->count(),
            ];
        })->sortBy('month')->values();
    }

    /**
     * Get available years for a plot
     */
    public function getAvailableYears(Plot $plot): array
    {
        return PlotRemoteSensing::where('plot_id', $plot->id)
            ->selectRaw('YEAR(image_date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();
    }

    /**
     * Calculate summary for a year's data
     */
    private function calculateSummary(Collection $monthlyData): array
    {
        if ($monthlyData->isEmpty()) {
            return [
                'avg_ndvi' => null,
                'min_ndvi' => null,
                'max_ndvi' => null,
                'total_precipitation' => 0,
                'avg_temperature' => null,
            ];
        }

        return [
            'avg_ndvi' => $monthlyData->avg('ndvi_avg'),
            'min_ndvi' => $monthlyData->min('ndvi_min'),
            'max_ndvi' => $monthlyData->max('ndvi_max'),
            'total_precipitation' => $monthlyData->sum('precipitation_sum'),
            'avg_temperature' => $monthlyData->whereNotNull('temperature_avg')->avg('temperature_avg'),
        ];
    }

    /**
     * Calculate comparison metrics between two years
     */
    private function calculateComparison(Collection $data1, Collection $data2): array
    {
        $summary1 = $this->calculateSummary($data1);
        $summary2 = $this->calculateSummary($data2);

        $ndviChange = null;
        if ($summary1['avg_ndvi'] !== null && $summary2['avg_ndvi'] !== null) {
            $ndviChange = (($summary2['avg_ndvi'] - $summary1['avg_ndvi']) / $summary1['avg_ndvi']) * 100;
        }

        return [
            'ndvi_change_percent' => $ndviChange,
            'ndvi_trend' => $ndviChange !== null ? ($ndviChange > 0 ? 'improving' : ($ndviChange < 0 ? 'declining' : 'stable')) : 'unknown',
            'precipitation_change' => $summary2['total_precipitation'] - $summary1['total_precipitation'],
        ];
    }
}
