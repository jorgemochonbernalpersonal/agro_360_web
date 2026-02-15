<?php

namespace App\Repositories;

use App\Models\Plot;
use App\Models\PlotRemoteSensing;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Repository for PlotRemoteSensing database operations
 * Centralizes all queries related to remote sensing data
 */
class PlotRemoteSensingRepository
{
    /**
     * Get the latest remote sensing data for a plot
     */
    public function getLatestForPlot(Plot $plot): ?PlotRemoteSensing
    {
        return PlotRemoteSensing::where('plot_id', $plot->id)
            ->orderBy('image_date', 'desc')
            ->first();
    }

    /**
     * Get latest data for today
     */
    public function getTodayForPlot(Plot $plot): ?PlotRemoteSensing
    {
        return PlotRemoteSensing::where('plot_id', $plot->id)
            ->whereDate('image_date', today())
            ->orderBy('id', 'desc')
            ->first();
    }

    /**
     * Get historical data for a plot
     */
    public function getHistoricalForPlot(Plot $plot, int $days = 90): Collection
    {
        return PlotRemoteSensing::where('plot_id', $plot->id)
            ->where('image_date', '>=', now()->subDays($days))
            ->orderBy('image_date', 'desc')
            ->get();
    }

    /**
     * Get data from same period last year
     */
    public function getLastYearDataForPlot(Plot $plot, int $month): ?PlotRemoteSensing
    {
        return PlotRemoteSensing::where('plot_id', $plot->id)
            ->whereMonth('image_date', $month)
            ->whereYear('image_date', now()->year - 1)
            ->orderBy('image_date', 'desc')
            ->first();
    }

    /**
     * Get all latest data for multiple plots (optimized)
     */
    public function getLatestForPlots(Collection $plotIds): Collection
    {
        return PlotRemoteSensing::whereIn('plot_id', $plotIds)
            ->whereIn('id', function ($subQuery) use ($plotIds) {
                $subQuery->selectRaw('MAX(id)')
                    ->from('plot_remote_sensing')
                    ->whereIn('plot_id', $plotIds)
                    ->groupBy('plot_id');
            })
            ->get()
            ->keyBy('plot_id');
    }

    /**
     * Check if data exists for a specific date
     */
    public function existsForDate(Plot $plot, Carbon $date): bool
    {
        return PlotRemoteSensing::where('plot_id', $plot->id)
            ->whereDate('image_date', $date)
            ->exists();
    }

    /**
     * Create or update remote sensing data
     */
    public function createOrUpdate(Plot $plot, Carbon $imageDate, array $data): PlotRemoteSensing
    {
        return PlotRemoteSensing::updateOrCreate(
            [
                'plot_id' => $plot->id,
                'image_date' => $imageDate->format('Y-m-d'),
            ],
            $data
        );
    }

    /**
     * Get previous data point for trend calculation
     */
    public function getPreviousData(Plot $plot, Carbon $beforeDate): ?PlotRemoteSensing
    {
        return PlotRemoteSensing::where('plot_id', $plot->id)
            ->where('image_date', '<', $beforeDate)
            ->orderBy('image_date', 'desc')
            ->first();
    }

    /**
     * Delete duplicate entries (same plot_id and image_date)
     */
    public function deleteDuplicates(): int
    {
        $deleted = 0;
        
        $duplicates = \DB::table('plot_remote_sensing')
            ->select('plot_id', 'image_date', \DB::raw('COUNT(*) as count'))
            ->groupBy('plot_id', 'image_date')
            ->having('count', '>', 1)
            ->get();

        foreach ($duplicates as $duplicate) {
            $records = PlotRemoteSensing::where('plot_id', $duplicate->plot_id)
                ->where('image_date', $duplicate->image_date)
                ->orderBy('id', 'desc')
                ->get();

            // Keep the most recent one, delete the rest
            $toDelete = $records->skip(1);
            foreach ($toDelete as $record) {
                $record->delete();
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * Get plots with health issues
     */
    public function getPlotsWithIssues(): Collection
    {
        return PlotRemoteSensing::whereIn('health_status', ['poor', 'critical'])
            ->whereIn('id', function ($subQuery) {
                $subQuery->selectRaw('MAX(id)')
                    ->from('plot_remote_sensing')
                    ->groupBy('plot_id');
            })
            ->with('plot')
            ->get();
    }

    /**
     * Get count by health status
     */
    public function getHealthStatusCounts(Collection $plotIds): array
    {
        $latestData = $this->getLatestForPlots($plotIds);

        return [
            'excellent' => $latestData->where('health_status', 'excellent')->count(),
            'good' => $latestData->where('health_status', 'good')->count(),
            'moderate' => $latestData->where('health_status', 'moderate')->count(),
            'poor' => $latestData->where('health_status', 'poor')->count(),
            'critical' => $latestData->where('health_status', 'critical')->count(),
        ];
    }
}
