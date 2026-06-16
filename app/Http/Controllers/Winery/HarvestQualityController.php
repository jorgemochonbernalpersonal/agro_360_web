<?php

namespace App\Http\Controllers\Winery;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Harvest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HarvestQualityController extends Controller
{
    public function exportPdf(Request $request)
    {
        $wineryId = Auth::id();

        $query = Harvest::with(['batch.viticulturist', 'plotPlanting.grapeVariety'])
            ->where('winery_id', $wineryId)
            ->where('status', 'active')
            ->where('disqualified', false);

        if ($request->campaign) {
            $query->whereHas('batch', fn (Builder $q) => $q->where('campaign_id', $request->campaign));
        }

        if ($request->viticulturist) {
            $query->whereHas('batch', fn (Builder $q) => $q->where('viticulturist_id', $request->viticulturist));
        }

        $harvests = $query->get();

        $campaign = $request->campaign ? Campaign::find($request->campaign) : null;
        $campaignYear = $campaign?->year;

        // Build stats
        $withAlcohol = $harvests->whereNotNull('potential_alcohol');
        $withBaume = $harvests->whereNotNull('baume_degree');
        $withBrix = $harvests->whereNotNull('brix_degree');
        $withAcidity = $harvests->whereNotNull('acidity_level');
        $withPh = $harvests->whereNotNull('ph_level');

        $globalStats = [
            'total_kg' => $harvests->sum(fn ($h) => (float) $h->total_weight),
            'total_entries' => $harvests->count(),
            'avg_alcohol' => $withAlcohol->count() ? round($withAlcohol->avg(fn ($h) => $h->potential_alcohol), 2) : null,
            'avg_baume' => $withBaume->count() ? round($withBaume->avg(fn ($h) => $h->baume_degree), 2) : null,
            'avg_brix' => $withBrix->count() ? round($withBrix->avg(fn ($h) => $h->brix_degree), 2) : null,
            'avg_acidity' => $withAcidity->count() ? round($withAcidity->avg(fn ($h) => $h->acidity_level), 2) : null,
            'avg_ph' => $withPh->count() ? round($withPh->avg(fn ($h) => $h->ph_level), 2) : null,
        ];

        // By viticulturist
        $byViticulturist = $harvests
            ->groupBy(fn ($h) => $h->batch->viticulturist_id ?? 'unknown')
            ->map(function ($group) {
                $wa = $group->whereNotNull('potential_alcohol');
                $wb = $group->whereNotNull('baume_degree');
                $wx = $group->whereNotNull('brix_degree');
                $wc = $group->whereNotNull('acidity_level');
                $wp = $group->whereNotNull('ph_level');
                $hd = $group->whereNotNull('health_status')->groupBy('health_status')->map->count()->sortDesc();

                return [
                    'label' => $group->first()->batch?->viticulturist->name ?? '—',
                    'total_kg' => $group->sum(fn ($h) => (float) $h->total_weight),
                    'count' => $group->count(),
                    'avg_alcohol' => $wa->count() ? round($wa->avg(fn ($h) => $h->potential_alcohol), 2) : null,
                    'avg_baume' => $wb->count() ? round($wb->avg(fn ($h) => $h->baume_degree), 2) : null,
                    'avg_brix' => $wx->count() ? round($wx->avg(fn ($h) => $h->brix_degree), 2) : null,
                    'avg_acidity' => $wc->count() ? round($wc->avg(fn ($h) => $h->acidity_level), 2) : null,
                    'avg_ph' => $wp->count() ? round($wp->avg(fn ($h) => $h->ph_level), 2) : null,
                    'health_dist' => $hd,
                ];
            })
            ->sortByDesc('total_kg')
            ->values();

        // By variety
        $byVariety = $harvests
            ->groupBy(fn ($h) => $h->plotPlanting?->grapeVariety->name ?? '—')
            ->map(function ($group, $variety) {
                $wa = $group->whereNotNull('potential_alcohol');
                $wb = $group->whereNotNull('baume_degree');
                $wx = $group->whereNotNull('brix_degree');
                $wc = $group->whereNotNull('acidity_level');
                $wp = $group->whereNotNull('ph_level');

                return [
                    'label' => $variety,
                    'total_kg' => $group->sum(fn ($h) => (float) $h->total_weight),
                    'count' => $group->count(),
                    'avg_alcohol' => $wa->count() ? round($wa->avg(fn ($h) => $h->potential_alcohol), 2) : null,
                    'avg_baume' => $wb->count() ? round($wb->avg(fn ($h) => $h->baume_degree), 2) : null,
                    'avg_brix' => $wx->count() ? round($wx->avg(fn ($h) => $h->brix_degree), 2) : null,
                    'avg_acidity' => $wc->count() ? round($wc->avg(fn ($h) => $h->acidity_level), 2) : null,
                    'avg_ph' => $wp->count() ? round($wp->avg(fn ($h) => $h->ph_level), 2) : null,
                ];
            })
            ->sortByDesc('total_kg')
            ->values();

        $pdf = Pdf::loadView('reports.harvest-quality', [
            'globalStats' => $globalStats,
            'byViticulturist' => $byViticulturist,
            'byVariety' => $byVariety,
            'campaignYear' => $campaignYear,
            'wineryName' => Auth::user()->name,
        ])
            ->setPaper('A4', 'landscape')
            ->setOption('defaultFont', 'DejaVu Sans')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false);

        $filename = 'calidad_vendimia_'.($campaignYear ?? now()->year).'.pdf';

        return $pdf->download($filename);
    }
}
