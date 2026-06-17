<?php

namespace App\Livewire\Winery\Silicie;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * INFOVI — Sistema de Información de Mercados del Sector Vitivinícola
 *
 * Governed by Real Decreto 739/2015 (MAPA/AICA).
 * Producers must declare monthly (≥1,000 HL avg) or twice yearly (<1,000 HL avg):
 * stocks, production, and sales broken down by wine type and DO/IGP/table.
 *
 * Campaign year: August 1 → July 31.
 * This component generates the data in HL (hectolitres) needed to fill
 * the official AICA declarations at mapa.gob.es/infovi.
 */
class Infovi extends Component
{
    // INFOVI wine type codes (AICA nomenclature)
    const WINE_CATEGORIES = [
        'red' => 'Vino tinto tranquilo',
        'white' => 'Vino blanco tranquilo',
        'rose' => 'Vino rosado tranquilo',
        'sparkling' => 'Vino espumoso',
        'fortified' => 'Vino licoroso / generoso',
        'sweet' => 'Vino dulce natural',
        'semi_sweet' => 'Vino semidulce',
        'other' => 'Otros vinos',
    ];

    // INFOVI category breakdown — three buckets as declared in AICA forms
    // Maps to wines.category (VdM | IGP | DO | DOCa | vino_de_pago)
    const PROTECTION_LEVELS = [
        'DO' => 'Denominación de Origen (DO/DOCa/Pago)',
        'IGP' => 'Indicación Geográfica Protegida (IGP)',
        'VdM' => 'Vino de Mesa / Sin indicación geográfica',
    ];

    // Bottle format in ml → for HL calculation from invoice items
    const BOTTLE_ML = [
        '187' => 187,
        '375' => 375,
        '500' => 500,
        '750' => 750,
        '1000' => 1000,
        '1500' => 1500,
        '3000' => 3000,
        '5000' => 5000,
    ];

    public string $filterCampaign = ''; // e.g. "2025" = campaña 2025/2026

    public bool $showCategoryBreakdown = false;

    public function mount(): void
    {
        // Default to current campaign year (campaign starts Aug 1)
        $now = now();
        $this->filterCampaign = (string) ($now->month >= 8 ? $now->year : $now->year - 1);
    }

    public function toggleCategoryBreakdown(): void
    {
        $this->showCategoryBreakdown = ! $this->showCategoryBreakdown;
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $wineryId = Auth::id();
        $campaign = (int) ($this->filterCampaign ?: (now()->month >= 8 ? now()->year : now()->year - 1));

        // Campaign date range: Aug 1 of $campaign → Jul 31 of $campaign+1
        $campaignStart = "{$campaign}-08-01";
        $campaignEnd = ($campaign + 1).'-07-31';

        $org = Auth::user()->organization;
        $threshold = $this->buildThreshold($wineryId);

        $existencias = $this->buildCuadroExistencias($wineryId, $campaign);
        $produccion = $this->buildCuadroProduccion($wineryId, $campaign);
        $ventas = $this->buildCuadroVentas($wineryId, $campaignStart, $campaignEnd);
        $entradas = $this->buildCuadroEntradas($wineryId, $campaign, $campaignStart, $campaignEnd);
        $balanceSheet = $this->buildBalanceSheet($wineryId, $campaign, $campaignStart, $campaignEnd);
        $mosto = $this->buildCuadroMosto($wineryId, $campaign, $campaignStart, $campaignEnd);

        // Available campaigns (based on harvest vintages)
        $campaigns = DB::table('harvests')
            ->where('winery_id', $wineryId)
            ->selectRaw('DISTINCT vintage')
            ->orderByDesc('vintage')
            ->pluck('vintage')
            ->map(fn ($v) => (int) $v) // vintage year = campaign start year (Aug Y → Jul Y+1)
            ->unique()
            ->values();

        if ($campaigns->isEmpty()) {
            $campaigns = collect([now()->month >= 8 ? now()->year : now()->year - 1]);
        }

        return view('livewire.winery.silicie.infovi', compact(
            'existencias', 'produccion', 'ventas', 'entradas',
            'balanceSheet', 'mosto',
            'campaign', 'campaignStart', 'campaignEnd',
            'org', 'threshold', 'campaigns'
        ));
    }

    // ── Threshold detection ───────────────────────────────────────────────

    /**
     * Determines declaration frequency based on avg HL produced over last 4 campaigns.
     * ≥ 1,000 HL avg → monthly declarations required (gran productor)
     * < 1,000 HL avg → December + August only (pequeño productor)
     */
    private function buildThreshold(int $wineryId): array
    {
        // Get HL produced per vintage (last 4)
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

    private function buildDeadlines(bool $isLarge): array
    {
        $now = now();
        $deadlines = [];

        if ($isLarge) {
            // Monthly: 1st–19th of each month for previous month's data
            $nextMonth = $now->copy()->addMonth()->startOfMonth();
            $deadlines[] = [
                'label' => 'Declaración mensual '.$nextMonth->translatedFormat('F Y'),
                'date' => $nextMonth->copy()->setDay(19)->toDateString(),
                'type' => 'monthly',
            ];
        } else {
            // Pequeño productor: Aug 1–19 (datos julio) y Dec 1–19 (datos noviembre)
            // Order matters: nearest deadline first
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

    // ── Cuadro 1 — Existencias ────────────────────────────────────────────

    /**
     * Stock at end of campaign, by wine type in HL (wines only, not mosto).
     * Uses last available snapshot for the campaign, or live stock.
     */
    private function buildCuadroExistencias(int $wineryId, int $campaign): array
    {
        // Look for snapshots within this campaign (Aug–Jul)
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

            // Category breakdown when requested (bucket: DO/IGP/VdM)
            $categoryRows = $this->showCategoryBreakdown
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

            $categoryRows = $this->showCategoryBreakdown
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

            // Build category sub-breakdown (DO/IGP/VdM buckets)
            $categories = [];
            if ($this->showCategoryBreakdown) {
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

    // ── Cuadro 2 — Producción ─────────────────────────────────────────────

    /**
     * Wines (not mosto) produced in the campaign vintage, in HL by type.
     */
    private function buildCuadroProduccion(int $wineryId, int $campaign): array
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

        // Category breakdown when requested (DO/IGP/VdM buckets)
        $categoryRows = $this->showCategoryBreakdown
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
            if ($this->showCategoryBreakdown) {
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

    // ── Cuadro 3 — Ventas ─────────────────────────────────────────────────

    /**
     * Sales of wine (not mosto) during the campaign period, in HL by wine type.
     *
     * Calculation chain: invoice_items.quantity × wine_lots.bottle_format (ml) / 1,000,000 → HL
     */
    private function buildCuadroVentas(int $wineryId, string $from, string $to): array
    {
        // Items linked to product lots → we can calculate HL (wine only)
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

        // Invoice totals (all, for economic summary)
        $invoiceTotals = DB::table('invoices as i')
            ->where('i.user_id', $wineryId)
            ->whereIn('i.status', ['sent', 'paid'])
            ->whereDate('i.invoice_date', '>=', $from)
            ->whereDate('i.invoice_date', '<=', $to)
            ->selectRaw('COUNT(*) as total_invoices, SUM(total_amount) as total_amount')
            ->first();

        // Items without wine_lot → cannot calculate HL (harvest settlements, misc)
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
            $result[] = [
                'type' => $type,
                'label' => $label,
                'hl' => $hl,
                'bottles' => $bottles,
            ];
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

    // ── Cuadro 4 — Entradas ───────────────────────────────────────────────

    /**
     * Grape, must and bulk wine received during the campaign, in KG and HL.
     */
    private function buildCuadroEntradas(int $wineryId, int $campaign, string $from, string $to): array
    {
        // Own harvests: vintage matches campaign start year
        $kgPropia = DB::table('harvests')
            ->where('winery_id', $wineryId)
            ->where('vintage', $campaign)
            ->sum('total_weight');

        // External purchases within campaign dates
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

    // ── Balance Sheet — Hoja de balance INFOVI ────────────────────────────

    /**
     * Full campaign balance: apertura → entradas/producción → salidas/pérdidas → cierre.
     * Used to verify INFOVI data coherence before submitting to AICA.
     *
     * apertura (opening) = most recent snapshot before campaign start (Aug 1)
     * producido = wines produced in campaign
     * comprado  = bulk wine purchased during campaign
     * vendido   = wine sold (HL from invoice_items) during campaign
     * perdido   = wine losses during campaign
     * cierre_calc = apertura + producido + comprado - vendido - perdido
     * cierre_real = latest snapshot within campaign (or null)
     */
    private function buildBalanceSheet(int $wineryId, int $campaign, string $campaignStart, string $campaignEnd): array
    {
        // ── Find snapshot dates (2 queries) ───────────────────────────────
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

        // ── Batch queries — one per metric grouped by wine_type ───────────

        // Opening HL by type
        $aperturaByType = $openingSnapshotDate
            ? DB::table('wine_stock_snapshots')
                ->where('user_id', $wineryId)
                ->where('snapshot_date', $openingSnapshotDate)
                ->where('is_must', false)
                ->groupBy('wine_type')
                ->selectRaw('wine_type, SUM(quantity_liters) / 100 as hl')
                ->pluck('hl', 'wine_type')
            : collect();

        // Produced HL by type
        $producidoByType = DB::table('wines')
            ->where('user_id', $wineryId)
            ->where('vintage', $campaign)
            ->whereNotIn('status', ['cancelled'])
            ->where('is_must', false)
            ->whereNotNull('volume_liters')
            ->groupBy('wine_type')
            ->selectRaw('wine_type, SUM(volume_liters) / 100 as hl')
            ->pluck('hl', 'wine_type');

        // Bulk wine purchased — NOT splittable by wine_type (no wine_type on external_grapes)
        // Counted in totals only; shown as a single line in the footer
        $compradoTotal = round((float) DB::table('external_grapes')
            ->where('user_id', $wineryId)
            ->where('grape_type', 'bulk_wine')
            ->whereDate('entry_date', '>=', $campaignStart)
            ->whereDate('entry_date', '<=', $campaignEnd)
            ->sum(DB::raw('total_weight_kg / 100')), 3);

        // Sold HL by type
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

        // Losses HL by type
        $perdidoByType = DB::table('wine_losses as wl')
            ->join('wines as w', 'w.id', '=', 'wl.wine_id')
            ->where('w.user_id', $wineryId)
            ->where('w.is_must', false)
            ->whereDate('wl.loss_date', '>=', $campaignStart)
            ->whereDate('wl.loss_date', '<=', $campaignEnd)
            ->groupBy('w.wine_type')
            ->selectRaw('w.wine_type, SUM(wl.quantity) / 100 as hl')
            ->pluck('hl', 'wine_type');

        // Closing HL by type
        $cierreRealByType = $closingSnapshotDate
            ? DB::table('wine_stock_snapshots')
                ->where('user_id', $wineryId)
                ->where('snapshot_date', $closingSnapshotDate)
                ->where('is_must', false)
                ->groupBy('wine_type')
                ->selectRaw('wine_type, SUM(quantity_liters) / 100 as hl')
                ->pluck('hl', 'wine_type')
            : collect();

        // ── Build per-type rows ───────────────────────────────────────────
        $result = [];
        $totals = [
            'apertura' => 0,
            'producido' => 0,
            'comprado' => $compradoTotal, // not splittable by type
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
            // comprado not split by type — shown as 0 per row, total in footer
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

        // Totals include comprado (global, not per type)
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

    // ── Mosto — Declaración separada INFOVI ──────────────────────────────

    /**
     * Mosto (must) declared separately in INFOVI.
     * Covers all wines where is_must = true.
     */
    private function buildCuadroMosto(int $wineryId, int $campaign, string $from, string $to): array
    {
        // Opening mosto stock (snapshot before campaign start)
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

        // Mosto produced (wines with is_must=true, vintage = campaign)
        $producido = round((float) DB::table('wines')
            ->where('user_id', $wineryId)
            ->where('vintage', $campaign)
            ->where('is_must', true)
            ->whereNotIn('status', ['cancelled'])
            ->whereNotNull('volume_liters')
            ->sum(DB::raw('volume_liters / 100')), 3);

        // Mosto purchased (external_grapes type='must')
        $comprado = round((float) DB::table('external_grapes')
            ->where('user_id', $wineryId)
            ->where('grape_type', 'must')
            ->whereDate('entry_date', '>=', $from)
            ->whereDate('entry_date', '<=', $to)
            ->sum(DB::raw('total_weight_kg / 100')), 3);

        // Mosto sold (invoice_items → wine_lots → is_must wines)
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

        // Mosto losses
        $perdido = round((float) DB::table('wine_losses as wl')
            ->join('wines as w', 'w.id', '=', 'wl.wine_id')
            ->where('w.user_id', $wineryId)
            ->where('w.is_must', true)
            ->whereDate('wl.loss_date', '>=', $from)
            ->whereDate('wl.loss_date', '<=', $to)
            ->sum(DB::raw('wl.quantity / 100')), 3);

        // Closing mosto (latest snapshot within campaign)
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
}
