<?php

namespace App\Repositories;

use App\Models\Plot;
use App\Models\PlotRemoteSensing;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Repository for PlotRemoteSensing database operations.
 * All per-sigpac methods accept ?int $plotSigpacId (multipart_plot_sigpac_id).
 * When provided, queries are scoped to that specific sigpac parcel.
 */
class PlotRemoteSensingRepository
{
    /**
     * Base query scoped to plot + optional sigpac parcel.
     */
    private function baseQuery(Plot $plot, ?int $plotSigpacId = null)
    {
        $q = PlotRemoteSensing::where('plot_id', $plot->id);

        if ($plotSigpacId !== null) {
            $q->where('multipart_plot_sigpac_id', $plotSigpacId);
        }

        return $q;
    }

    /**
     * Get the latest remote sensing data for a plot (optionally scoped to a sigpac parcel).
     */
    public function getLatestForPlot(Plot $plot, ?int $plotSigpacId = null): ?PlotRemoteSensing
    {
        return $this->baseQuery($plot, $plotSigpacId)
            ->orderBy('image_date', 'desc')
            ->first();
    }

    /**
     * Get latest data for today.
     */
    public function getTodayForPlot(Plot $plot, ?int $plotSigpacId = null): ?PlotRemoteSensing
    {
        return $this->baseQuery($plot, $plotSigpacId)
            ->whereDate('image_date', today())
            ->orderBy('id', 'desc')
            ->first();
    }

    /**
     * Get historical data for a plot (optionally scoped to a sigpac parcel).
     */
    public function getHistoricalForPlot(Plot $plot, int $days = 90, ?int $plotSigpacId = null): Collection
    {
        return $this->baseQuery($plot, $plotSigpacId)
            ->where('image_date', '>=', now()->subDays($days))
            ->orderBy('image_date', 'desc')
            ->get();
    }

    /**
     * Get data from same period last year.
     */
    public function getLastYearDataForPlot(Plot $plot, int $month, ?int $plotSigpacId = null): ?PlotRemoteSensing
    {
        return $this->baseQuery($plot, $plotSigpacId)
            ->whereMonth('image_date', $month)
            ->whereYear('image_date', now()->year - 1)
            ->orderBy('image_date', 'desc')
            ->first();
    }

    /**
     * Get all latest data for multiple plots (optimized, no sigpac filter — used for dashboard stats).
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
     * Check if data exists for a specific date (optionally scoped to a sigpac parcel).
     */
    public function existsForDate(Plot $plot, Carbon $date, ?int $plotSigpacId = null): bool
    {
        return $this->baseQuery($plot, $plotSigpacId)
            ->whereDate('image_date', $date)
            ->exists();
    }

    /**
     * Create or update remote sensing data.
     * When $plotSigpacId is provided, the unique match key is (plot_id, multipart_plot_sigpac_id, image_date).
     * When null, matches legacy rows by (plot_id, image_date) where sigpac parcel IS NULL.
     */
    public function createOrUpdate(Plot $plot, Carbon $imageDate, array $data, ?int $plotSigpacId = null): PlotRemoteSensing
    {
        $matchKeys = [
            'plot_id'    => $plot->id,
            'image_date' => $imageDate->format('Y-m-d'),
        ];

        if ($plotSigpacId !== null) {
            $matchKeys['multipart_plot_sigpac_id'] = $plotSigpacId;
        }

        // Ensure the sigpac parcel FK is persisted in the row data too
        $data['multipart_plot_sigpac_id'] = $plotSigpacId;

        return PlotRemoteSensing::updateOrCreate($matchKeys, $data);
    }

    /**
     * Get previous data point for trend calculation.
     */
    public function getPreviousData(Plot $plot, Carbon $beforeDate, ?int $plotSigpacId = null): ?PlotRemoteSensing
    {
        return $this->baseQuery($plot, $plotSigpacId)
            ->where('image_date', '<', $beforeDate)
            ->orderBy('image_date', 'desc')
            ->first();
    }

    /**
     * Delete duplicate entries (same plot_id, multipart_plot_sigpac_id, image_date).
     */
    public function deleteDuplicates(): int
    {
        $deleted = 0;

        $duplicates = \DB::table('plot_remote_sensing')
            ->select('plot_id', 'multipart_plot_sigpac_id', 'image_date', \DB::raw('COUNT(*) as count'))
            ->groupBy('plot_id', 'multipart_plot_sigpac_id', 'image_date')
            ->having('count', '>', 1)
            ->get();

        foreach ($duplicates as $duplicate) {
            $records = PlotRemoteSensing::where('plot_id', $duplicate->plot_id)
                ->where('image_date', $duplicate->image_date)
                ->where(function ($q) use ($duplicate) {
                    $duplicate->multipart_plot_sigpac_id === null
                        ? $q->whereNull('multipart_plot_sigpac_id')
                        : $q->where('multipart_plot_sigpac_id', $duplicate->multipart_plot_sigpac_id);
                })
                ->orderBy('id', 'desc')
                ->get();

            foreach ($records->skip(1) as $record) {
                $record->delete();
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * Get plots with health issues.
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
     * Get count by health status.
     */
    public function getHealthStatusCounts(Collection $plotIds): array
    {
        $latestData = $this->getLatestForPlots($plotIds);

        return [
            'excellent' => $latestData->where('health_status', 'excellent')->count(),
            'good'      => $latestData->where('health_status', 'good')->count(),
            'moderate'  => $latestData->where('health_status', 'moderate')->count(),
            'poor'      => $latestData->where('health_status', 'poor')->count(),
            'critical'  => $latestData->where('health_status', 'critical')->count(),
        ];
    }
}
