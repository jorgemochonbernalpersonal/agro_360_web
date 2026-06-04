<?php

namespace App\Livewire\Winery\Financial;

use App\Models\Container;
use App\Models\Invoice;
use App\Models\Wine;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Summary extends Component
{
    public int $year;

    public function mount(): void
    {
        $this->year = now()->year;
    }

    public function updatedYear(): void {}

    public function render()
    {
        $userId = Auth::id();

        // ── Ingresos: facturas de venta de productos ─────────────────────────
        $salesBase = Invoice::where('user_id', $userId)
            ->whereNull('viticulturist_id')
            ->whereNotNull('client_id')
            ->whereYear('invoice_date', $this->year);

        $totalRevenue = (float) (clone $salesBase)->where('status', 'paid')->sum('total_amount');
        $pendingRevenue = (float) (clone $salesBase)->whereIn('status', ['sent', 'draft'])
            ->where('payment_status', '!=', 'paid')->sum('total_amount');
        $invoiceCount = (clone $salesBase)->count();
        $paidCount = (clone $salesBase)->where('status', 'paid')->count();

        // ── Gastos: liquidaciones de compra de uva ───────────────────────────
        $grapeCostBase = Invoice::where('user_id', $userId)
            ->whereNotNull('viticulturist_id')
            ->whereYear('invoice_date', $this->year);

        $totalGrapeCost = (float) (clone $grapeCostBase)->where('status', 'paid')->sum('total_amount');
        $pendingGrapeCost = (float) (clone $grapeCostBase)->whereIn('status', ['sent', 'draft'])
            ->where('payment_status', '!=', 'paid')->sum('total_amount');

        // ── Costes de mantenimiento de contenedores ──────────────────────────
        $maintenanceCost = (float) DB::table('container_maintenances as cm')
            ->join('containers as c', 'c.id', '=', 'cm.container_id')
            ->where('c.user_id', $userId)
            ->where('cm.status', 'completed')
            ->whereYear('cm.performed_date', $this->year)
            ->sum('cm.cost');

        // ── Margen bruto ─────────────────────────────────────────────────────
        $grossMargin = $totalRevenue - $totalGrapeCost - $maintenanceCost;
        $grossMarginPct = $totalRevenue > 0 ? ($grossMargin / $totalRevenue) * 100 : 0;

        // ── Ingresos mensuales (ventas pagadas) ──────────────────────────────
        $monthlyRevenue = Invoice::where('user_id', $userId)
            ->whereNull('viticulturist_id')
            ->whereNotNull('client_id')
            ->where('status', 'paid')
            ->whereYear('invoice_date', $this->year)
            ->selectRaw('MONTH(invoice_date) as month, SUM(total_amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $monthlyData = collect(range(1, 12))->map(fn ($m) => [
            'month' => $m,
            'label' => now()->month($m)->format('M'),
            'total' => (float) ($monthlyRevenue->get($m)?->total ?? 0),
        ]);

        // ── Ingresos por tipo de vino ─────────────────────────────────────────
        // Basado en vinos en bodega con sus características
        $winesByType = Wine::where('user_id', $userId)
            ->select('wine_type', DB::raw('COUNT(*) as count'), DB::raw('SUM(volume_liters) as total_liters'))
            ->groupBy('wine_type')
            ->get();

        // ── Top clientes por facturación ──────────────────────────────────────
        $topClients = Invoice::where('user_id', $userId)
            ->whereNull('viticulturist_id')
            ->whereNotNull('client_id')
            ->where('status', 'paid')
            ->whereYear('invoice_date', $this->year)
            ->with('client:id,first_name,last_name,company_name')
            ->select('client_id', DB::raw('SUM(total_amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('client_id')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        // ── Años disponibles para filtro ──────────────────────────────────────
        $availableYears = Invoice::where('user_id', $userId)
            ->selectRaw('YEAR(invoice_date) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->toArray();

        if (empty($availableYears)) {
            $availableYears = [now()->year];
        }

        // ── Stock de bodega ───────────────────────────────────────────────────
        $wineStats = [
            'total' => Wine::where('user_id', $userId)->count(),
            'in_progress' => Wine::where('user_id', $userId)->where('status', 'in_progress')->count(),
            'bottled' => Wine::where('user_id', $userId)->where('status', 'bottled')->count(),
            'sold' => Wine::where('user_id', $userId)->where('status', 'sold')->count(),
        ];

        $containerBase = Container::where('user_id', $userId)->where('archived', false);
        $containerStats = [
            'total' => (clone $containerBase)->count(),
            'total_capacity' => (float) (clone $containerBase)->sum('capacity'),
            'used_capacity' => (float) DB::table('containers')->where('user_id', $userId)->where('archived', false)
                ->selectRaw('SUM(used_capacity + wine_volume_liters)')->value('SUM(used_capacity + wine_volume_liters)'),
            'maintenance_cost' => $maintenanceCost,
        ];
        $containerStats['usage_pct'] = $containerStats['total_capacity'] > 0
            ? ($containerStats['used_capacity'] / $containerStats['total_capacity']) * 100
            : 0;

        return view('livewire.winery.financial.summary', [
            'year' => $this->year,
            'availableYears' => $availableYears,
            'totalRevenue' => $totalRevenue,
            'pendingRevenue' => $pendingRevenue,
            'totalGrapeCost' => $totalGrapeCost,
            'pendingGrapeCost' => $pendingGrapeCost,
            'maintenanceCost' => $maintenanceCost,
            'grossMargin' => $grossMargin,
            'grossMarginPct' => $grossMarginPct,
            'invoiceCount' => $invoiceCount,
            'paidCount' => $paidCount,
            'monthlyData' => $monthlyData,
            'topClients' => $topClients,
            'winesByType' => $winesByType,
            'wineStats' => $wineStats,
            'containerStats' => $containerStats,
        ])->layout('layouts.app');
    }
}
