<?php

namespace App\Livewire\Winery\Clients;

use App\Exports\ClientInsightsExport;
use App\Models\Client;
use App\Models\InvoiceItem;
use App\Models\ProductLot;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class Insights extends Component
{
    public string $dateFrom = '';

    public string $dateTo = '';

    public string $filterClientId = '';

    public string $filterLotId = '';

    public string $metric = 'qty'; // 'qty' | 'amount'

    protected $queryString = [
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'filterClientId' => ['except' => ''],
        'filterLotId' => ['except' => ''],
        'metric' => ['except' => 'qty'],
    ];

    public function mount(): void
    {
        if (! $this->dateFrom) {
            $this->dateFrom = now()->startOfYear()->toDateString();
        }
        if (! $this->dateTo) {
            $this->dateTo = now()->toDateString();
        }
    }

    public function clearFilters(): void
    {
        $this->filterClientId = '';
        $this->filterLotId = '';
        $this->dateFrom = now()->startOfYear()->toDateString();
        $this->dateTo = now()->toDateString();
        $this->metric = 'qty';
    }

    // ── Export ────────────────────────────────────────────────────────────────

    public function export()
    {
        $pivot = $this->buildPivot($this->fetchRows());
        $filename = 'insights_clientes_'.$this->dateFrom.'_'.$this->dateTo.'.xlsx';

        return Excel::download(
            new ClientInsightsExport($pivot, $this->metric),
            $filename
        );
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        $userId = Auth::id();
        $rows = $this->fetchRows();
        $pivot = $this->buildPivot($rows);

        $clients = Client::where('user_id', $userId)
            ->where('active', true)
            ->orderByRaw("COALESCE(NULLIF(company_name,''), CONCAT(first_name,' ',last_name))")
            ->get(['id', 'first_name', 'last_name', 'company_name']);

        $lots = ProductLot::where('user_id', $userId)
            ->orderBy('name')
            ->get(['id', 'name', 'vintage']);

        return view('livewire.winery.clients.insights', [
            'pivot' => $pivot,
            'clients' => $clients,
            'lots' => $lots,
        ])->layout('layouts.app');
    }

    // ── Datos de la tabla pivot ───────────────────────────────────────────────

    private function fetchRows(): Collection
    {
        return InvoiceItem::query()
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->join('clients', 'clients.id', '=', 'invoices.client_id')
            ->where('invoices.user_id', Auth::id())
            ->where('invoices.invoice_type', 'wine_sale')
            ->where('invoices.status', '!=', 'cancelled')
            ->whereNotNull('invoices.invoice_date')
            ->when($this->dateFrom, fn ($q) => $q->where('invoices.invoice_date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->where('invoices.invoice_date', '<=', $this->dateTo))
            ->when($this->filterClientId, fn ($q) => $q->where('invoices.client_id', $this->filterClientId))
            ->when($this->filterLotId, fn ($q) => $q->where('invoice_items.wine_lot_id', $this->filterLotId))
            ->selectRaw("
                clients.id            as client_id,
                clients.first_name    as first_name,
                clients.last_name     as last_name,
                clients.company_name  as company_name,
                DATE_FORMAT(invoices.invoice_date, '%Y-%m') as month,
                SUM(invoice_items.quantity) as total_qty,
                SUM(invoice_items.total)    as total_amount
            ")
            ->groupBy('clients.id', 'clients.first_name', 'clients.last_name', 'clients.company_name', 'month')
            ->orderBy('month')
            ->orderBy('client_id')
            ->get();
    }

    private function buildPivot(Collection $rows): array
    {
        $months = $rows->pluck('month')->unique()->sort()->values()->toArray();
        $clients = [];

        foreach ($rows as $row) {
            $id = $row->client_id;
            if (! isset($clients[$id])) {
                $clients[$id] = [
                    'name' => $row->company_name ?: trim($row->first_name.' '.$row->last_name),
                    'months' => [],
                    'total' => 0.0,
                ];
            }

            $val = $this->metric === 'amount'
                ? (float) $row->total_amount
                : (float) $row->total_qty;

            $clients[$id]['months'][$row->month] = $val;
            $clients[$id]['total'] += $val;
        }

        // Ordenar clientes por nombre
        uasort($clients, fn ($a, $b) => strcmp($a['name'], $b['name']));

        // Totales por columna (mes)
        $colTotals = [];
        foreach ($months as $month) {
            $colTotals[$month] = array_sum(
                array_map(fn ($c) => $c['months'][$month] ?? 0.0, $clients)
            );
        }

        return [
            'months' => $months,
            'clients' => $clients,
            'colTotals' => $colTotals,
            'grandTotal' => array_sum(array_column($clients, 'total')),
        ];
    }
}
