<?php

namespace App\Livewire\Viticulturist;

use App\Models\Invoice;
use App\Models\Client;
use App\Models\Harvest;
use App\Models\InvoiceItem;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            ->sum('total_amount') ?? 0;

        // Pendiente de cobro
        $pendingAmount = Invoice::forUser($user->id)
            ->where('payment_status', 'unpaid')
            ->where('status', '!=', 'cancelled')
            ->sum('total_amount') ?? 0;

        // Facturas vencidas (solo las marcadas como overdue)
        $overdueAmount = Invoice::forUser($user->id)
            ->where('payment_status', 'overdue')
            ->where('status', '!=', 'cancelled')
            ->sum('total_amount') ?? 0;

        $overdueCount = Invoice::forUser($user->id)
            ->where('payment_status', 'overdue')
            ->where('status', '!=', 'cancelled')
            ->count();

        // Tasa de cobro (pagado / total facturado)
        $paidAmount = Invoice::forUser($user->id)
            ->whereYear('invoice_date', $this->selectedYear)
            ->where('payment_status', 'paid')
            ->sum('total_amount') ?? 0;
        
        $collectionRate = $totalInvoiced > 0 ? ($paidAmount / $totalInvoiced) * 100 : 0;

        // Clientes activos (con facturas este año)
        $activeClients = Client::forUser($user->id)
            ->whereHas('invoices', function($q) {
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

        // Evolución de ingresos (12 meses)
        $monthlyIncome = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $income = Invoice::forUser($user->id)
                ->whereYear('invoice_date', $month->year)
                ->whereMonth('invoice_date', $month->month)
                ->where('status', '!=', 'cancelled')
                ->sum('total_amount') ?? 0;
            
            $monthlyIncome[] = [
                'month' => $month->format('M Y'),
                'income' => $income
            ];
        }

        // Top 10 clientes por facturación
        $topClients = Client::forUser($user->id)
            ->with('invoices')
            ->get()
            ->map(function($client) {
                $client->total_invoiced = $client->invoices()
                    ->whereYear('invoice_date', $this->selectedYear)
                    ->where('status', '!=', 'cancelled')
                    ->sum('total_amount') ?? 0;
                return $client;
            })
            ->filter(function($client) {
                return $client->total_invoiced > 0;
            })
            ->sortByDesc('total_invoiced')
            ->take(10)
            ->values();

        // Distribución de ventas por variedad
        $salesByVariety = InvoiceItem::whereHas('invoice', function($q) use ($user) {
                $q->forUser($user->id)
                  ->whereYear('invoice_date', $this->selectedYear)
                  ->where('status', '!=', 'cancelled');
            })
            ->whereHas('harvest.plotPlanting.grapeVariety')
            ->with('harvest.plotPlanting.grapeVariety')
            ->get()
            ->groupBy(function($item) {
                return $item->harvest->plotPlanting->grapeVariety->name ?? 'Sin variedad';
            })
            ->map(function($items) {
                return [
                    'total' => $items->sum('total'),
                    'weight' => $items->sum('quantity')
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

        // Stock por variedad (usando el nuevo sistema de stock)
        $stockByVariety = Harvest::whereHas('activity', function($q) use ($user) {
                $q->where('viticulturist_id', $user->id);
            })
            ->whereHas('plotPlanting.grapeVariety')
            ->with(['plotPlanting.grapeVariety', 'stockMovements'])
            ->get()
            ->groupBy(function($harvest) {
                return $harvest->plotPlanting->grapeVariety->name ?? 'Sin variedad';
            })
            ->map(function($harvests) {
                $available = 0;
                $reserved = 0;
                $sold = 0;
                
                foreach ($harvests as $harvest) {
                    $stock = $harvest->getCurrentStock();
                    $available += $stock['available'];
                    $reserved += $stock['reserved'];
                    $sold += $stock['sold'];
                }
                
                return [
                    'available' => $available,
                    'reserved' => $reserved,
                    'sold' => $sold,
                    'total' => $available + $reserved + $sold
                ];
            });

        // Comparativa año actual vs anterior
        $previousYearIncome = Invoice::forUser($user->id)
            ->whereYear('invoice_date', $this->selectedYear - 1)
            ->where('status', '!=', 'cancelled')
            ->sum('total_amount') ?? 0;

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
            'title' => 'Estadísticas Financieras - Agro365',
            'description' => 'Análisis completo de tu negocio vitivinícola. Ingresos, cobros pendientes, evolución mensual y análisis de rentabilidad por variedad.',
        ]);
    }
}
