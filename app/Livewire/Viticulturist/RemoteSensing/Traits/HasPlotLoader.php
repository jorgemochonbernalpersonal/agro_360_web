<?php

namespace App\Livewire\Viticulturist\RemoteSensing\Traits;

use App\Models\Plot;

trait HasPlotLoader
{
    public function loadStats(): void
    {
        $plots = collect($this->plots);
        $plotIds = $plots->pluck('id');

        if ($plotIds->isEmpty()) {
            $this->stats = [
                'total_plots' => 0, 'with_data' => 0, 'average_ndvi' => 0,
                'excellent' => 0, 'good' => 0, 'moderate' => 0, 'poor' => 0, 'critical' => 0,
            ];

            return;
        }

        $latestNdviData = \App\Models\PlotRemoteSensing::whereIn('plot_id', $plotIds)
            ->whereIn('id', function ($subQuery) use ($plotIds) {
                $subQuery->selectRaw('MAX(id)')->from('plot_remote_sensing')
                    ->whereIn('plot_id', $plotIds)->groupBy('plot_id');
            })
            ->get()
            ->keyBy('plot_id');

        $excellent = $good = $moderate = $poor = $critical = 0;
        $totalNdvi = 0;
        $ndviCount = 0;

        foreach ($plots as $plot) {
            $data = $latestNdviData->get($plot->id);
            if ($data) {
                $ndviCount++;
                $totalNdvi += $data->ndvi_mean ?? 0;
                match ($data->health_status) {
                    'excellent' => $excellent++,
                    'good' => $good++,
                    'moderate' => $moderate++,
                    'poor' => $poor++,
                    'critical' => $critical++,
                    default => null,
                };
            }
        }

        $this->stats = [
            'total_plots' => $plots->count(),
            'with_data' => $ndviCount,
            'average_ndvi' => $ndviCount > 0 ? round($totalNdvi / $ndviCount, 3) : 0,
            'excellent' => $excellent, 'good' => $good, 'moderate' => $moderate,
            'poor' => $poor, 'critical' => $critical,
            'alerts' => $poor + $critical,
        ];
    }

    private function loadAllSigpacs(): void
    {
        $plotIds = collect($this->plots)->pluck('id');

        if ($plotIds->isEmpty()) {
            $this->allSigpacs = [];

            return;
        }

        $this->allSigpacs = \App\Models\MultipartPlotSigpac::whereIn('plot_id', $plotIds)
            ->whereNotNull('plot_geometry_id')
            ->with(['sigpacCode', 'plotGeometry', 'plot'])
            ->get()
            ->map(function ($mps) {
                $geometry = $mps->plotGeometry;
                $centroid = $geometry?->getCentroidAsArray();
                $area = $geometry ? $this->polygonAreaHa($geometry->getCoordinatesAsArray()) : null;

                return [
                    'id' => $mps->id,
                    'plot_id' => $mps->plot_id,
                    'plot_name' => $mps->plot->name ?? 'Sin parcela',
                    'sigpac_code' => $mps->sigpacCode->code ?? 'Sin código',
                    'display_name' => ($mps->plot->name ?? 'Parcela').' · '.($mps->sigpacCode->code ?? 'Recinto '.$mps->id),
                    'area_ha' => $area ? round($area, 2) : 0,
                    'centroid' => $centroid,
                ];
            })
            ->toArray();
    }

    private function deriveSelectedPlot(): void
    {
        if (! $this->selectedSigpacId) {
            $this->selectedPlot = null;

            return;
        }

        $sigpac = collect($this->allSigpacs)->firstWhere('id', $this->selectedSigpacId);
        $this->selectedPlot = $sigpac ? Plot::find($sigpac['plot_id']) : null;
    }

    /**
     * Shoelace formula with per-latitude metre correction.
     * Returns area in hectares from an array of ['lat'=>..., 'lng'=>...] points.
     */
    private function polygonAreaHa(array $points): float
    {
        $n = count($points);
        if ($n < 3) {
            return 0.0;
        }

        $latSum = array_sum(array_column($points, 'lat'));
        $latRad = deg2rad($latSum / $n);
        $mLat = 111320.0;
        $mLng = 111320.0 * cos($latRad);
        $area = 0.0;

        for ($i = 0; $i < $n; $i++) {
            $j = ($i + 1) % $n;
            $xi = $points[$i]['lng'] * $mLng;
            $yi = $points[$i]['lat'] * $mLat;
            $xj = $points[$j]['lng'] * $mLng;
            $yj = $points[$j]['lat'] * $mLat;
            $area += $xi * $yj - $xj * $yi;
        }

        return round(abs($area) / 2 / 10000, 2);
    }
}
