<?php

namespace App\Http\Controllers\Winery;

use App\Exports\HarvestReceptionExport;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Harvest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class HarvestReceptionController extends Controller
{
    public function exportPdf(Request $request)
    {
        $wineryId = Auth::id();
        $harvests = $this->buildQuery($wineryId, $request)->get();

        $campaign = $request->campaign
            ? Campaign::find($request->campaign)
            : null;
        $campaignYear = $campaign?->year;

        $activeHarvests = $harvests->where('status', 'active');
        $stats = [
            'total_kg' => $activeHarvests->sum(fn ($h) => (float) $h->total_weight),
            'total_count' => $activeHarvests->count(),
            'disqualified_kg' => $activeHarvests->where('disqualified', true)->sum(fn ($h) => (float) $h->total_weight),
            'viticulturists' => $activeHarvests->map(fn ($h) => $h->batch?->viticulturist_id)->unique()->filter()->count(),
        ];

        $pdf = Pdf::loadView('reports.harvest-reception', [
            'harvests' => $harvests,
            'stats' => $stats,
            'campaignYear' => $campaignYear,
            'wineryName' => Auth::user()->name,
        ])
            ->setPaper('A4', 'landscape')
            ->setOption('defaultFont', 'DejaVu Sans')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false);

        $filename = 'recepciones_uva_'.now()->format('Y-m-d').'.pdf';

        return $pdf->download($filename);
    }

    public function exportExcel(Request $request)
    {
        $wineryId = Auth::id();
        $filename = 'recepciones_uva_'.now()->format('Y-m-d').'.xlsx';

        return Excel::download(
            new HarvestReceptionExport(
                wineryId: $wineryId,
                campaignFilter: $request->campaign ?? '',
                viticulturistFilter: $request->viticulturist ?? '',
                disqualifiedFilter: $request->disqualified ?? '',
            ),
            $filename
        );
    }

    public function exportPdfSingle(Harvest $harvest)
    {
        $wineryId = Auth::id();
        abort_unless($harvest->winery_id === $wineryId, 403);

        $harvest->load([
            'plotPlanting.grapeVariety',
            'plotPlanting.plot',
            'batch.viticulturist',
            'container',
        ]);

        $pdf = Pdf::loadView('reports.harvest-reception-single', [
            'harvest' => $harvest,
            'wineryName' => Auth::user()->name,
        ])
            ->setPaper('A4', 'portrait')
            ->setOption('defaultFont', 'DejaVu Sans')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false);

        $filename = 'recepcion_'.$harvest->id.'_'.now()->format('Y-m-d').'.pdf';

        return $pdf->download($filename);
    }

    protected function buildQuery(int $wineryId, Request $request)
    {
        $query = Harvest::with([
            'plotPlanting.grapeVariety',
            'plotPlanting.plot',
            'batch.viticulturist',
            'container',
        ])->where('winery_id', $wineryId);

        if ($request->campaign) {
            $query->whereHas('batch', fn (Builder $q) => $q->where('campaign_id', $request->campaign)
            );
        }

        if ($request->viticulturist) {
            $query->whereHas('batch', fn (Builder $q) => $q->where('viticulturist_id', $request->viticulturist)
            );
        }

        if ($request->disqualified !== null && $request->disqualified !== '') {
            $query->where('disqualified', (bool) $request->disqualified);
        }

        return $query->orderByDesc('harvest_start_date');
    }
}
