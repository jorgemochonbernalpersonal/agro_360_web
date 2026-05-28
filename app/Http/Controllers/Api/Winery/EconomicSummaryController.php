<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Wine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EconomicSummaryController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user   = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $userId = $user->id;
        $year   = $request->integer('year', now()->year);

        // ── Ventas por cliente (top 10) ───────────────────────────────────────
        $salesByClient = Invoice::where('invoices.user_id', $userId)
            ->where('invoices.invoice_type', '!=', 'grape_purchase')
            ->whereYear('invoices.invoice_date', $year)
            ->whereNotNull('invoices.client_id')
            ->join('clients', 'invoices.client_id', '=', 'clients.id')
            ->selectRaw('
                clients.id,
                COALESCE(clients.company_name, CONCAT(COALESCE(clients.first_name,"")," ",COALESCE(clients.last_name,""))) as client_name,
                SUM(invoices.total_amount) as total_amount,
                COUNT(invoices.id) as invoice_count
            ')
            ->groupBy('clients.id', 'clients.company_name', 'clients.first_name', 'clients.last_name')
            ->orderByDesc('total_amount')
            ->limit(10)
            ->get()
            ->map(fn ($r) => [
                'client_id'   => $r->id,
                'client_name' => trim($r->client_name),
                'amount'      => (float) $r->total_amount,
                'count'       => (int) $r->invoice_count,
            ]);

        // ── Ventas por tipo de vino ───────────────────────────────────────────
        $salesByWineType = Invoice::where('invoices.user_id', $userId)
            ->where('invoices.invoice_type', '!=', 'grape_purchase')
            ->whereYear('invoices.invoice_date', $year)
            ->join('invoice_items', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->join('wine_lots', 'invoice_items.wine_lot_id', '=', 'wine_lots.id')
            ->join('wines', 'wine_lots.wine_id', '=', 'wines.id')
            ->selectRaw('
                wines.wine_type,
                SUM(invoice_items.unit_price * invoice_items.quantity) as amount,
                SUM(invoice_items.quantity) as quantity
            ')
            ->groupBy('wines.wine_type')
            ->orderByDesc('amount')
            ->get()
            ->map(fn ($r) => [
                'wine_type'  => $r->wine_type,
                'type_label' => __(Wine::WINE_TYPES[$r->wine_type] ?? $r->wine_type),
                'amount'     => (float) $r->amount,
                'quantity'   => (float) $r->quantity,
            ]);

        // ── Coste de materias primas (liquidaciones de uva) ───────────────────
        $grapeCost = Invoice::where('user_id', $userId)
            ->where('invoice_type', 'grape_purchase')
            ->whereYear('invoice_date', $year)
            ->selectRaw('
                SUM(CASE WHEN payment_status = "paid"  THEN total_amount ELSE 0 END) as paid,
                SUM(CASE WHEN payment_status != "paid" THEN total_amount ELSE 0 END) as pending,
                SUM(total_amount) as total,
                COUNT(*) as count
            ')
            ->first();

        // ── Totales de ventas ─────────────────────────────────────────────────
        $salesTotal = Invoice::where('user_id', $userId)
            ->where('invoice_type', '!=', 'grape_purchase')
            ->whereYear('invoice_date', $year)
            ->selectRaw('
                SUM(CASE WHEN payment_status = "paid"  THEN total_amount ELSE 0 END) as paid,
                SUM(CASE WHEN payment_status != "paid" THEN total_amount ELSE 0 END) as pending,
                SUM(total_amount) as total,
                COUNT(*) as count
            ')
            ->first();

        // ── Margen bruto estimado ─────────────────────────────────────────────
        $totalRevenue  = (float) ($salesTotal->total ?? 0);
        $totalGrapeCost= (float) ($grapeCost->total ?? 0);
        $grossMargin   = $totalRevenue - $totalGrapeCost;
        $grossMarginPct= $totalRevenue > 0 ? round(($grossMargin / $totalRevenue) * 100, 1) : 0.0;

        // ── Ventas por trimestre ──────────────────────────────────────────────
        $salesByQuarter = Invoice::where('user_id', $userId)
            ->where('invoice_type', '!=', 'grape_purchase')
            ->whereYear('invoice_date', $year)
            ->selectRaw('QUARTER(invoice_date) as quarter, SUM(total_amount) as amount, COUNT(*) as count')
            ->groupBy('quarter')
            ->orderBy('quarter')
            ->get()
            ->map(fn ($r) => [
                'quarter' => "Q{$r->quarter}",
                'amount'  => (float) $r->amount,
                'count'   => (int) $r->count,
            ])->values();

        return response()->json([
            'year' => $year,
            'revenue' => [
                'total'   => $totalRevenue,
                'paid'    => (float) ($salesTotal->paid ?? 0),
                'pending' => (float) ($salesTotal->pending ?? 0),
                'count'   => (int) ($salesTotal->count ?? 0),
            ],
            'grape_cost' => [
                'total'   => $totalGrapeCost,
                'paid'    => (float) ($grapeCost->paid ?? 0),
                'pending' => (float) ($grapeCost->pending ?? 0),
                'count'   => (int) ($grapeCost->count ?? 0),
            ],
            'gross_margin' => [
                'amount'     => $grossMargin,
                'percentage' => $grossMarginPct,
            ],
            'top_clients'       => $salesByClient,
            'sales_by_wine_type'=> $salesByWineType,
            'sales_by_quarter'  => $salesByQuarter,
        ]);
    }
}
