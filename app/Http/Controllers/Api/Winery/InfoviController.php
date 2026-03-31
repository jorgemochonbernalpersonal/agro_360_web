<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Controller;
use App\Livewire\Winery\Silicie\Infovi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InfoviController extends Controller
{
    // ─── GET /winery/infovi ───────────────────────────────────────────────────
    // Cuadros completos INFOVI para una campaña

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $request->validate(['campaign' => 'nullable|integer|min:1990|max:' . now()->year]);

        $wineryId = $user->id;
        $campaign = $request->integer(
            'campaign',
            now()->month >= 8 ? now()->year : now()->year - 1
        );

        $campaignStart = "{$campaign}-08-01";
        $campaignEnd   = ($campaign + 1) . "-07-31";

        $campaigns = DB::table('harvests')
            ->where('winery_id', $wineryId)
            ->selectRaw('DISTINCT vintage')
            ->orderByDesc('vintage')
            ->pluck('vintage')
            ->map(fn ($v) => (int) $v >= 8 ? $v : $v - 1)
            ->unique()
            ->values();

        if ($campaigns->isEmpty()) {
            $campaigns = collect([now()->month >= 8 ? now()->year : now()->year - 1]);
        }

        return response()->json([
            'data' => [
                'campaign'        => $campaign,
                'campaign_start'  => $campaignStart,
                'campaign_end'    => $campaignEnd,
                'campaigns'       => $campaigns,
                'threshold'       => $this->buildThreshold($wineryId),
                'existencias'     => $this->buildCuadroExistencias($wineryId, $campaign),
                'produccion'      => $this->buildCuadroProduccion($wineryId, $campaign),
                'ventas'          => $this->buildCuadroVentas($wineryId, $campaignStart, $campaignEnd),
                'entradas'        => $this->buildCuadroEntradas($wineryId, $campaign, $campaignStart, $campaignEnd),
                'balance'         => $this->buildBalanceSheet($wineryId, $campaign, $campaignStart, $campaignEnd),
                'mosto'           => $this->buildCuadroMosto($wineryId, $campaign, $campaignStart, $campaignEnd),
            ],
        ]);
    }

    // ─── GET /winery/infovi/threshold ────────────────────────────────────────

    public function threshold(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        return response()->json([
            'data' => $this->buildThreshold($user->id),
        ]);
    }

    // ─── Private: Threshold ───────────────────────────────────────────────────

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
            ->groupBy('vintage')
            ->selectRaw('vintage, SUM(volume_liters) / 100 as hl')
            ->pluck('hl', 'vintage');

        $campaigns = $vintageHl->count();
        $avgHl     = $campaigns > 0 ? round($vintageHl->sum() / $campaigns, 1) : 0;
        $isLarge   = $avgHl >= 1000;

        return [
            'avg_hl'         => $avgHl,
            'campaigns'      => $campaigns,
            'is_large'       => $isLarge,
            'frequency'      => $isLarge ? 'monthly' : 'semi_annual',
            'by_vintage'     => $vintageHl,
            'next_deadlines' => $this->buildDeadlines($isLarge),
        ];
    }

    private function buildDeadlines(bool $isLarge): array
    {
        $now       = now();
        $deadlines = [];

        if ($isLarge) {
            $nextMonth   = $now->copy()->addMonth()->startOfMonth();
            $deadlines[] = [
                'label' => 'Declaración mensual ' . $nextMonth->translatedFormat('F Y'),
                'date'  => $nextMonth->copy()->setDay(19)->toDateString(),
                'type'  => 'monthly',
            ];
        } else {
            $candidates = [
                now()->year . '-08-19',
                now()->year . '-12-19',
                (now()->year + 1) . '-08-19',
                (now()->year + 1) . '-12-19',
            ];
            foreach ($candidates as $d) {
                if ($d >= $now->toDateString()) {
                    $date  = \Carbon\Carbon::parse($d);
                    $month = $date->month === 12 ? 'noviembre' : 'julio';
                    $deadlines[] = [
                        'label' => 'Declaración ampliada ' . $month . ' ' . $date->year,
                        'date'  => $d,
                        'type'  => 'semi_annual',
                    ];
                    if (count($deadlines) >= 2) break;
                }
            }
        }

        return $deadlines;
    }

    // ─── Private: Cuadro 1 — Existencias ─────────────────────────────────────

    private function buildCuadroExistencias(int $wineryId, int $campaign): array
    {
        $snapshotDate = DB::table('wine_stock_snapshots')
            ->where('user_id', $wineryId)
            ->whereDate('snapshot_date', '>=', "{$campaign}-08-01")
            ->whereDate('snapshot_date', '<=', ($campaign + 1) . "-07-31")
            ->orderByDesc('snapshot_date')
            ->value('snapshot_date');

        if ($snapshotDate) {
            $rows = DB::table('wine_stock_snapshots as wss')
                ->where('wss.user_id', $wineryId)
                ->where('wss.snapshot_date', $snapshotDate)
                ->where('wss.is_must', false)
                ->select(['wss.wine_type', DB::raw('SUM(wss.quantity_liters) / 100 as hl'), DB::raw('COUNT(DISTINCT wss.wine_id) as wine_count')])
                ->groupBy('wss.wine_type')
                ->get();
        } else {
            $rows = DB::table('container_current_states as ccs')
                ->join('containers as c', 'c.id', '=', 'ccs.container_id')
                ->join('wines as w', 'w.id', '=', 'ccs.wine_id')
                ->where('c.user_id', $wineryId)
                ->where('ccs.current_quantity', '>', 0)
                ->where('w.is_must', false)
                ->select(['w.wine_type', DB::raw('SUM(ccs.current_quantity) / 100 as hl'), DB::raw('COUNT(DISTINCT w.id) as wine_count')])
                ->groupBy('w.wine_type')
                ->get();
        }

        return $this->buildCategoryRows($rows, $snapshotDate);
    }

    // ─── Private: Cuadro 2 — Producción ──────────────────────────────────────

    private function buildCuadroProduccion(int $wineryId, int $campaign): array
    {
        $rows = DB::table('wines as w')
            ->where('w.user_id', $wineryId)
            ->where('w.vintage', $campaign)
            ->whereNotIn('w.status', ['cancelled'])
            ->whereNotNull('w.volume_liters')
            ->where('w.volume_liters', '>', 0)
            ->where('w.is_must', false)
            ->select(['w.wine_type', DB::raw('SUM(w.volume_liters) / 100 as hl'), DB::raw('COUNT(*) as wine_count')])
            ->groupBy('w.wine_type')
            ->get();

        return $this->buildCategoryRows($rows, null);
    }

    // ─── Private: Cuadro 3 — Ventas ──────────────────────────────────────────

    private function buildCuadroVentas(int $wineryId, string $from, string $to): array
    {
        $lotRows = DB::table('invoice_items as ii')
            ->join('invoices as i', 'i.id', '=', 'ii.invoice_id')
            ->join('wine_lots as pl', 'pl.id', '=', 'ii.wine_lot_id')
            ->join('wines as w', 'w.id', '=', 'pl.wine_id')
            ->where('i.user_id', $wineryId)
            ->whereIn('i.status', ['sent', 'paid'])
            ->whereDate('i.invoice_date', '>=', $from)
            ->whereDate('i.invoice_date', '<=', $to)
            ->whereNotNull('ii.wine_lot_id')
            ->whereNotNull('pl.bottle_format')
            ->where('w.is_must', false)
            ->select([
                'w.wine_type',
                DB::raw('SUM(ii.quantity * CAST(pl.bottle_format AS DECIMAL(10,0)) / 1000000) as hl'),
                DB::raw('SUM(ii.quantity) as bottles'),
            ])
            ->groupBy('w.wine_type')
            ->get();

        $invoiceTotals = DB::table('invoices as i')
            ->where('i.user_id', $wineryId)
            ->whereIn('i.status', ['sent', 'paid'])
            ->whereDate('i.invoice_date', '>=', $from)
            ->whereDate('i.invoice_date', '<=', $to)
            ->selectRaw('COUNT(*) as total_invoices, SUM(total_amount) as total_amount')
            ->first();

        $result = [];
        $totalHl = $totalBottles = 0;

        foreach (Infovi::WINE_CATEGORIES as $type => $label) {
            $row      = $lotRows->firstWhere('wine_type', $type);
            $hl       = $row ? round((float) $row->hl, 3) : 0;
            $bottles  = $row ? (int) $row->bottles : 0;
            $result[] = compact('type', 'label', 'hl', 'bottles');
            $totalHl      += $hl;
            $totalBottles += $bottles;
        }

        return [
            'rows'           => $result,
            'total_hl'       => round($totalHl, 3),
            'total_bottles'  => $totalBottles,
            'total_invoices' => (int) ($invoiceTotals->total_invoices ?? 0),
            'total_amount'   => (float) ($invoiceTotals->total_amount ?? 0),
        ];
    }

    // ─── Private: Cuadro 4 — Entradas ────────────────────────────────────────

    private function buildCuadroEntradas(int $wineryId, int $campaign, string $from, string $to): array
    {
        $kgPropia = (float) DB::table('harvests')
            ->where('winery_id', $wineryId)
            ->where('vintage', $campaign)
            ->sum('total_weight');

        $externas = DB::table('external_grapes')
            ->where('user_id', $wineryId)
            ->whereDate('entry_date', '>=', $from)
            ->whereDate('entry_date', '<=', $to)
            ->select(['grape_type', DB::raw('SUM(total_weight_kg) as total_kg')])
            ->groupBy('grape_type')
            ->get();

        return [
            'kg_propia'      => $kgPropia,
            'kg_comprada'    => (float) ($externas->firstWhere('grape_type', 'grapes')?->total_kg ?? 0),
            'hl_mosto'       => round((float) ($externas->firstWhere('grape_type', 'must')?->total_kg ?? 0) / 100, 3),
            'hl_vino_granel' => round((float) ($externas->firstWhere('grape_type', 'bulk_wine')?->total_kg ?? 0) / 100, 3),
        ];
    }

    // ─── Private: Balance sheet ───────────────────────────────────────────────

    private function buildBalanceSheet(int $wineryId, int $campaign, string $campaignStart, string $campaignEnd): array
    {
        $openingSnapshotDate = DB::table('wine_stock_snapshots')
            ->where('user_id', $wineryId)->whereDate('snapshot_date', '<', $campaignStart)
            ->where('is_must', false)->orderByDesc('snapshot_date')->value('snapshot_date');

        $closingSnapshotDate = DB::table('wine_stock_snapshots')
            ->where('user_id', $wineryId)->whereDate('snapshot_date', '>=', $campaignStart)
            ->whereDate('snapshot_date', '<=', $campaignEnd)
            ->where('is_must', false)->orderByDesc('snapshot_date')->value('snapshot_date');

        $aperturaByType = $openingSnapshotDate
            ? DB::table('wine_stock_snapshots')->where('user_id', $wineryId)
                ->where('snapshot_date', $openingSnapshotDate)->where('is_must', false)
                ->groupBy('wine_type')->selectRaw('wine_type, SUM(quantity_liters) / 100 as hl')
                ->pluck('hl', 'wine_type')
            : collect();

        $producidoByType = DB::table('wines')->where('user_id', $wineryId)->where('vintage', $campaign)
            ->whereNotIn('status', ['cancelled'])->where('is_must', false)
            ->whereNotNull('volume_liters')->groupBy('wine_type')
            ->selectRaw('wine_type, SUM(volume_liters) / 100 as hl')->pluck('hl', 'wine_type');

        $compradoTotal = round((float) DB::table('external_grapes')->where('user_id', $wineryId)
            ->where('grape_type', 'bulk_wine')
            ->whereDate('entry_date', '>=', $campaignStart)->whereDate('entry_date', '<=', $campaignEnd)
            ->sum(DB::raw('total_weight_kg / 100')), 3);

        $vendidoByType = DB::table('invoice_items as ii')
            ->join('invoices as i', 'i.id', '=', 'ii.invoice_id')
            ->join('wine_lots as pl', 'pl.id', '=', 'ii.wine_lot_id')
            ->join('wines as w', 'w.id', '=', 'pl.wine_id')
            ->where('i.user_id', $wineryId)->whereIn('i.status', ['sent', 'paid'])
            ->whereDate('i.invoice_date', '>=', $campaignStart)->whereDate('i.invoice_date', '<=', $campaignEnd)
            ->where('w.is_must', false)->whereNotNull('pl.bottle_format')
            ->groupBy('w.wine_type')
            ->selectRaw('w.wine_type, SUM(ii.quantity * CAST(pl.bottle_format AS DECIMAL(10,0)) / 1000000) as hl')
            ->pluck('hl', 'wine_type');

        $perdidoByType = DB::table('wine_losses as wl')
            ->join('wines as w', 'w.id', '=', 'wl.wine_id')
            ->where('w.user_id', $wineryId)->where('w.is_must', false)
            ->whereDate('wl.loss_date', '>=', $campaignStart)->whereDate('wl.loss_date', '<=', $campaignEnd)
            ->groupBy('w.wine_type')
            ->selectRaw('w.wine_type, SUM(wl.quantity) / 100 as hl')->pluck('hl', 'wine_type');

        $cierreRealByType = $closingSnapshotDate
            ? DB::table('wine_stock_snapshots')->where('user_id', $wineryId)
                ->where('snapshot_date', $closingSnapshotDate)->where('is_must', false)
                ->groupBy('wine_type')->selectRaw('wine_type, SUM(quantity_liters) / 100 as hl')
                ->pluck('hl', 'wine_type')
            : collect();

        $result = [];
        $totals = ['apertura' => 0, 'producido' => 0, 'comprado' => $compradoTotal, 'vendido' => 0, 'perdido' => 0, 'cierre_calc' => 0, 'cierre_real' => null];

        foreach (Infovi::WINE_CATEGORIES as $type => $label) {
            $apertura   = round((float) ($aperturaByType[$type] ?? 0), 3);
            $producido  = round((float) ($producidoByType[$type] ?? 0), 3);
            $vendido    = round((float) ($vendidoByType[$type] ?? 0), 3);
            $perdido    = round((float) ($perdidoByType[$type] ?? 0), 3);
            $cierreCalc = round($apertura + $producido - $vendido - $perdido, 3);
            $cierreReal = isset($cierreRealByType[$type]) ? round((float) $cierreRealByType[$type], 3) : null;
            $delta      = $cierreReal !== null ? round($cierreReal - $cierreCalc, 3) : null;

            $result[] = compact('type', 'label', 'apertura', 'producido', 'vendido', 'perdido', 'cierreCalc', 'cierreReal', 'delta');
            $totals['apertura']    += $apertura;
            $totals['producido']   += $producido;
            $totals['vendido']     += $vendido;
            $totals['perdido']     += $perdido;
            $totals['cierre_calc'] += $cierreCalc;
            if ($cierreReal !== null) {
                $totals['cierre_real'] = ($totals['cierre_real'] ?? 0) + $cierreReal;
            }
        }

        $totals['cierre_calc'] = round($totals['cierre_calc'] + $compradoTotal, 3);
        foreach (['apertura', 'producido', 'vendido', 'perdido'] as $k) {
            $totals[$k] = round($totals[$k], 3);
        }
        if ($totals['cierre_real'] !== null) {
            $totals['cierre_real'] = round($totals['cierre_real'], 3);
        }

        return [
            'rows'             => $result,
            'totals'           => $totals,
            'comprado_total'   => $compradoTotal,
            'opening_snapshot' => $openingSnapshotDate,
            'closing_snapshot' => $closingSnapshotDate,
        ];
    }

    // ─── Private: Mosto ───────────────────────────────────────────────────────

    private function buildCuadroMosto(int $wineryId, int $campaign, string $from, string $to): array
    {
        $openingDate = DB::table('wine_stock_snapshots')->where('user_id', $wineryId)
            ->whereDate('snapshot_date', '<', $from)->where('is_must', true)
            ->orderByDesc('snapshot_date')->value('snapshot_date');

        $apertura = $openingDate
            ? round((float) DB::table('wine_stock_snapshots')->where('user_id', $wineryId)
                ->where('snapshot_date', $openingDate)->where('is_must', true)
                ->sum(DB::raw('quantity_liters / 100')), 3)
            : 0;

        $producido = round((float) DB::table('wines')->where('user_id', $wineryId)
            ->where('vintage', $campaign)->where('is_must', true)
            ->whereNotIn('status', ['cancelled'])->whereNotNull('volume_liters')
            ->sum(DB::raw('volume_liters / 100')), 3);

        $comprado = round((float) DB::table('external_grapes')->where('user_id', $wineryId)
            ->where('grape_type', 'must')
            ->whereDate('entry_date', '>=', $from)->whereDate('entry_date', '<=', $to)
            ->sum(DB::raw('total_weight_kg / 100')), 3);

        $vendido = round((float) DB::table('invoice_items as ii')
            ->join('invoices as i', 'i.id', '=', 'ii.invoice_id')
            ->join('wine_lots as pl', 'pl.id', '=', 'ii.wine_lot_id')
            ->join('wines as w', 'w.id', '=', 'pl.wine_id')
            ->where('i.user_id', $wineryId)->whereIn('i.status', ['sent', 'paid'])
            ->whereDate('i.invoice_date', '>=', $from)->whereDate('i.invoice_date', '<=', $to)
            ->where('w.is_must', true)->whereNotNull('pl.bottle_format')
            ->sum(DB::raw('ii.quantity * CAST(pl.bottle_format AS DECIMAL(10,0)) / 1000000')), 3);

        $perdido = round((float) DB::table('wine_losses as wl')
            ->join('wines as w', 'w.id', '=', 'wl.wine_id')
            ->where('w.user_id', $wineryId)->where('w.is_must', true)
            ->whereDate('wl.loss_date', '>=', $from)->whereDate('wl.loss_date', '<=', $to)
            ->sum(DB::raw('wl.quantity / 100')), 3);

        $closingDate = DB::table('wine_stock_snapshots')->where('user_id', $wineryId)
            ->whereDate('snapshot_date', '>=', $from)->whereDate('snapshot_date', '<=', $to)
            ->where('is_must', true)->orderByDesc('snapshot_date')->value('snapshot_date');

        $cierreReal = $closingDate
            ? round((float) DB::table('wine_stock_snapshots')->where('user_id', $wineryId)
                ->where('snapshot_date', $closingDate)->where('is_must', true)
                ->sum(DB::raw('quantity_liters / 100')), 3)
            : null;

        $cierreCalc = round($apertura + $producido + $comprado - $vendido - $perdido, 3);

        return [
            'apertura'         => $apertura,
            'producido'        => $producido,
            'comprado'         => $comprado,
            'vendido'          => $vendido,
            'perdido'          => $perdido,
            'cierre_calc'      => $cierreCalc,
            'cierre_real'      => $cierreReal,
            'delta'            => $cierreReal !== null ? round($cierreReal - $cierreCalc, 3) : null,
            'opening_snapshot' => $openingDate,
            'closing_snapshot' => $closingDate,
            'has_data'         => ($producido + $comprado + $apertura) > 0,
        ];
    }

    // ─── Private: Helper rows builder ────────────────────────────────────────

    private function buildCategoryRows($rows, ?string $snapshotDate): array
    {
        $result = [];
        $total  = 0;

        foreach (Infovi::WINE_CATEGORIES as $type => $label) {
            $row      = $rows->firstWhere('wine_type', $type);
            $hl       = $row ? round((float) $row->hl, 3) : 0;
            $result[] = [
                'type'       => $type,
                'label'      => $label,
                'hl'         => $hl,
                'wine_count' => (int) ($row->wine_count ?? 0),
                'source'     => $snapshotDate ? 'snapshot' : 'live',
            ];
            $total += $hl;
        }

        return [
            'rows'          => $result,
            'total_hl'      => round($total, 3),
            'snapshot_date' => $snapshotDate,
        ];
    }
}
