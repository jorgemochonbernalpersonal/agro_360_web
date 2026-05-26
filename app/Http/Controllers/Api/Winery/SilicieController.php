<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Controller;
use App\Models\Wine;
use App\Models\WineStockSnapshot;
use App\Services\Exporters\SilicieCsvExporter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SilicieController extends Controller
{
    // ─── GET /winery/silicie ──────────────────────────────────────────────────
    // Stats globales + añadas disponibles

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $request->validate(['vintage' => 'nullable|integer|min:1990|max:' . (now()->year + 1)]);

        $wineryId = $user->id;
        $vintage  = $request->integer('vintage', now()->year);

        $vintages = DB::table('harvests')
            ->where('winery_id', $wineryId)
            ->select('vintage')->distinct()
            ->orderByDesc('vintage')
            ->pluck('vintage');

        if ($vintages->isEmpty()) {
            $vintages = collect([now()->year]);
        }

        return response()->json([
            'data' => [
                'stats'    => $this->buildStats($wineryId, $vintage),
                'vintages' => $vintages->values(),
                'vintage'  => $vintage,
            ],
        ]);
    }

    // ─── GET /winery/silicie/entradas ─────────────────────────────────────────

    public function entries(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $request->validate(['vintage' => 'nullable|integer|min:1990|max:' . (now()->year + 1)]);

        $wineryId = $user->id;
        $vintage  = $request->integer('vintage', now()->year);

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
            ->limit(500)
            ->get();

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

        return response()->json([
            'data' => [
                'recepciones' => $recepciones,
                'externas'    => $externas,
                'totals'      => [
                    'recepciones'    => $recepciones->count(),
                    'kg_total'       => (float) $recepciones->sum('total_weight'),
                    'externas_count' => $externas->count(),
                    'externas_kg'    => (float) $externas->sum('total_weight_kg'),
                ],
            ],
        ]);
    }

    // ─── GET /winery/silicie/elaboracion ──────────────────────────────────────

    public function elaboration(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $request->validate(['vintage' => 'nullable|integer|min:1990|max:' . (now()->year + 1)]);

        $wineryId = $user->id;
        $vintage  = $request->integer('vintage', now()->year);

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
            ->limit(500)
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
            ->limit(200)
            ->get();

        return response()->json([
            'data' => compact('steps', 'losses'),
        ]);
    }

    // ─── GET /winery/silicie/existencias ──────────────────────────────────────

    public function inventory(Request $request): JsonResponse
    {
        $user     = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $wineryId = $user->id;

        $stockHarvest = DB::table('containers as c')
            ->where('c.user_id', $wineryId)
            ->where('c.archived', false)
            ->where('c.used_capacity', '>', 0)
            ->leftJoin('container_rooms as cr', 'cr.id', '=', 'c.container_room_id')
            ->select([
                'c.id            as container_id',
                'c.name          as container_name',
                'c.capacity',
                'c.used_capacity as current_quantity',
                DB::raw('"harvest" as wine_type'),
                'cr.name         as room_name',
            ])
            ->orderBy('cr.name')
            ->orderBy('c.name')
            ->get();

        $stock = DB::table('containers as c')
            ->where('c.user_id', $wineryId)
            ->where('c.archived', false)
            ->where('c.wine_volume_liters', '>', 0)
            ->leftJoin('container_current_states as ccs', 'ccs.container_id', '=', 'c.id')
            ->leftJoin('wines as w', 'w.id', '=', 'ccs.wine_id')
            ->leftJoin('container_rooms as cr', 'cr.id', '=', 'c.container_room_id')
            ->select([
                'c.id                  as container_id',
                'c.name                as container_name',
                'c.capacity',
                'c.wine_volume_liters  as current_quantity',
                'w.id                  as wine_id',
                'w.name                as wine_name',
                'w.wine_type',
                'w.vintage',
                'w.status              as wine_status',
                'cr.name               as room_name',
            ])
            ->orderBy('cr.name')
            ->orderBy('c.name')
            ->get();

        $byWine = $stock->groupBy('wine_id')->map(fn ($rows) => [
            'wine_name'    => $rows->first()->wine_name,
            'wine_type'    => $rows->first()->wine_type,
            'vintage'      => $rows->first()->vintage,
            'total_liters' => (float) $rows->sum('current_quantity'),
            'containers'   => $rows->count(),
        ])->values();

        $lastSnapshot = WineStockSnapshot::where('user_id', $wineryId)
            ->orderByDesc('snapshot_date')
            ->value('snapshot_date');

        return response()->json([
            'data' => [
                'stock'         => $stock,
                'stock_harvest' => $stockHarvest,
                'by_wine'       => $byWine,
                'last_snapshot' => $lastSnapshot,
                'totals'        => [
                    'total_liters'    => (float) $stock->sum('current_quantity'),
                    'harvest_kg'      => (float) $stockHarvest->sum('current_quantity'),
                    'container_count' => $stock->count() + $stockHarvest->count(),
                    'wine_count'      => $stock->pluck('wine_id')->filter()->unique()->count(),
                ],
            ],
        ]);
    }

    // ─── GET /winery/silicie/salidas ──────────────────────────────────────────

    public function outputs(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $request->validate(['vintage' => 'nullable|integer|min:1990|max:' . (now()->year + 1)]);

        $wineryId = $user->id;
        $vintage  = $request->integer('vintage', now()->year);

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
            ->limit(200)
            ->get();

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
            ->limit(200)
            ->get();

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

        return response()->json([
            'data' => [
                'ventas'       => $ventas,
                'perdidas'     => $perdidas,
                'subproductos' => $subproductos,
                'totals'       => [
                    'ventas_count'     => $ventas->count(),
                    'ventas_amount'    => (float) $ventas->sum('total_amount'),
                    'perdidas_qty'     => (float) $perdidas->sum('quantity'),
                    'subproductos_qty' => (float) $subproductos->sum('quantity'),
                ],
            ],
        ]);
    }

    // ─── GET /winery/silicie/apertura ─────────────────────────────────────────

    public function opening(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $request->validate(['fiscal_year' => 'nullable|integer|min:1990|max:' . (now()->year + 1)]);

        $wineryId   = $user->id;
        $fiscalYear = $request->integer('fiscal_year', now()->year);
        $openingDate = "{$fiscalYear}-01-01";

        $snapshotDate = DB::table('wine_stock_snapshots')
            ->where('user_id', $wineryId)
            ->whereDate('snapshot_date', '<', $openingDate)
            ->orderByDesc('snapshot_date')
            ->value('snapshot_date');

        if (!$snapshotDate) {
            return response()->json([
                'data' => [
                    'fiscal_year'   => $fiscalYear,
                    'snapshot_date' => null,
                    'rows'          => [],
                    'total_hl'      => 0,
                ],
            ]);
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
        $total  = 0;

        foreach ($rows as $row) {
            $hl    = round((float) $row->hl, 3);
            $label = $row->is_must
                ? 'Mosto (' . ($wineCategories[$row->wine_type] ?? $row->wine_type) . ')'
                : ($wineCategories[$row->wine_type] ?? $row->wine_type);

            $result[] = [
                'wine_type'  => $row->wine_type,
                'is_must'    => (bool) $row->is_must,
                'label'      => $label,
                'hl'         => $hl,
                'wine_count' => (int) $row->wine_count,
                'silicie_movement' => 'A22',
                'nc_code'    => $row->is_must ? '22043096' : '22042199',
            ];
            $total += $hl;
        }

        return response()->json([
            'data' => [
                'fiscal_year'   => $fiscalYear,
                'snapshot_date' => $snapshotDate,
                'rows'          => $result,
                'total_hl'      => round($total, 3),
            ],
        ]);
    }

    // ─── POST /winery/silicie/snapshot ───────────────────────────────────────

    public function snapshot(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $wineryId = $user->id;
        $today    = now()->toDateString();

        $rows = DB::table('container_current_states as ccs')
            ->join('containers as c', 'c.id', '=', 'ccs.container_id')
            ->join('wines as w', 'w.id', '=', 'ccs.wine_id')
            ->where('c.user_id', $wineryId)
            ->where('ccs.current_quantity', '>', 0)
            ->select([
                'w.id       as wine_id',
                'w.vintage',
                'w.wine_type',
                'w.is_must',
                DB::raw('SUM(ccs.current_quantity) as total_liters'),
                DB::raw('COUNT(DISTINCT ccs.container_id) as container_count'),
            ])
            ->groupBy('w.id', 'w.vintage', 'w.wine_type', 'w.is_must')
            ->get();

        $saved = 0;
        foreach ($rows as $row) {
            WineStockSnapshot::updateOrCreate(
                [
                    'user_id'       => $wineryId,
                    'wine_id'       => $row->wine_id,
                    'snapshot_date' => $today,
                ],
                [
                    'quantity_liters' => $row->total_liters,
                    'container_count' => $row->container_count,
                    'vintage'         => $row->vintage,
                    'wine_type'       => $row->wine_type,
                    'is_must'         => (bool) $row->is_must,
                    'created_by'      => $wineryId,
                ]
            );
            $saved++;
        }

        return response()->json([
            'message'       => __('Instantánea de existencias registrada correctamente.'),
            'snapshot_date' => $today,
            'wines_saved'   => $saved,
        ]);
    }

    // ─── GET /winery/silicie/export ───────────────────────────────────────────

    public function export(Request $request)
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $request->validate(['vintage' => 'nullable|integer|min:1990|max:' . (now()->year + 1)]);

        $wineryId = $user->id;
        $vintage  = $request->integer('vintage', now()->year);
        $csv      = (new SilicieCsvExporter())->export($wineryId, $vintage);
        $filename = "SILICIE_{$wineryId}_{$vintage}.csv";

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

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

        return [
            'kg_received'   => (float) $kgReceived,
            'winery_liters' => (float) $wineryLiters,
            'active_wines'  => (int) $activeWines,
            'outputs'       => (int) $outputs,
        ];
    }
}
