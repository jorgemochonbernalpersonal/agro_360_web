<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Controller;
use App\Models\Harvest;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LabelBatch;
use App\Models\Wine;
use App\Models\WineBottling;
use App\Models\WineryViticulturist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user   = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $userId = $user->id;
        $year   = $request->integer('year', now()->year);

        // ── Facturas de venta ─────────────────────────────────────────────────
        $invoicesBase = Invoice::where('user_id', $userId)
            ->where('invoice_type', '!=', 'grape_purchase')
            ->whereYear('invoice_date', $year);

        $salesStats = (clone $invoicesBase)->selectRaw('
            COUNT(*) as total_count,
            SUM(CASE WHEN payment_status = "paid" THEN total_amount ELSE 0 END) as paid_amount,
            SUM(CASE WHEN payment_status != "paid" THEN total_amount ELSE 0 END) as pending_amount,
            SUM(total_amount) as total_amount
        ')->first();

        // ── Liquidaciones de uva ──────────────────────────────────────────────
        $grapePurchaseBase = Invoice::where('user_id', $userId)
            ->where('invoice_type', 'grape_purchase')
            ->whereYear('invoice_date', $year);

        $grapeStats = (clone $grapePurchaseBase)->selectRaw('
            COUNT(*) as total_count,
            SUM(CASE WHEN payment_status = "paid" THEN total_amount ELSE 0 END) as paid_amount,
            SUM(CASE WHEN payment_status != "paid" THEN total_amount ELSE 0 END) as pending_amount,
            SUM(total_amount) as total_amount
        ')->first();

        // ── Recepciones de uva ────────────────────────────────────────────────
        $harvestStats = Harvest::where('winery_id', $userId)
            ->where('status', 'active')
            ->whereYear('harvest_start_date', $year)
            ->selectRaw('COUNT(*) as total_count, SUM(total_weight) as total_kg')
            ->first();

        // ── Vinos ─────────────────────────────────────────────────────────────
        $wineStats = Wine::forUser($userId)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "in_progress" THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN status = "bottled"     THEN 1 ELSE 0 END) as bottled,
                SUM(CASE WHEN status = "sold"        THEN 1 ELSE 0 END) as sold
            ')->first();

        // ── Embotellado ───────────────────────────────────────────────────────
        $bottlingStats = WineBottling::whereHas('wine', fn ($q) => $q->where('user_id', $userId))
            ->whereYear('bottling_date', $year)
            ->selectRaw('COUNT(*) as total_sessions, SUM(quantity_bottles) as total_bottles')
            ->first();

        // ── Etiquetas ─────────────────────────────────────────────────────────
        $labelStats = LabelBatch::forUser($userId)
            ->selectRaw('
                SUM(total_quantity) as total_labels,
                SUM(used_quantity) as used_labels,
                SUM(total_quantity - used_quantity - wasted_quantity) as available_labels
            ')->first();

        // ── Viticultores vinculados ───────────────────────────────────────────
        $viticulturistCount = WineryViticulturist::where('winery_id', $userId)->count();

        // ── Ventas por mes ────────────────────────────────────────────────────
        $salesByMonth = Invoice::where('user_id', $userId)
            ->where('invoice_type', '!=', 'grape_purchase')
            ->whereYear('invoice_date', $year)
            ->selectRaw('MONTH(invoice_date) as month, SUM(total_amount) as amount, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month')
            ->map(fn ($r) => ['amount' => (float) $r->amount, 'count' => (int) $r->count]);

        return response()->json([
            'year' => $year,
            'sales' => [
                'total_count'    => (int) $salesStats->total_count,
                'total_amount'   => (float) ($salesStats->total_amount ?? 0),
                'paid_amount'    => (float) ($salesStats->paid_amount ?? 0),
                'pending_amount' => (float) ($salesStats->pending_amount ?? 0),
            ],
            'grape_purchases' => [
                'total_count'    => (int) $grapeStats->total_count,
                'total_amount'   => (float) ($grapeStats->total_amount ?? 0),
                'paid_amount'    => (float) ($grapeStats->paid_amount ?? 0),
                'pending_amount' => (float) ($grapeStats->pending_amount ?? 0),
            ],
            'harvest' => [
                'total_receptions' => (int) ($harvestStats->total_count ?? 0),
                'total_kg'         => (float) ($harvestStats->total_kg ?? 0),
            ],
            'wines' => [
                'total'       => (int) $wineStats->total,
                'in_progress' => (int) $wineStats->in_progress,
                'bottled'     => (int) $wineStats->bottled,
                'sold'        => (int) $wineStats->sold,
            ],
            'bottling' => [
                'total_sessions' => (int) ($bottlingStats->total_sessions ?? 0),
                'total_bottles'  => (int) ($bottlingStats->total_bottles ?? 0),
            ],
            'labels' => [
                'total'     => (int) ($labelStats->total_labels ?? 0),
                'used'      => (int) ($labelStats->used_labels ?? 0),
                'available' => (int) ($labelStats->available_labels ?? 0),
            ],
            'viticulturists_linked' => $viticulturistCount,
            'sales_by_month'        => $salesByMonth,
        ]);
    }
}
