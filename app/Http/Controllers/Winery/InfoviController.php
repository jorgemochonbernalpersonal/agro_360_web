<?php

namespace App\Http\Controllers\Winery;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InfoviController extends Controller
{
    public function exportPdf()
    {
        $wineryId = Auth::id();
        $user     = Auth::user();

        $campaign = request('campaign',
            now()->month >= 8 ? now()->year : now()->year - 1
        );
        $campaign = (int) $campaign;

        $campaignStart = "{$campaign}-08-01";
        $campaignEnd   = ($campaign + 1) . "-07-31";

        $org       = $user->organization;
        $threshold = $this->buildThreshold($wineryId);

        $existencias = $this->buildCuadroExistencias($wineryId, $campaign);
        $produccion  = $this->buildCuadroProduccion($wineryId, $campaign);
        $ventas      = $this->buildCuadroVentas($wineryId, $campaignStart, $campaignEnd);
        $entradas    = $this->buildCuadroEntradas($wineryId, $campaign, $campaignStart, $campaignEnd);
        $mosto       = $this->buildCuadroMosto($wineryId, $campaign, $campaignStart, $campaignEnd);

        $wineLabels = [
            'red'        => 'Vino tinto tranquilo',
            'white'      => 'Vino blanco tranquilo',
            'rose'       => 'Vino rosado tranquilo',
            'sparkling'  => 'Vino espumoso',
            'fortified'  => 'Vino licoroso / generoso',
            'sweet'      => 'Vino dulce natural',
            'semi_sweet' => 'Vino semidulce',
            'other'      => 'Otros vinos',
        ];

        $pdf = Pdf::loadView('reports.infovi', compact(
            'existencias', 'produccion', 'ventas', 'entradas', 'mosto',
            'campaign', 'campaignStart', 'campaignEnd',
            'org', 'threshold', 'wineLabels', 'user'
        ))
        ->setPaper('A4', 'portrait')
        ->setOption('defaultFont', 'DejaVu Sans')
        ->setOption('isHtml5ParserEnabled', true)
        ->setOption('isRemoteEnabled', false);

        $filename = 'INFOVI_' . $campaign . '_' . ($campaign + 1) . '_' . now()->format('Ymd') . '.pdf';

        return $pdf->download($filename);
    }

    // ── Data builders (mirrors Infovi Livewire component) ─────────────────

    private function buildThreshold(int $wineryId): array
    {
        $vintageHl = DB::table('wines')
            ->where('user_id', $wineryId)
            ->whereNotNull('vintage')
            ->whereNotIn('status', ['cancelled'])
            ->whereNotNull('volume_liters')
            ->where('volume_liters', '>', 0)
            ->where('is_must', false)
            ->orderByDesc('vintage')
            ->limit(4)
            ->selectRaw('vintage, SUM(volume_liters) / 100.0 as hl')
            ->groupBy('vintage')
            ->pluck('hl');

        $avgHl    = $vintageHl->avg() ?? 0;
        $isLarge  = $avgHl >= 1000;

        return [
            'is_large'  => $isLarge,
            'avg_hl'    => round((float) $avgHl, 1),
            'campaigns' => $vintageHl->count(),
        ];
    }

    private function buildCuadroExistencias(int $wineryId, int $campaign): array
    {
        $snapshotDate = DB::table('wine_stock_snapshots')
            ->where('user_id', $wineryId)
            ->where('snapshot_date', '<=', ($campaign + 1) . '-07-31')
            ->max('snapshot_date');

        if (!$snapshotDate) {
            return [];
        }

        return DB::table('wine_stock_snapshots as s')
            ->join('wines as w', 'w.id', '=', 's.wine_id')
            ->where('s.user_id', $wineryId)
            ->where('s.snapshot_date', $snapshotDate)
            ->where('s.is_must', false)
            ->selectRaw('w.wine_type, SUM(s.quantity_liters) / 100.0 as hl, COUNT(DISTINCT s.wine_id) as cnt')
            ->groupBy('w.wine_type')
            ->get()
            ->keyBy('wine_type')
            ->toArray();
    }

    private function buildCuadroProduccion(int $wineryId, int $campaign): array
    {
        return DB::table('wines as w')
            ->where('w.user_id', $wineryId)
            ->where('w.vintage', $campaign)
            ->whereNotIn('w.status', ['cancelled'])
            ->where('w.is_must', false)
            ->whereNotNull('w.volume_liters')
            ->where('w.volume_liters', '>', 0)
            ->selectRaw('w.wine_type, SUM(w.volume_liters) / 100.0 as hl, COUNT(*) as cnt')
            ->groupBy('w.wine_type')
            ->get()
            ->keyBy('wine_type')
            ->toArray();
    }

    private function buildCuadroVentas(int $wineryId, string $start, string $end): array
    {
        $bottleMl = [
            '187' => 187, '375' => 375, '500' => 500, '750' => 750,
            '1000' => 1000, '1500' => 1500, '3000' => 3000, '5000' => 5000,
        ];

        $rows = DB::table('invoice_items as ii')
            ->join('invoices as i', 'i.id', '=', 'ii.invoice_id')
            ->join('wines as w', 'w.id', '=', 'ii.wine_id')
            ->where('i.user_id', $wineryId)
            ->whereBetween('i.issue_date', [$start, $end])
            ->where('w.is_must', false)
            ->whereNotNull('ii.wine_id')
            ->selectRaw('w.wine_type, ii.bottle_size, SUM(ii.quantity) as bottles')
            ->groupBy('w.wine_type', 'ii.bottle_size')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $ml = $bottleMl[$row->bottle_size] ?? 750;
            $hl = ($row->bottles * $ml) / 100000;
            $type = $row->wine_type;
            if (!isset($result[$type])) {
                $result[$type] = (object) ['wine_type' => $type, 'hl' => 0, 'bottles' => 0];
            }
            $result[$type]->hl      += $hl;
            $result[$type]->bottles += $row->bottles;
        }

        return $result;
    }

    private function buildCuadroEntradas(int $wineryId, int $campaign, string $start, string $end): array
    {
        $harvests = DB::table('harvests')
            ->where('winery_id', $wineryId)
            ->where('vintage', $campaign)
            ->whereIn('status', ['active', 'closed'])
            ->selectRaw('SUM(total_weight) as kg')
            ->value('kg') ?? 0;

        return [
            'uva_propia_kg' => (float) $harvests,
            'uva_propia_hl' => round((float) $harvests / 130, 2),
        ];
    }

    private function buildCuadroMosto(int $wineryId, int $campaign, string $start, string $end): array
    {
        $snapshotDate = DB::table('wine_stock_snapshots')
            ->where('user_id', $wineryId)
            ->where('snapshot_date', '<=', ($campaign + 1) . '-07-31')
            ->max('snapshot_date');

        $existencias = $snapshotDate
            ? (float) DB::table('wine_stock_snapshots')
                ->where('user_id', $wineryId)
                ->where('snapshot_date', $snapshotDate)
                ->where('is_must', true)
                ->sum(DB::raw('quantity_liters / 100.0'))
            : 0;

        $producido = (float) DB::table('wines')
            ->where('user_id', $wineryId)
            ->where('vintage', $campaign)
            ->where('is_must', true)
            ->whereNotIn('status', ['cancelled'])
            ->sum(DB::raw('COALESCE(volume_liters,0) / 100.0'));

        return [
            'has_data'    => $existencias > 0 || $producido > 0,
            'existencias' => round($existencias, 2),
            'producido'   => round($producido, 2),
        ];
    }
}
