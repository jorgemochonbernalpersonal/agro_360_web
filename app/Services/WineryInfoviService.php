<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class WineryInfoviService
{
    public const WINE_CATEGORIES = [
        'red' => 'Vino tinto tranquilo',
        'white' => 'Vino blanco tranquilo',
        'rose' => 'Vino rosado tranquilo',
        'sparkling' => 'Vino espumoso',
        'fortified' => 'Vino licoroso / generoso',
        'sweet' => 'Vino dulce natural',
        'semi_sweet' => 'Vino semidulce',
        'other' => 'Otros vinos',
    ];

    public const PROTECTION_LEVELS = [
        'DO' => 'Denominación de Origen (DO/DOCa/Pago)',
        'IGP' => 'Indicación Geográfica Protegida (IGP)',
        'VdM' => 'Vino de Mesa / Sin indicación geográfica',
    ];

    public const BOTTLE_ML = [
        '187' => 187,
        '375' => 375,
        '500' => 500,
        '750' => 750,
        '1000' => 1000,
        '1500' => 1500,
        '3000' => 3000,
        '5000' => 5000,
    ];

    public function buildThreshold(int $wineryId): array
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
        $avgHl = $campaigns > 0 ? round($vintageHl->sum() / $campaigns, 1) : 0;
        $isLarge = $avgHl >= 1000;

        return [
            'avg_hl' => $avgHl,
            'campaigns' => $campaigns,
            'is_large' => $isLarge,
            'by_vintage' => $vintageHl,
            'next_deadlines' => $this->buildDeadlines($isLarge),
        ];
    }

    public function buildCuadroExistencias(int $wineryId, int $campaign, bool $showCategoryBreakdown): array
    {
        $snapshotDate = DB::table('wine_stock_snapshots')
            ->where('user_id', $wineryId)
            ->whereDate('snapshot_date', '>=', "{$campaign}-08-01")
            ->whereDate('snapshot_date', '<=', ($campaign + 1).'-07-31')
            ->orderByDesc('snapshot_date')
            ->value('snapshot_date');

        if ($snapshotDate) {
            $rows = DB::table('wine_stock_snapshots as wss')
                ->where('wss.user_id', $wineryId)
                ->where('wss.snapshot_date', $snapshotDate)
                ->where('wss.is_must', false)
                ->select([
                    'wss.wine_type',
                    DB::raw('SUM(wss.quantity_liters) / 100 as hl'),
                    DB::raw('COUNT(DISTINCT wss.wine_id) as wine_count'),
                ])
                ->groupBy('wss.wine_type')
                ->get();

            $categoryRows = $showCategoryBreakdown
                ? DB::table('wine_stock_snapshots as wss')
                    ->join('wines as w', 'w.id', '=', 'wss.wine_id')
                    ->where('wss.user_id', $wineryId)
                    ->where('wss.snapshot_date', $snapshotDate)
                    ->where('wss.is_must', false)
                    ->select([
                        'wss.wine_type',
                        DB::raw("CASE WHEN w.category IN ('DO','DOCa','vino_de_pago') THEN 'DO' WHEN w.category='IGP' THEN 'IGP' ELSE 'VdM' END as infovi_bucket"),
                        DB::raw('SUM(wss.quantity_liters) / 100 as hl'),
                    ])
                    ->groupBy('wss.wine_type', 'infovi_bucket')
                    ->get()
                : collect();
        } else {
            $rows = DB::table('container_current_states as ccs')
                ->join('containers as c', 'c.id', '=', 'ccs.container_id')
                ->join('wines as w', 'w.id', '=', 'ccs.wine_id')
                ->where('c.user_id', $wineryId)
                ->where('ccs.current_quantity', '>', 0)
                ->where('w.is_must', false)
                ->select([
                    'w.wine_type',
                    DB::raw('SUM(ccs.current_quantity) / 100 as hl'),
                    DB::raw('COUNT(DISTINCT w.id) as wine_count'),
                ])
                ->groupBy('w.wine_type')
                ->get();

            $categoryRows = $showCategoryBreakdown
                ? DB::table('container_current_states as ccs')
                    ->join('containers as c', 'c.id', '=', 'ccs.container_id')
                    ->join('wines as w', 'w.id', '=', 'ccs.wine_id')
                    ->where('c.user_id', $wineryId)
                    ->where('ccs.current_quantity', '>', 0)
                    ->where('w.is_must', false)
                    ->select([
                        'w.wine_type',
                        DB::raw("CASE WHEN w.category IN ('DO','DOCa','vino_de_pago') THEN 'DO' WHEN w.category='IGP' THEN 'IGP' ELSE 'VdM' END as infovi_bucket"),
                        DB::raw('SUM(ccs.current_quantity) / 100 as hl'),
                    ])
                    ->groupBy('w.wine_type', 'infovi_bucket')
                    ->get()
                : collect();
        }

        $result = [];
        $total = 0;

        foreach (self::WINE_CATEGORIES as $type => $label) {
            $row = $rows->firstWhere('wine_type', $type);
            $hl = $row ? round((float) $row->hl, 3) : 0;

            $categories = [];
            if ($showCategoryBreakdown) {
                foreach (self::PROTECTION_LEVELS as $lvl => $lvlLabel) {
                    $catRow = $categoryRows->first(fn ($r) => $r->wine_type === $type && $r->infovi_bucket === $lvl);
                    $categories[$lvl] = [
                        'label' => $lvlLabel,
                        'hl' => $catRow ? round((float) $catRow->hl, 3) : 0,
                    ];
                }
            }

            $result[] = [
                'type' => $type,
                'label' => $label,
                'hl' => $hl,
                'wine_count' => $row->wine_count ?? 0,
                'source' => $snapshotDate ? 'snapshot' : 'live',
                'categories' => $categories,
            ];
            $total += $hl;
        }

        return [
            'rows' => $result,
            'total_hl' => round($total, 3),
            'snapshot_date' => $snapshotDate,
        ];
    }

    public function buildCuadroProduccion(int $wineryId, int $campaign, bool $showCategoryBreakdown): array
    {
        $rows = DB::table('wines as w')
            ->where('w.user_id', $wineryId)
            ->where('w.vintage', $campaign)
            ->whereNotIn('w.status', ['cancelled'])
            ->whereNotNull('w.volume_liters')
            ->where('w.volume_liters', '>', 0)
            ->where('w.is_must', false)
            ->select([
                'w.wine_type',
                DB::raw('SUM(w.volume_liters) / 100 as hl'),
                DB::raw('COUNT(*) as wine_count'),
            ])
            ->groupBy('w.wine_type')
            ->get();

        $categoryRows = $showCategoryBreakdown
            ? DB::table('wines as w')
                ->where('w.user_id', $wineryId)
                ->where('w.vintage', $campaign)
                ->whereNotIn('w.status', ['cancelled'])
                ->whereNotNull('w.volume_liters')
                ->where('w.volume_liters', '>', 0)
                ->where('w.is_must', false)
                ->select([
                    'w.wine_type',
                    DB::raw("CASE WHEN w.category IN ('DO','DOCa','vino_de_pago') THEN 'DO' WHEN w.category='IGP' THEN 'IGP' ELSE 'VdM' END as infovi_bucket"),
                    DB::raw('SUM(w.volume_liters) / 100 as hl'),
                ])
                ->groupBy('w.wine_type', 'infovi_bucket')
                ->get()
            : collect();

        $result = [];
        $total = 0;

        foreach (self::WINE_CATEGORIES as $type => $label) {
            $row = $rows->firstWhere('wine_type', $type);
            $hl = $row ? round((float) $row->hl, 3) : 0;

            $categories = [];
            if ($showCategoryBreakdown) {
                foreach (self::PROTECTION_LEVELS as $lvl => $lvlLabel) {
                    $catRow = $categoryRows->first(fn ($r) => $r->wine_type === $type && $r->infovi_bucket === $lvl);
                    $categories[$lvl] = [
                        'label' => $lvlLabel,
                        'hl' => $catRow ? round((float) $catRow->hl, 3) : 0,
                    ];
                }
            }

            $result[] = [
                'type' => $type,
                'label' => $label,
                'hl' => $hl,
                'wine_count' => $row->wine_count ?? 0,
                'categories' => $categories,
            ];
            $total += $hl;
        }

        return [
            'rows' => $result,
            'total_hl' => round($total, 3),
        ];
    }

    public function buildCuadroVentas(int $wineryId, string $from, string $to): array
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
                DB::raw('COUNT(DISTINCT i.id) as invoice_count'),
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

        $unknownHlCount = DB::table('invoice_items as ii')
            ->join('invoices as i', 'i.id', '=', 'ii.invoice_id')
            ->where('i.user_id', $wineryId)
            ->whereIn('i.status', ['sent', 'paid'])
            ->whereDate('i.invoice_date', '>=', $from)
            ->whereDate('i.invoice_date', '<=', $to)
            ->whereNull('ii.wine_lot_id')
            ->count();

        $result = [];
        $totalHl = 0;
        $totalBottles = 0;

        foreach (self::WINE_CATEGORIES as $type => $label) {
            $row = $lotRows->firstWhere('wine_type', $type);
            $hl = $row ? round((float) $row->hl, 3) : 0;
            $bottles = $row ? (int) $row->bottles : 0;
            $result[] = ['type' => $type, 'label' => $label, 'hl' => $hl, 'bottles' => $bottles];
            $totalHl += $hl;
            $totalBottles += $bottles;
        }

        return [
            'rows' => $result,
            'total_hl' => round($totalHl, 3),
            'total_bottles' => $totalBottles,
            'total_invoices' => (int) ($invoiceTotals->total_invoices ?? 0),
            'total_amount' => (float) ($invoiceTotals->total_amount ?? 0),
            'unknown_hl_items' => $unknownHlCount,
        ];
    }

    public function buildCuadroEntradas(int $wineryId, int $campaign, string $from, string $to): array
    {
        $kgPropia = DB::table('harvests')
            ->where('winery_id', $wineryId)
            ->where('vintage', $campaign)
            ->sum('total_weight');

        $externas = DB::table('external_grapes')
            ->where('user_id', $wineryId)
            ->whereDate('entry_date', '>=', $from)
            ->whereDate('entry_date', '<=', $to)
            ->select([
                'grape_type',
                DB::raw('SUM(total_weight_kg) as total_kg'),
                DB::raw('COUNT(*) as entries'),
            ])
            ->groupBy('grape_type')
            ->get();

        return [
            'kg_propia' => (float) $kgPropia,
            'kg_comprada' => (float) ($externas->firstWhere('grape_type', 'grapes')->total_kg ?? 0),
            'hl_mosto' => round((float) ($externas->firstWhere('grape_type', 'must')->total_kg ?? 0) / 100, 3),
            'hl_vino_granel' => round((float) ($externas->firstWhere('grape_type', 'bulk_wine')->total_kg ?? 0) / 100, 3),
        ];
    }

    public function buildBalanceSheet(int $wineryId, int $campaign, string $campaignStart, string $campaignEnd): array
    {
        $openingSnapshotDate = DB::table('wine_stock_snapshots')
            ->where('user_id', $wineryId)
            ->whereDate('snapshot_date', '<', $campaignStart)
            ->where('is_must', false)
            ->orderByDesc('snapshot_date')
            ->value('snapshot_date');

        $closingSnapshotDate = DB::table('wine_stock_snapshots')
            ->where('user_id', $wineryId)
            ->whereDate('snapshot_date', '>=', $campaignStart)
            ->whereDate('snapshot_date', '<=', $campaignEnd)
            ->where('is_must', false)
            ->orderByDesc('snapshot_date')
            ->value('snapshot_date');

        $aperturaByType = $openingSnapshotDate
            ? DB::table('wine_stock_snapshots')
                ->where('user_id', $wineryId)
                ->where('snapshot_date', $openingSnapshotDate)
                ->where('is_must', false)
                ->groupBy('wine_type')
                ->selectRaw('wine_type, SUM(quantity_liters) / 100 as hl')
                ->pluck('hl', 'wine_type')
            : collect();

        $producidoByType = DB::table('wines')
            ->where('user_id', $wineryId)
            ->where('vintage', $campaign)
            ->whereNotIn('status', ['cancelled'])
            ->where('is_must', false)
            ->whereNotNull('volume_liters')
            ->groupBy('wine_type')
            ->selectRaw('wine_type, SUM(volume_liters) / 100 as hl')
            ->pluck('hl', 'wine_type');

        $compradoTotal = round((float) DB::table('external_grapes')
            ->where('user_id', $wineryId)
            ->where('grape_type', 'bulk_wine')
            ->whereDate('entry_date', '>=', $campaignStart)
            ->whereDate('entry_date', '<=', $campaignEnd)
            ->sum(DB::raw('total_weight_kg / 100')), 3);

        $vendidoByType = DB::table('invoice_items as ii')
            ->join('invoices as i', 'i.id', '=', 'ii.invoice_id')
            ->join('wine_lots as pl', 'pl.id', '=', 'ii.wine_lot_id')
            ->join('wines as w', 'w.id', '=', 'pl.wine_id')
            ->where('i.user_id', $wineryId)
            ->whereIn('i.status', ['sent', 'paid'])
            ->whereDate('i.invoice_date', '>=', $campaignStart)
            ->whereDate('i.invoice_date', '<=', $campaignEnd)
            ->where('w.is_must', false)
            ->whereNotNull('pl.bottle_format')
            ->groupBy('w.wine_type')
            ->selectRaw('w.wine_type, SUM(ii.quantity * CAST(pl.bottle_format AS DECIMAL(10,0)) / 1000000) as hl')
            ->pluck('hl', 'wine_type');

        $perdidoByType = DB::table('wine_losses as wl')
            ->join('wines as w', 'w.id', '=', 'wl.wine_id')
            ->where('w.user_id', $wineryId)
            ->where('w.is_must', false)
            ->whereDate('wl.loss_date', '>=', $campaignStart)
            ->whereDate('wl.loss_date', '<=', $campaignEnd)
            ->groupBy('w.wine_type')
            ->selectRaw('w.wine_type, SUM(wl.quantity) / 100 as hl')
            ->pluck('hl', 'wine_type');

        $cierreRealByType = $closingSnapshotDate
            ? DB::table('wine_stock_snapshots')
                ->where('user_id', $wineryId)
                ->where('snapshot_date', $closingSnapshotDate)
                ->where('is_must', false)
                ->groupBy('wine_type')
                ->selectRaw('wine_type, SUM(quantity_liters) / 100 as hl')
                ->pluck('hl', 'wine_type')
            : collect();

        $result = [];
        $totals = [
            'apertura' => 0,
            'producido' => 0,
            'comprado' => $compradoTotal,
            'vendido' => 0,
            'perdido' => 0,
            'cierre_calc' => 0,
            'cierre_real' => null,
        ];

        foreach (self::WINE_CATEGORIES as $type => $label) {
            $apertura = round((float) ($aperturaByType[$type] ?? 0), 3);
            $producido = round((float) ($producidoByType[$type] ?? 0), 3);
            $vendido = round((float) ($vendidoByType[$type] ?? 0), 3);
            $perdido = round((float) ($perdidoByType[$type] ?? 0), 3);
            $cierreCalc = round($apertura + $producido - $vendido - $perdido, 3);
            $cierreReal = isset($cierreRealByType[$type])
                ? round((float) $cierreRealByType[$type], 3)
                : null;
            $delta = $cierreReal !== null ? round($cierreReal - $cierreCalc, 3) : null;

            $result[] = compact(
                'type', 'label', 'apertura', 'producido',
                'vendido', 'perdido', 'cierreCalc', 'cierreReal', 'delta'
            ) + ['cierre_calc' => $cierreCalc, 'cierre_real' => $cierreReal];

            $totals['apertura'] += $apertura;
            $totals['producido'] += $producido;
            $totals['vendido'] += $vendido;
            $totals['perdido'] += $perdido;
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
            'rows' => $result,
            'totals' => $totals,
            'comprado_total' => $compradoTotal,
            'opening_snapshot' => $openingSnapshotDate,
            'closing_snapshot' => $closingSnapshotDate,
        ];
    }

    public function buildCuadroMosto(int $wineryId, int $campaign, string $from, string $to): array
    {
        $openingDate = DB::table('wine_stock_snapshots')
            ->where('user_id', $wineryId)
            ->whereDate('snapshot_date', '<', $from)
            ->where('is_must', true)
            ->orderByDesc('snapshot_date')
            ->value('snapshot_date');

        $apertura = $openingDate
            ? round((float) DB::table('wine_stock_snapshots')
                ->where('user_id', $wineryId)
                ->where('snapshot_date', $openingDate)
                ->where('is_must', true)
                ->sum(DB::raw('quantity_liters / 100')), 3)
            : 0;

        $producido = round((float) DB::table('wines')
            ->where('user_id', $wineryId)
            ->where('vintage', $campaign)
            ->where('is_must', true)
            ->whereNotIn('status', ['cancelled'])
            ->whereNotNull('volume_liters')
            ->sum(DB::raw('volume_liters / 100')), 3);

        $comprado = round((float) DB::table('external_grapes')
            ->where('user_id', $wineryId)
            ->where('grape_type', 'must')
            ->whereDate('entry_date', '>=', $from)
            ->whereDate('entry_date', '<=', $to)
            ->sum(DB::raw('total_weight_kg / 100')), 3);

        $vendido = round((float) DB::table('invoice_items as ii')
            ->join('invoices as i', 'i.id', '=', 'ii.invoice_id')
            ->join('wine_lots as pl', 'pl.id', '=', 'ii.wine_lot_id')
            ->join('wines as w', 'w.id', '=', 'pl.wine_id')
            ->where('i.user_id', $wineryId)
            ->whereIn('i.status', ['sent', 'paid'])
            ->whereDate('i.invoice_date', '>=', $from)
            ->whereDate('i.invoice_date', '<=', $to)
            ->where('w.is_must', true)
            ->whereNotNull('pl.bottle_format')
            ->sum(DB::raw('ii.quantity * CAST(pl.bottle_format AS DECIMAL(10,0)) / 1000000')), 3);

        $perdido = round((float) DB::table('wine_losses as wl')
            ->join('wines as w', 'w.id', '=', 'wl.wine_id')
            ->where('w.user_id', $wineryId)
            ->where('w.is_must', true)
            ->whereDate('wl.loss_date', '>=', $from)
            ->whereDate('wl.loss_date', '<=', $to)
            ->sum(DB::raw('wl.quantity / 100')), 3);

        $closingDate = DB::table('wine_stock_snapshots')
            ->where('user_id', $wineryId)
            ->whereDate('snapshot_date', '>=', $from)
            ->whereDate('snapshot_date', '<=', $to)
            ->where('is_must', true)
            ->orderByDesc('snapshot_date')
            ->value('snapshot_date');

        $cierreReal = $closingDate
            ? round((float) DB::table('wine_stock_snapshots')
                ->where('user_id', $wineryId)
                ->where('snapshot_date', $closingDate)
                ->where('is_must', true)
                ->sum(DB::raw('quantity_liters / 100')), 3)
            : null;

        $cierreCalc = round($apertura + $producido + $comprado - $vendido - $perdido, 3);

        return [
            'apertura' => $apertura,
            'producido' => $producido,
            'comprado' => $comprado,
            'vendido' => $vendido,
            'perdido' => $perdido,
            'cierre_calc' => $cierreCalc,
            'cierre_real' => $cierreReal,
            'delta' => $cierreReal !== null ? round($cierreReal - $cierreCalc, 3) : null,
            'opening_snapshot' => $openingDate,
            'closing_snapshot' => $closingDate,
            'has_data' => ($producido + $comprado + $apertura) > 0,
        ];
    }

    private function buildDeadlines(bool $isLarge): array
    {
        $now = now();
        $deadlines = [];

        if ($isLarge) {
            $nextMonth = $now->copy()->addMonth()->startOfMonth();
            $deadlines[] = [
                'label' => 'Declaración mensual '.$nextMonth->translatedFormat('F Y'),
                'date' => $nextMonth->copy()->setDay(19)->toDateString(),
                'type' => 'monthly',
            ];
        } else {
            $candidates = [
                now()->year.'-08-19',
                now()->year.'-12-19',
                (now()->year + 1).'-08-19',
                (now()->year + 1).'-12-19',
            ];
            foreach ($candidates as $d) {
                if ($d >= $now->toDateString()) {
                    $date = \Carbon\Carbon::parse($d);
                    $month = $date->month === 12 ? 'noviembre' : 'julio';
                    $deadlines[] = [
                        'label' => 'Declaración ampliada '.$month.' '.$date->year,
                        'date' => $d,
                        'type' => 'semi_annual',
                    ];
                    if (count($deadlines) >= 2) {
                        break;
                    }
                }
            }
        }

        return $deadlines;
    }
}
