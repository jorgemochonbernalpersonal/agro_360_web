<?php

namespace App\Http\Controllers\Viticulturist;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\GrapeReceptionBatch;
use App\Models\Harvest;
use App\Models\HarvestDelivery;
use App\Models\PlotPlanting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HarvestsPdfController extends Controller
{
    public function export(Request $request)
    {
        $viticulturistId = Auth::id();
        $vintageYear     = (int) ($request->vintage ?? now()->year);

        // Harvest records del cuaderno agrupados por planting
        $harvestsByPlanting = Harvest::with(['activity', 'plotPlanting.grapeVariety', 'plotPlanting.plot'])
            ->whereHas('activity', fn ($q) => $q->where('viticulturist_id', $viticulturistId))
            ->where('vintage', $vintageYear)
            ->where('status', 'active')
            ->get()
            ->groupBy('plot_planting_id');

        // Lotes de bodega agrupados por planting
        $batchesByPlanting = GrapeReceptionBatch::with(['winery:id,name'])
            ->where('viticulturist_id', $viticulturistId)
            ->where('vintage_year', $vintageYear)
            ->get()
            ->groupBy('plot_planting_id');

        // Entregas declaradas agrupadas por planting
        $deliveriesByPlanting = HarvestDelivery::forViticulturist($viticulturistId)
            ->where('vintage_year', $vintageYear)
            ->orderBy('delivery_date')
            ->get()
            ->groupBy('plot_planting_id');

        // Union de plantings
        $plantingIds = $harvestsByPlanting->keys()
            ->merge($batchesByPlanting->keys())
            ->merge($deliveriesByPlanting->keys())
            ->filter()->unique();

        $plantings = PlotPlanting::with(['grapeVariety', 'plot'])
            ->whereIn('id', $plantingIds)
            ->get()
            ->keyBy('id');

        // Construir filas por planting
        $rows = $plantingIds->map(function ($plantingId) use (
            $plantings, $harvestsByPlanting, $batchesByPlanting, $deliveriesByPlanting
        ) {
            $planting   = $plantings->get($plantingId);
            $harvestKg  = (float) $harvestsByPlanting->get($plantingId, collect())->sum('total_weight');
            $wineryKg   = (float) $batchesByPlanting->get($plantingId, collect())->sum('total_weight_kg');
            $deliveries = $deliveriesByPlanting->get($plantingId, collect());
            // All declared kg (for display/audit column — includes linked deliveries)
            $declaredKg = (float) $deliveries->where('disqualified', false)->sum('delivered_kg');
            // Unlinked declared kg (not yet backed by a winery reception)
            $unlinkedDeclaredKg = (float) $deliveries
                ->where('disqualified', false)
                ->filter(fn ($d) => $d->harvest_id === null)
                ->sum('delivered_kg');

            $discrepancyKg  = ($harvestKg > 0 && $wineryKg > 0) ? abs($harvestKg - $wineryKg) : null;
            $discrepancyPct = ($discrepancyKg !== null && $harvestKg > 0)
                ? round($discrepancyKg / $harvestKg * 100, 1)
                : null;

            $cupoKg  = $planting?->effectiveHarvestLimitKg($vintageYear);
            // Cupo reference: winery confirmed + still-unlinked declared (no double-count)
            $totalRef = $wineryKg + $unlinkedDeclaredKg;
            $cupoPct = ($cupoKg && $cupoKg > 0 && $totalRef > 0)
                ? round($totalRef / $cupoKg * 100, 1)
                : null;

            return [
                'planting'        => $planting,
                'variety'         => $planting?->grapeVariety?->name ?? $planting?->name ?? '—',
                'plot'            => $planting?->plot?->name ?? '—',
                'area'            => $planting?->area_planted ? (float) $planting->area_planted : null,
                'harvest_kg'      => $harvestKg,
                'winery_kg'       => $wineryKg,
                'declared_kg'     => $declaredKg,
                'discrepancy_kg'  => $discrepancyKg,
                'discrepancy_pct' => $discrepancyPct,
                'cupo_kg'         => $cupoKg,
                'cupo_pct'        => $cupoPct,
                'cupo_exceeded'   => $cupoPct !== null && $cupoPct > 100,
                'deliveries'      => $deliveries,
                'batches'         => $batchesByPlanting->get($plantingId, collect()),
            ];
        })->filter()->values();

        // Totales globales
        $totals = [
            'harvest_kg'  => $rows->sum('harvest_kg'),
            'winery_kg'   => $rows->sum('winery_kg'),
            'declared_kg' => $rows->sum('declared_kg'),
            'plantings'   => $rows->count(),
        ];

        $pdf = Pdf::loadView('reports.viticulturist-vendimia', [
            'rows'            => $rows,
            'totals'          => $totals,
            'vintageYear'     => $vintageYear,
            'viticulturistName' => Auth::user()->name,
        ])
        ->setPaper('A4', 'landscape')
        ->setOption('defaultFont', 'DejaVu Sans')
        ->setOption('isHtml5ParserEnabled', true)
        ->setOption('isRemoteEnabled', false);

        $filename = 'vendimia_' . $vintageYear . '_' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }
}
