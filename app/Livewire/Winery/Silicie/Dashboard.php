<?php

namespace App\Livewire\Winery\Silicie;

use App\Models\Wine;
use App\Models\WineStockSnapshot;
use App\Services\Exporters\SilicieCsvExporter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Dashboard extends Component
{
    public string $filterVintage = '';

    public string $filterFiscalYear = '';

    public string $currentTab = 'entries';

    public bool $showExportGuide = false;

    public function mount(): void
    {
        $this->filterVintage = (string) now()->year;
        $this->filterFiscalYear = (string) now()->year;
    }

    public function switchTab(string $tab): void
    {
        $this->currentTab = $tab;
    }

    /** Genera un snapshot de existencias para la fecha actual */
    public function takeSnapshot(): void
    {
        $wineryId = Auth::id();
        $today = now()->toDateString();

        $rows = DB::table('container_current_states as ccs')
            ->join('containers as c', 'c.id', '=', 'ccs.container_id')
            ->join('wines as w', 'w.id', '=', 'ccs.wine_id')
            ->where('c.user_id', $wineryId)
            ->where('w.user_id', $wineryId)
            ->where('ccs.current_quantity', '>', 0)
            ->select([
                'w.id as wine_id',
                'w.vintage',
                'w.wine_type',
                'w.is_must',
                DB::raw('SUM(ccs.current_quantity) as total_liters'),
                DB::raw('COUNT(DISTINCT ccs.container_id) as container_count'),
            ])
            ->groupBy('w.id', 'w.vintage', 'w.wine_type', 'w.is_must')
            ->get();

        foreach ($rows as $row) {
            WineStockSnapshot::updateOrCreate(
                [
                    'user_id' => $wineryId,
                    'wine_id' => $row->wine_id,
                    'snapshot_date' => $today,
                ],
                [
                    'quantity_liters' => $row->total_liters,
                    'container_count' => $row->container_count,
                    'vintage' => $row->vintage,
                    'wine_type' => $row->wine_type,
                    'is_must' => (bool) $row->is_must,
                    'created_by' => $wineryId,
                ]
            );
        }

        $this->dispatch('toast', message: __('Instantánea de existencias registrada.'), type: 'success');
    }

    /** Descarga el CSV SILICIE del año seleccionado y muestra la guía post-exportación */
    public function exportCsv(): StreamedResponse
    {
        $wineryId = Auth::id();
        $vintage = (int) ($this->filterVintage ?: now()->year);
        $csv = (new SilicieCsvExporter)->export($wineryId, $vintage);
        $filename = 'SILICIE_'.$wineryId.'_'.$vintage.'.csv';

        $this->showExportGuide = true;

        return response()->streamDownload(
            fn () => print ($csv),
            $filename,
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }

    public function dismissExportGuide(): void
    {
        $this->showExportGuide = false;
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $wineryId = Auth::id();
        $vintage = (int) ($this->filterVintage ?: now()->year);

        // ── Stats globales (siempre visibles) ─────────────────────────────
        $stats = $this->buildStats($wineryId, $vintage);

        // ── Añadas disponibles para el filtro ─────────────────────────────
        $vintages = DB::table('harvests')
            ->where('winery_id', $wineryId)
            ->select('vintage')->distinct()
            ->orderByDesc('vintage')
            ->pluck('vintage');

        if ($vintages->isEmpty()) {
            $vintages = collect([now()->year]);
        }

        $fiscalYear = (int) ($this->filterFiscalYear ?: now()->year);

        // ── Datos del tab activo ──────────────────────────────────────────
        $tabData = match ($this->currentTab) {
            'entries' => $this->queryEntries($wineryId, $vintage),
            'elaboration' => $this->queryElaboration($wineryId, $vintage),
            'inventory' => $this->queryInventory($wineryId),
            'outputs' => $this->queryOutputs($wineryId, $vintage),
            'opening' => $this->buildFiscalYearOpeningBalances($wineryId, $fiscalYear),
            default => [],
        };

        // Fiscal years for apertura tab — same set as vintages
        $fiscalYears = $vintages;

        return view('livewire.winery.silicie.dashboard', [
            'stats' => $stats,
            'vintages' => $vintages,
            'vintage' => $vintage,
            'fiscalYears' => $fiscalYears,
            'fiscalYear' => $fiscalYear,
            'tabData' => $tabData,
        ]);
    }

    // ── Libro I — Entradas ────────────────────────────────────────────────

    private function queryEntries(int $wineryId, int $vintage): array
    {
        $recepciones = DB::table('harvests as h')
            ->where('h.winery_id', $wineryId)
            ->where('h.vintage', $vintage)
            ->leftJoin('grape_reception_batches as grb', 'grb.id', '=', 'h.batch_id')
            ->leftJoin('users as viti', 'viti.id', '=', 'grb.viticulturist_id')
            ->leftJoin('plot_plantings as pp', 'pp.id', '=', 'h.plot_planting_id')
            ->leftJoin('grape_varieties as gv', 'gv.id', '=', 'pp.grape_variety_id')
            ->select([
                'h.id',
                'h.harvest_start_date',
                'h.total_weight',
                'h.health_status',
                'h.baume_degree',
                'viti.name as viticulturist_name',
                'gv.name  as variety_name',
            ])
            ->orderByDesc('h.harvest_start_date')
            ->limit(200)
            ->get();

        // External grapes / must / bulk wine purchased
        $externas = DB::table('external_grapes as eg')
            ->where('eg.user_id', $wineryId)
            ->where('eg.vintage_year', $vintage)
            ->leftJoin('grape_varieties as gv', 'gv.id', '=', 'eg.grape_variety_id')
            ->select([
                'eg.id',
                'eg.entry_date',
                'eg.grape_type',
                'eg.total_weight_kg',
                'eg.supplier_name',
                'eg.protection_level',
                'gv.name as variety_name',
            ])
            ->orderByDesc('eg.entry_date')
            ->limit(200)
            ->get();

        $totals = [
            'recepciones' => $recepciones->count(),
            'kg_total' => $recepciones->sum('total_weight'),
            'externas_count' => $externas->count(),
            'externas_kg' => $externas->sum('total_weight_kg'),
        ];

        return compact('recepciones', 'externas', 'totals');
    }

    // ── Libro II — Elaboración ────────────────────────────────────────────

    private function queryElaboration(int $wineryId, int $vintage): array
    {
        $steps = DB::table('wine_process_details as wpd')
            ->join('wines as w', 'w.id', '=', 'wpd.wine_id')
            ->where('w.user_id', $wineryId)
            ->where('w.vintage', $vintage)
            ->leftJoin('containers as c', 'c.id', '=', 'wpd.container_id')
            ->select([
                'wpd.id',
                'wpd.process_type',
                'wpd.start_date',
                'wpd.end_date',
                'wpd.quantity',
                'wpd.observations',
                'w.name     as wine_name',
                'w.wine_type',
                'c.name     as container_name',
            ])
            ->orderBy('w.name')
            ->orderBy('wpd.start_date')
            ->limit(200)
            ->get();

        $losses = DB::table('wine_losses as wl')
            ->join('wines as w', 'w.id', '=', 'wl.wine_id')
            ->where('w.user_id', $wineryId)
            ->where('w.vintage', $vintage)
            ->leftJoin('containers as c', 'c.id', '=', 'wl.container_id')
            ->select([
                'wl.id',
                'wl.loss_date',
                'wl.loss_type',
                'wl.loss_authorization',
                'wl.quantity',
                'wl.notes',
                'w.name   as wine_name',
                'c.name   as container_name',
            ])
            ->orderByDesc('wl.loss_date')
            ->limit(100)
            ->get();

        return compact('steps', 'losses');
    }

    // ── Libro III — Existencias ────────────────────────────────────────────

    private function queryInventory(int $wineryId): array
    {
        // Cosecha (uva) por contenedor
        $stockHarvest = DB::table('containers as c')
            ->where('c.user_id', $wineryId)
            ->where('c.archived', false)
            ->where('c.used_capacity', '>', 0)
            ->leftJoin('container_rooms as cr', 'cr.id', '=', 'c.container_room_id')
            ->select([
                'c.id         as container_id',
                'c.name       as container_name',
                'c.capacity',
                'c.used_capacity as current_quantity',
                DB::raw('NULL as wine_id'),
                DB::raw('"Uva / cosecha" as wine_name'),
                DB::raw('"harvest" as wine_type'),
                DB::raw('NULL as vintage'),
                DB::raw('"harvest" as wine_status'),
                'cr.name      as room_name',
            ])
            ->orderBy('cr.name')
            ->orderBy('c.name')
            ->get();

        // Vino elaborado por contenedor
        $stock = DB::table('containers as c')
            ->where('c.user_id', $wineryId)
            ->where('c.archived', false)
            ->where('c.wine_volume_liters', '>', 0)
            ->leftJoin('container_current_states as ccs', 'ccs.container_id', '=', 'c.id')
            ->leftJoin('wines as w', 'w.id', '=', 'ccs.wine_id')
            ->leftJoin('container_rooms as cr', 'cr.id', '=', 'c.container_room_id')
            ->select([
                'c.id         as container_id',
                'c.name       as container_name',
                'c.capacity',
                'c.wine_volume_liters as current_quantity',
                'w.id         as wine_id',
                'w.name       as wine_name',
                'w.wine_type',
                'w.vintage',
                'w.status     as wine_status',
                'cr.name      as room_name',
            ])
            ->orderBy('cr.name')
            ->orderBy('c.name')
            ->get();

        $byWine = $stock->groupBy('wine_id')->map(fn ($rows) => [
            'wine_name' => $rows->first()->wine_name,
            'wine_type' => $rows->first()->wine_type,
            'vintage' => $rows->first()->vintage,
            'wine_status' => $rows->first()->wine_status,
            'total_liters' => $rows->sum('current_quantity'),
            'containers' => $rows->count(),
        ])->values();

        $totals = [
            'total_liters' => $stock->sum('current_quantity'),
            'harvest_kg' => $stockHarvest->sum('current_quantity'),
            'container_count' => $stock->count() + $stockHarvest->count(),
            'wine_count' => $stock->pluck('wine_id')->filter()->unique()->count(),
        ];

        // Último snapshot disponible (excluye mostos — solo vino elaborado)
        $lastSnapshot = WineStockSnapshot::where('user_id', $wineryId)
            ->where('is_must', false)
            ->orderByDesc('snapshot_date')
            ->value('snapshot_date');

        return compact('stock', 'stockHarvest', 'byWine', 'totals', 'lastSnapshot');
    }

    // ── Libro IV — Salidas ────────────────────────────────────────────────

    private function queryOutputs(int $wineryId, int $vintage): array
    {
        // Ventas (facturas)
        $ventas = DB::table('invoices as i')
            ->where('i.user_id', $wineryId)
            ->whereIn('i.status', ['sent', 'paid'])
            ->whereYear('i.invoice_date', $vintage)
            ->leftJoin('clients as cl', 'cl.id', '=', 'i.client_id')
            ->select([
                'i.id',
                'i.invoice_date',
                'i.invoice_number',
                'i.total_amount',
                'i.status',
                DB::raw("COALESCE(cl.company_name, CONCAT(COALESCE(cl.first_name,''),' ',COALESCE(cl.last_name,''))) as client_name"),
            ])
            ->orderByDesc('i.invoice_date')
            ->limit(100)
            ->get();

        // Pérdidas
        $perdidas = DB::table('wine_losses as wl')
            ->join('wines as w', 'w.id', '=', 'wl.wine_id')
            ->where('w.user_id', $wineryId)
            ->where('w.vintage', $vintage)
            ->select([
                'wl.id',
                'wl.loss_date',
                'wl.loss_type',
                'wl.loss_authorization',
                'wl.quantity',
                'wl.notes',
                'w.name as wine_name',
            ])
            ->orderByDesc('wl.loss_date')
            ->limit(100)
            ->get();

        // Subproductos (orujo, lías, vinaza)
        $subproductos = DB::table('wine_subproducts as ws')
            ->where('ws.user_id', $wineryId)
            ->whereYear('ws.subproduct_date', $vintage)
            ->leftJoin('wines as w', 'w.id', '=', 'ws.wine_id')
            ->select([
                'ws.id',
                'ws.subproduct_date',
                'ws.type',
                'ws.destination',
                'ws.destination_name',
                'ws.quantity',
                'ws.lot_number',
                'w.name as wine_name',
            ])
            ->orderByDesc('ws.subproduct_date')
            ->limit(100)
            ->get();

        $totals = [
            'ventas_count' => $ventas->count(),
            'ventas_amount' => $ventas->sum('total_amount'),
            'perdidas_qty' => $perdidas->sum('quantity'),
            'subproductos_qty' => $subproductos->sum('quantity'),
        ];

        return compact('ventas', 'perdidas', 'subproductos', 'totals');
    }

    // ── Apertura de ejercicio fiscal (SILICIE A22) ────────────────────────

    /**
     * Calculates opening stock balances for the fiscal year (Jan 1).
     * SILICIE movement A22 (Entrada almacén auxiliar) is used when declaring
     * opening balances at the start of a new fiscal year.
     *
     * The balance is taken from the most recent snapshot before Jan 1 of $fiscalYear,
     * grouped by wine type and mosto/wine distinction for AEAT compliance.
     */
    private function buildFiscalYearOpeningBalances(int $wineryId, int $fiscalYear): array
    {
        $openingDate = "{$fiscalYear}-01-01";

        // Most recent snapshot before Jan 1
        $snapshotDate = DB::table('wine_stock_snapshots')
            ->where('user_id', $wineryId)
            ->whereDate('snapshot_date', '<', $openingDate)
            ->orderByDesc('snapshot_date')
            ->value('snapshot_date');

        if (! $snapshotDate) {
            return [
                'fiscal_year' => $fiscalYear,
                'snapshot_date' => null,
                'rows' => [],
                'total_hl' => 0,
            ];
        }

        $rows = DB::table('wine_stock_snapshots as wss')
            ->where('wss.user_id', $wineryId)
            ->where('wss.snapshot_date', $snapshotDate)
            ->select([
                'wss.wine_type',
                'wss.is_must',
                DB::raw('SUM(wss.quantity_liters) / 100 as hl'),
                DB::raw('COUNT(DISTINCT wss.wine_id) as wine_count'),
            ])
            ->groupBy('wss.wine_type', 'wss.is_must')
            ->orderBy('wss.is_must')
            ->orderBy('wss.wine_type')
            ->get();

        $wineCategories = \App\Livewire\Winery\Silicie\Infovi::WINE_CATEGORIES;
        $result = [];
        $total = 0;

        foreach ($rows as $row) {
            $hl = round((float) $row->hl, 3);
            $label = $row->is_must
                ? 'Mosto ('.($wineCategories[$row->wine_type] ?? $row->wine_type).')'
                : ($wineCategories[$row->wine_type] ?? $row->wine_type);
            $result[] = [
                'wine_type' => $row->wine_type,
                'is_must' => (bool) $row->is_must,
                'label' => $label,
                'hl' => $hl,
                'wine_count' => (int) $row->wine_count,
                // SILICIE 2.0 format for A22 row
                'silicie_row' => [
                    'TIPO_MOVIMIENTO' => 'A22',
                    'FECHA_OPERACION' => \Carbon\Carbon::parse($openingDate)->format('d/m/Y'),
                    'TIPO_DOCUMENTO' => 'OTR',
                    'PERIODO_FISCAL' => $fiscalYear.'-01',
                    'CODIGO_NC' => $row->is_must ? '22043096' : '22042199',
                    'DESCRIPCION_PRODUCTO' => $label,
                    'CANTIDAD' => number_format($hl, 3, ',', ''),
                    'UNIDAD_MEDIDA' => 'HL',
                    'NUM_DOCUMENTO' => __('APERTURA-').$fiscalYear,
                    'OBSERVACIONES' => __('Apertura ejercicio ').$fiscalYear,
                ],
            ];
            $total += $hl;
        }

        return [
            'fiscal_year' => $fiscalYear,
            'snapshot_date' => $snapshotDate,
            'rows' => $result,
            'total_hl' => round($total, 3),
        ];
    }

    // ── KPIs globales ─────────────────────────────────────────────────────

    private function buildStats(int $wineryId, int $vintage): array
    {
        $kgReceived = DB::table('harvests')
            ->where('winery_id', $wineryId)
            ->where('vintage', $vintage)
            ->sum('total_weight');

        $wineryLiters = DB::table('container_current_states as ccs')
            ->join('containers as c', 'c.id', '=', 'ccs.container_id')
            ->where('c.user_id', $wineryId)
            ->where('ccs.current_quantity', '>', 0)
            ->sum('ccs.current_quantity');

        $activeWines = Wine::where('user_id', $wineryId)
            ->where('vintage', $vintage)
            ->whereIn('status', ['in_progress', 'aged'])
            ->count();

        $outputs = DB::table('invoices')
            ->where('user_id', $wineryId)
            ->whereIn('status', ['sent', 'paid'])
            ->whereYear('invoice_date', $vintage)
            ->count();

        return compact('kgReceived', 'wineryLiters', 'activeWines', 'outputs');
    }
}
