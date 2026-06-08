<?php

namespace App\Livewire\Winery\Financial;

use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Stats extends Component
{
    public int $year;

    public string $compareWith = 'previous'; // previous, none

    public function mount(): void
    {
        $this->year = now()->year;
    }

    public function render()
    {
        $userId = Auth::id();

        $availableYears = Invoice::where('user_id', $userId)
            ->selectRaw('YEAR(invoice_date) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->toArray();

        if (empty($availableYears)) {
            $availableYears = [now()->year];
        }

        // ═══════════════════════════════════════════════════════════
        // KPIs AÑO ACTUAL
        // ═══════════════════════════════════════════════════════════

        $salesBase = Invoice::where('user_id', $userId)
            ->whereNull('viticulturist_id')
            ->whereNotNull('client_id')
            ->whereYear('invoice_date', $this->year)
            ->where('status', '!=', 'cancelled');

        $totalRevenue = (float) (clone $salesBase)->where('status', 'paid')->sum('total_amount');
        $pendingRevenue = (float) (clone $salesBase)->whereIn('status', ['sent', 'draft'])
            ->where('payment_status', '!=', 'paid')->sum('total_amount');
        $invoiceCount = (clone $salesBase)->count();
        $paidCount = (clone $salesBase)->where('status', 'paid')->count();
        $averageInvoice = $invoiceCount > 0 ? $totalRevenue / $invoiceCount : 0;

        // Gastos compra uva
        $grapeCost = (float) Invoice::where('user_id', $userId)
            ->whereNotNull('viticulturist_id')
            ->whereYear('invoice_date', $this->year)
            ->where('status', 'paid')
            ->sum('total_amount');

        // Mantenimiento contenedores
        $maintenanceCost = (float) DB::table('container_maintenances as cm')
            ->join('containers as c', 'c.id', '=', 'cm.container_id')
            ->where('c.user_id', $userId)
            ->where('cm.status', 'completed')
            ->whereYear('cm.performed_date', $this->year)
            ->sum('cm.cost');

        $totalCosts = $grapeCost + $maintenanceCost;
        $grossMargin = $totalRevenue - $totalCosts;
        $grossMarginPct = $totalRevenue > 0 ? ($grossMargin / $totalRevenue) * 100 : 0;

        // Tasa de cobro
        $collectionRate = $invoiceCount > 0 && $paidCount > 0
            ? ($paidCount / $invoiceCount) * 100
            : 0;

        // Facturas vencidas
        $overdueAmount = (float) Invoice::where('user_id', $userId)
            ->whereNull('viticulturist_id')
            ->where('payment_status', 'overdue')
            ->where('status', '!=', 'cancelled')
            ->sum('total_amount');
        $overdueCount = Invoice::where('user_id', $userId)
            ->whereNull('viticulturist_id')
            ->where('payment_status', 'overdue')
            ->where('status', '!=', 'cancelled')
            ->count();

        // ═══════════════════════════════════════════════════════════
        // COMPARATIVA INTERANUAL
        // ═══════════════════════════════════════════════════════════

        $prevYear = $this->year - 1;

        $prevRevenue = (float) Invoice::where('user_id', $userId)
            ->whereNull('viticulturist_id')
            ->whereNotNull('client_id')
            ->whereYear('invoice_date', $prevYear)
            ->where('status', 'paid')
            ->sum('total_amount');

        $prevGrapeCost = (float) Invoice::where('user_id', $userId)
            ->whereNotNull('viticulturist_id')
            ->whereYear('invoice_date', $prevYear)
            ->where('status', 'paid')
            ->sum('total_amount');

        $prevInvoiceCount = Invoice::where('user_id', $userId)
            ->whereNull('viticulturist_id')
            ->whereNotNull('client_id')
            ->whereYear('invoice_date', $prevYear)
            ->where('status', '!=', 'cancelled')
            ->count();

        $revenueGrowth = $prevRevenue > 0 ? (($totalRevenue - $prevRevenue) / $prevRevenue) * 100 : null;
        $costGrowth = $prevGrapeCost > 0 ? (($grapeCost - $prevGrapeCost) / $prevGrapeCost) * 100 : null;

        // ═══════════════════════════════════════════════════════════
        // EVOLUCIÓN MENSUAL (año actual vs anterior)
        // ═══════════════════════════════════════════════════════════

        $monthlyRevenue = Invoice::where('user_id', $userId)
            ->whereNull('viticulturist_id')
            ->whereNotNull('client_id')
            ->where('status', 'paid')
            ->whereYear('invoice_date', $this->year)
            ->selectRaw('MONTH(invoice_date) as month, SUM(total_amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $prevMonthlyRevenue = Invoice::where('user_id', $userId)
            ->whereNull('viticulturist_id')
            ->whereNotNull('client_id')
            ->where('status', 'paid')
            ->whereYear('invoice_date', $prevYear)
            ->selectRaw('MONTH(invoice_date) as month, SUM(total_amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $monthlyData = collect(range(1, 12))->map(fn ($m) => [
            'month' => $m,
            'label' => now()->month($m)->format('M'),
            'current' => (float) ($monthlyRevenue->get($m) ?? 0),
            'previous' => (float) ($prevMonthlyRevenue->get($m) ?? 0),
        ]);

        // ═══════════════════════════════════════════════════════════
        // RENTABILIDAD POR TIPO DE VINO
        // ═══════════════════════════════════════════════════════════

        $revenueByWineType = DB::table('invoices as i')
            ->join('invoice_items as ii', 'ii.invoice_id', '=', 'i.id')
            ->join('wine_lots as pl', 'pl.id', '=', 'ii.wine_lot_id')
            ->where('i.user_id', $userId)
            ->whereNull('i.viticulturist_id')
            ->where('i.status', 'paid')
            ->whereYear('i.invoice_date', $this->year)
            ->select(
                'pl.wine_type',
                DB::raw('SUM(ii.total) as revenue'),
                DB::raw('COUNT(DISTINCT i.id) as invoice_count'),
                DB::raw('SUM(ii.quantity) as units_sold'),
            )
            ->groupBy('pl.wine_type')
            ->orderByDesc('revenue')
            ->get();

        // Coste por tipo (compra uva asociada a vinos del tipo)
        $costByWineType = DB::table('wines')
            ->where('user_id', $userId)
            ->whereNotNull('wine_type')
            ->select('wine_type', DB::raw('SUM(volume_liters) as total_liters'), DB::raw('COUNT(*) as count'))
            ->groupBy('wine_type')
            ->get()
            ->keyBy('wine_type');

        // ═══════════════════════════════════════════════════════════
        // TOP CLIENTES + TOP PRODUCTOS
        // ═══════════════════════════════════════════════════════════

        $topClients = Invoice::where('user_id', $userId)
            ->whereNull('viticulturist_id')
            ->whereNotNull('client_id')
            ->where('status', 'paid')
            ->whereYear('invoice_date', $this->year)
            ->with('client:id,first_name,last_name,company_name')
            ->select('client_id', DB::raw('SUM(total_amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('client_id')
            ->orderByDesc('total')
            ->take(8)
            ->get();

        $topProducts = DB::table('invoices as i')
            ->join('invoice_items as ii', 'ii.invoice_id', '=', 'i.id')
            ->join('wine_lots as pl', 'pl.id', '=', 'ii.wine_lot_id')
            ->where('i.user_id', $userId)
            ->whereNull('i.viticulturist_id')
            ->where('i.status', 'paid')
            ->whereYear('i.invoice_date', $this->year)
            ->select(
                'pl.name',
                'pl.wine_type',
                DB::raw('SUM(ii.total) as revenue'),
                DB::raw('SUM(ii.quantity) as units'),
            )
            ->groupBy('pl.name', 'pl.wine_type')
            ->orderByDesc('revenue')
            ->take(8)
            ->get();

        // ═══════════════════════════════════════════════════════════
        // FACTURAS PENDIENTES
        // ═══════════════════════════════════════════════════════════

        $pendingInvoices = Invoice::where('user_id', $userId)
            ->whereNull('viticulturist_id')
            ->whereIn('payment_status', ['unpaid', 'overdue'])
            ->where('status', '!=', 'cancelled')
            ->with('client:id,first_name,last_name,company_name')
            ->orderBy('invoice_date')
            ->take(10)
            ->get();

        // ═══════════════════════════════════════════════════════════
        // STOCK DE PRODUCTOS
        // ═══════════════════════════════════════════════════════════

        $productStock = DB::table('wine_lots')
            ->join('wines', 'wines.id', '=', 'wine_lots.wine_id')
            ->where('wines.user_id', $userId)
            ->select(
                'wine_lots.wine_type',
                DB::raw('SUM(wine_lots.quantity) as total_units'),
                DB::raw('SUM(wine_lots.available_quantity) as available'),
                DB::raw('SUM(wine_lots.reserved_quantity) as reserved'),
                DB::raw('SUM(wine_lots.sold_quantity) as sold'),
                DB::raw('SUM(wine_lots.sold_quantity * wine_lots.price_per_unit) as total_revenue'),
                DB::raw('SUM(wine_lots.quantity * COALESCE(wine_lots.cost_price, 0)) as total_cost'),
            )
            ->groupBy('wine_lots.wine_type')
            ->get();

        return view('livewire.winery.financial.stats', [
            'availableYears' => $availableYears,
            // KPIs
            'totalRevenue' => $totalRevenue,
            'pendingRevenue' => $pendingRevenue,
            'invoiceCount' => $invoiceCount,
            'paidCount' => $paidCount,
            'averageInvoice' => $averageInvoice,
            'grapeCost' => $grapeCost,
            'maintenanceCost' => $maintenanceCost,
            'totalCosts' => $totalCosts,
            'grossMargin' => $grossMargin,
            'grossMarginPct' => $grossMarginPct,
            'collectionRate' => $collectionRate,
            'overdueAmount' => $overdueAmount,
            'overdueCount' => $overdueCount,
            // Comparativa
            'prevRevenue' => $prevRevenue,
            'revenueGrowth' => $revenueGrowth,
            'costGrowth' => $costGrowth,
            'prevInvoiceCount' => $prevInvoiceCount,
            // Mensual
            'monthlyData' => $monthlyData,
            // Rentabilidad
            'revenueByWineType' => $revenueByWineType,
            'costByWineType' => $costByWineType,
            // Rankings
            'topClients' => $topClients,
            'topProducts' => $topProducts,
            // Pendientes
            'pendingInvoices' => $pendingInvoices,
            // Stock
            'productStock' => $productStock,
        ])->layout('layouts.app', [
            'title' => __('Estadísticas Financieras de Bodega'),
            'description' => __('KPIs, tendencias y comparativas económicas de tu bodega'),
        ]);
    }
}
