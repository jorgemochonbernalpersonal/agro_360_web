<?php

namespace App\Livewire\Viticulturist;

use App\Models\Client;
use App\Models\Harvest;
use App\Models\HarvestStock;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class FinancialStats extends Component
{
    public $period = 'year'; // year, month, quarter

    public $selectedYear;

    public function mount()
    {
        $this->selectedYear = date('Y');
    }

    public function render()
    {
        $user = Auth::user();

        // =======================
        // KPIs FINANCIEROS
        // =======================

        // Total Facturado (año actual)
        $totalInvoiced = Invoice::forUser($user->id)
            ->whereYear('invoice_date', $this->selectedYear)
            ->where('status', '!=', 'cancelled')
            ->sum('total_amount');

        // Pendiente de cobro
        $pendingAmount = Invoice::forUser($user->id)
            ->where('payment_status', 'unpaid')
            ->where('status', '!=', 'cancelled')
            ->sum('total_amount');

        // Facturas vencidas (solo las marcadas como overdue)
        $overdueAmount = Invoice::forUser($user->id)
            ->where('payment_status', 'overdue')
            ->where('status', '!=', 'cancelled')
            ->sum('total_amount');

        $overdueCount = Invoice::forUser($user->id)
            ->where('payment_status', 'overdue')
            ->where('status', '!=', 'cancelled')
            ->count();

        // Tasa de cobro (pagado / total facturado)
        $paidAmount = Invoice::forUser($user->id)
            ->whereYear('invoice_date', $this->selectedYear)
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        $collectionRate = $totalInvoiced > 0 ? ($paidAmount / $totalInvoiced) * 100 : 0;

        // Clientes activos (con facturas este año)
        $activeClients = Client::forUser($user->id)
            ->whereHas('invoices', function ($q) {
                $q->whereYear('invoice_date', $this->selectedYear)
                    ->where('status', '!=', 'cancelled');
            })
            ->count();

        // Factura media
        $invoiceCount = Invoice::forUser($user->id)
            ->whereYear('invoice_date', $this->selectedYear)
            ->where('status', '!=', 'cancelled')
            ->count();

        $averageInvoice = $invoiceCount > 0 ? $totalInvoiced / $invoiceCount : 0;

        // =======================
        // GRÁFICOS
        // =======================

        // Evolución de ingresos (12 meses) — una sola query agrupada por mes
        $startMonth = now()->subMonths(11)->startOfMonth();
        $incomeByMonth = Invoice::forUser($user->id)
            ->where('invoice_date', '>=', $startMonth)
            ->where('status', '!=', 'cancelled')
            ->selectRaw('YEAR(invoice_date) as year, MONTH(invoice_date) as month, SUM(total_amount) as total')
            ->groupBy('year', 'month')
            ->get()
            ->keyBy(fn ($row) => $row->year.'-'.str_pad($row->month, 2, '0', STR_PAD_LEFT));

        $monthlyIncome = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $key = $month->format('Y-m');
            $monthlyIncome[] = [
                'month' => $month->format('M Y'),
                'income' => (float) ($incomeByMonth[$key]->total ?? 0),
            ];
        }

        // Top 10 clientes por facturación
        $topClients = Client::forUser($user->id)
            ->withSum(['invoices as total_invoiced' => function ($q) {
                $q->whereYear('invoice_date', $this->selectedYear)
                    ->where('status', '!=', 'cancelled');
            }], 'total_amount')
            ->get()
            ->sortByDesc(fn ($c) => (float) ($c->total_invoiced ?? 0))
            ->filter(fn ($c) => ($c->total_invoiced ?? 0) > 0)
            ->take(10)
            ->values();

        // Distribución de ventas por variedad
        $salesByVariety = InvoiceItem::whereHas('invoice', function ($q) use ($user) {
            $q->where('user_id', $user->id)
                ->whereYear('invoice_date', $this->selectedYear)
                ->where('status', '!=', 'cancelled');
        })
            ->whereHas('harvest.plotPlanting.grapeVariety')
            ->with('harvest.plotPlanting.grapeVariety')
            ->get()
            ->groupBy(function ($item) {
                return $item->harvest->plotPlanting->grapeVariety->name ?? 'Sin variedad';
            })
            ->map(function ($items) {
                return [
                    'total' => $items->sum('total'),
                    'weight' => $items->sum('quantity'),
                ];
            })
            ->sortByDesc('total');

        // Facturas pendientes de pago (sin fecha de vencimiento, se muestran las más antiguas)
        $upcomingInvoices = Invoice::forUser($user->id)
            ->where('payment_status', 'unpaid')
            ->where('status', '!=', 'cancelled')
            ->with('client')
            ->orderBy('invoice_date', 'asc')
            ->take(10)
            ->get();

        // Stock por variedad — última fila de stock por cosecha en una sola subquery
        $harvests = Harvest::whereHas('activity', function ($q) use ($user) {
            $q->where('viticulturist_id', $user->id);
        })
            ->whereHas('plotPlanting.grapeVariety')
            ->with(['plotPlanting.grapeVariety'])
            ->get();

        $latestStocks = HarvestStock::whereIn('harvest_id', $harvests->pluck('id'))
            ->whereIn('id', function ($sub) {
                $sub->selectRaw('MAX(id)')
                    ->from('harvest_stocks')
                    ->groupBy('harvest_id');
            })
            ->get()
            ->keyBy('harvest_id');

        $stockByVariety = $harvests
            ->groupBy(fn ($h) => $h->plotPlanting->grapeVariety->name ?? 'Sin variedad')
            ->map(function ($varietyHarvests) use ($latestStocks) {
                $available = 0;
                $reserved = 0;
                $sold = 0;

                foreach ($varietyHarvests as $harvest) {
                    $stock = $latestStocks->get($harvest->id);
                    $available += $stock?->available_qty ?? 0;
                    $reserved += $stock?->reserved_qty ?? 0;
                    $sold += $stock?->sold_qty ?? 0;
                }

                return [
                    'available' => $available,
                    'reserved' => $reserved,
                    'sold' => $sold,
                    'total' => $available + $reserved + $sold,
                ];
            });

        // Comparativa año actual vs anterior
        $previousYearIncome = Invoice::forUser($user->id)
            ->whereYear('invoice_date', $this->selectedYear - 1)
            ->where('status', '!=', 'cancelled')
            ->sum('total_amount');

        $growthPercentage = $previousYearIncome > 0
            ? (($totalInvoiced - $previousYearIncome) / $previousYearIncome) * 100
            : 0;

        return view('livewire.viticulturist.financial-stats', [
            // KPIs
            'totalInvoiced' => $totalInvoiced,
            'pendingAmount' => $pendingAmount,
            'overdueAmount' => $overdueAmount,
            'overdueCount' => $overdueCount,
            'collectionRate' => $collectionRate,
            'activeClients' => $activeClients,
            'averageInvoice' => $averageInvoice,
            'invoiceCount' => $invoiceCount,

            // Gráficos
            'monthlyIncome' => $monthlyIncome,
            'topClients' => $topClients,
            'salesByVariety' => $salesByVariety,
            'upcomingInvoices' => $upcomingInvoices,
            'stockByVariety' => $stockByVariety,

            // Comparativa
            'previousYearIncome' => $previousYearIncome,
            'growthPercentage' => $growthPercentage,
        ])->layout('layouts.app', [
            'title' => __('Estadísticas Financieras - Agro365'),
            'description' => __('Análisis completo de tu negocio vitivinícola. Ingresos, cobros pendientes, evolución mensual y análisis de rentabilidad por variedad.'),
        ]);
    }
}
