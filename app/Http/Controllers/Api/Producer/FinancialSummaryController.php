<?php

namespace App\Http\Controllers\Api\Producer;

use App\Http\Controllers\Controller;
use App\Models\Container;
use App\Models\ContainerMaintenance;
use App\Models\Invoice;
use App\Models\Wine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinancialSummaryController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $userId = $user->id;
        $year = $request->integer('year', now()->year);

        // ── Ventas ───────────────────────────────────────────────────────────
        $salesTotal = Invoice::where('user_id', $userId)
            ->where('invoice_type', '!=', 'grape_purchase')
            ->whereYear('invoice_date', $year)
            ->selectRaw('
                SUM(total_amount) as total,
                SUM(CASE WHEN payment_status = "paid"  THEN total_amount ELSE 0 END) as paid,
                SUM(CASE WHEN payment_status != "paid" THEN total_amount ELSE 0 END) as pending,
                COUNT(*) as count
            ')
            ->first();

        // ── Coste uva ────────────────────────────────────────────────────────
        $grapeCost = Invoice::where('user_id', $userId)
            ->where('invoice_type', 'grape_purchase')
            ->whereYear('invoice_date', $year)
            ->selectRaw('
                SUM(total_amount) as total,
                SUM(CASE WHEN payment_status = "paid"  THEN total_amount ELSE 0 END) as paid,
                SUM(CASE WHEN payment_status != "paid" THEN total_amount ELSE 0 END) as pending,
                COUNT(*) as count
            ')
            ->first();

        // ── Coste mantenimiento ──────────────────────────────────────────────
        $maintenanceCost = (float) ContainerMaintenance::whereHas('container', fn ($q) => $q->where('user_id', $userId))
            ->where('status', 'completed')
            ->whereYear('scheduled_date', $year)
            ->sum('cost');

        // ── Márgenes ─────────────────────────────────────────────────────────
        $revenue = (float) ($salesTotal->total ?? 0);
        $totalCost = (float) ($grapeCost->total ?? 0) + $maintenanceCost;
        $grossMargin = $revenue - $totalCost;
        $marginPct = $revenue > 0 ? round(($grossMargin / $revenue) * 100, 1) : 0.0;

        // ── Ventas por mes ───────────────────────────────────────────────────
        $salesByMonth = Invoice::where('user_id', $userId)
            ->where('invoice_type', '!=', 'grape_purchase')
            ->whereYear('invoice_date', $year)
            ->selectRaw('MONTH(invoice_date) as month, SUM(total_amount) as amount, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn ($r) => [
                'month' => (int) $r->month,
                'amount' => (float) $r->amount,
                'count' => (int) $r->count,
            ]);

        // ── Top clientes ─────────────────────────────────────────────────────
        $topClients = Invoice::where('invoices.user_id', $userId)
            ->where('invoices.invoice_type', '!=', 'grape_purchase')
            ->whereYear('invoices.invoice_date', $year)
            ->whereNotNull('invoices.client_id')
            ->join('clients', 'invoices.client_id', '=', 'clients.id')
            ->selectRaw('
                clients.id,
                COALESCE(clients.company_name, CONCAT(COALESCE(clients.first_name,"")," ",COALESCE(clients.last_name,""))) as client_name,
                SUM(invoices.total_amount) as amount,
                COUNT(invoices.id) as count
            ')
            ->groupBy('clients.id', 'clients.company_name', 'clients.first_name', 'clients.last_name')
            ->orderByDesc('amount')
            ->limit(10)
            ->get()
            ->map(fn ($r) => [
                'client_id' => $r->id,
                'client_name' => trim($r->client_name),
                'amount' => (float) $r->amount,
                'count' => (int) $r->count,
            ]);

        // ── Inventario vino ──────────────────────────────────────────────────
        $wineStats = Wine::where('user_id', $userId)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active,
                COALESCE(SUM(volume_liters), 0) as volume
            ')
            ->first();

        // ── Contenedores ─────────────────────────────────────────────────────
        $containerStats = Container::where('user_id', $userId)
            ->where('archived', false)
            ->selectRaw('COUNT(*) as total, COALESCE(SUM(capacity), 0) as capacity, COALESCE(SUM(used_capacity + wine_volume_liters), 0) as used')
            ->first();

        return response()->json([
            'year' => $year,
            'revenue' => [
                'total' => $revenue,
                'paid' => (float) ($salesTotal->paid ?? 0),
                'pending' => (float) ($salesTotal->pending ?? 0),
                'count' => (int) ($salesTotal->count ?? 0),
            ],
            'grape_cost' => [
                'total' => (float) ($grapeCost->total ?? 0),
                'paid' => (float) ($grapeCost->paid ?? 0),
                'pending' => (float) ($grapeCost->pending ?? 0),
                'count' => (int) ($grapeCost->count ?? 0),
            ],
            'maintenance_cost' => $maintenanceCost,
            'gross_margin' => [
                'amount' => $grossMargin,
                'percentage' => $marginPct,
            ],
            'sales_by_month' => $salesByMonth,
            'top_clients' => $topClients,
            'wine_inventory' => [
                'total' => (int) ($wineStats->total ?? 0),
                'active' => (int) ($wineStats->active ?? 0),
                'volume' => (float) ($wineStats->volume ?? 0),
            ],
            'containers' => [
                'total' => (int) ($containerStats->total ?? 0),
                'capacity' => (float) ($containerStats->capacity ?? 0),
                'used' => (float) ($containerStats->used ?? 0),
                'pct' => ((float) ($containerStats->capacity ?? 0)) > 0
                    ? round((float) ($containerStats->used ?? 0) / (float) $containerStats->capacity * 100, 1) : 0,
            ],
        ])->header('Cache-Control', 'private, max-age=600');
    }
}
