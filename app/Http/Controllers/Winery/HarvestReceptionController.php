<?php

namespace App\Http\Controllers\Winery;

use App\Exports\HarvestReceptionExport;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Harvest;
use App\Models\WineryViticulturist;
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

        $campaign     = $request->campaign
            ? Campaign::find($request->campaign)
            : null;
        $campaignYear = $campaign?->year;

        $stats = [
            'total_kg'        => $harvests->where('status', 'active')->sum(fn($h) => (float) $h->total_weight),
            'total_count'     => $harvests->where('status', 'active')->count(),
            'disqualified_kg' => $harvests->where('status', 'active')->where('disqualified', true)->sum(fn($h) => (float) $h->total_weight),
            'viticulturists'  => $harvests->map(fn($h) => $h->activity?->viticulturist_id)->unique()->filter()->count(),
        ];

        $pdf = Pdf::loadView('reports.harvest-reception', [
            'harvests'     => $harvests,
            'stats'        => $stats,
            'campaignYear' => $campaignYear,
            'wineryName'   => Auth::user()->name,
        ])
        ->setPaper('A4', 'landscape')
        ->setOption('defaultFont', 'DejaVu Sans')
        ->setOption('isHtml5ParserEnabled', true)
        ->setOption('isRemoteEnabled', false);

        $filename = 'recepciones_uva_' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    public function exportExcel(Request $request)
    {
        $wineryId = Auth::id();
        $filename = 'recepciones_uva_' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new HarvestReceptionExport(
                wineryId:             $wineryId,
                campaignFilter:       $request->campaign ?? '',
                viticulturistFilter:  $request->viticulturist ?? '',
                disqualifiedFilter:   $request->disqualified ?? '',
            ),
            $filename
        );
    }

    public function exportPdfSingle(Harvest $harvest)
    {
        $wineryId         = Auth::id();
        $viticulturistIds = WineryViticulturist::where('winery_id', $wineryId)->pluck('viticulturist_id');
        $campaignIds      = Campaign::forViticulturist($wineryId)->pluck('id');

        $exists = Harvest::where('id', $harvest->id)
            ->whereHas('activity', fn($q) =>
                $q->whereIn('viticulturist_id', $viticulturistIds)
                  ->whereIn('campaign_id', $campaignIds)
            )->exists();

        abort_unless($exists, 403);

        $harvest->load([
            'plotPlanting.grapeVariety',
            'plotPlanting.plot',
            'activity.viticulturist',
            'activity.campaign',
            'container',
        ]);

        $pdf = Pdf::loadView('reports.harvest-reception-single', [
            'harvest'    => $harvest,
            'wineryName' => Auth::user()->name,
        ])
        ->setPaper('A4', 'portrait')
        ->setOption('defaultFont', 'DejaVu Sans')
        ->setOption('isHtml5ParserEnabled', true)
        ->setOption('isRemoteEnabled', false);

        $filename = 'recepcion_' . $harvest->id . '_' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    protected function buildQuery(int $wineryId, Request $request)
    {
        $viticulturistIds = WineryViticulturist::where('winery_id', $wineryId)->pluck('viticulturist_id');
        $campaignIds      = Campaign::forViticulturist($wineryId)->pluck('id');

        $query = Harvest::with([
            'plotPlanting.grapeVariety',
            'plotPlanting.plot',
            'activity.viticulturist',
            'activity.campaign',
            'container',
        ])->whereHas('activity', fn(Builder $q) =>
            $q->whereIn('viticulturist_id', $viticulturistIds)
              ->whereIn('campaign_id', $campaignIds)
        );

        if ($request->campaign) {
            $query->whereHas('activity', fn(Builder $q) =>
                $q->where('campaign_id', $request->campaign)
            );
        }

        if ($request->viticulturist) {
            $query->whereHas('activity', fn(Builder $q) =>
                $q->where('viticulturist_id', $request->viticulturist)
            );
        }

        if ($request->disqualified !== null && $request->disqualified !== '') {
            $query->where('disqualified', (bool) $request->disqualified);
        }

        return $query->orderByDesc('harvest_start_date');
    }
}
