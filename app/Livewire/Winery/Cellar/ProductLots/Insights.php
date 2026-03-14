<?php

namespace App\Livewire\Winery\Cellar\ProductLots;

use App\Exports\ProductLotInsightsExport;
use App\Models\Client;
use App\Models\InvoiceItem;
use App\Models\ProductLot;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class Insights extends Component
{
    public string $dateFrom      = '';
    public string $dateTo        = '';
    public string $filterLotId   = '';
    public string $filterClientId = '';
    public string $metric        = 'qty'; // 'qty' | 'amount'

    protected $queryString = [
        'dateFrom'       => ['except' => ''],
        'dateTo'         => ['except' => ''],
        'filterLotId'    => ['except' => ''],
        'filterClientId' => ['except' => ''],
        'metric'         => ['except' => 'qty'],
    ];

    public function mount(): void
    {
        if (!$this->dateFrom) {
            $this->dateFrom = now()->startOfYear()->toDateString();
        }
        if (!$this->dateTo) {
            $this->dateTo = now()->toDateString();
        }
    }

    public function clearFilters(): void
    {
        $this->filterLotId    = '';
        $this->filterClientId = '';
        $this->dateFrom       = now()->startOfYear()->toDateString();
        $this->dateTo         = now()->toDateString();
        $this->metric         = 'qty';
    }

    // ── Query ─────────────────────────────────────────────────────────────────

    private function fetchRows(): Collection
    {
        return InvoiceItem::query()
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->join('wine_lots', 'wine_lots.id', '=', 'invoice_items.wine_lot_id')
            ->leftJoin('clients', 'clients.id', '=', 'invoices.client_id')
            ->where('invoices.user_id', Auth::id())
            ->where('invoices.invoice_type', 'wine_sale')
            ->where('invoices.status', '!=', 'cancelled')
            ->whereNotNull('invoice_items.wine_lot_id')
            ->whereNotNull('invoices.invoice_date')
            ->when($this->dateFrom,       fn ($q) => $q->where('invoices.invoice_date', '>=', $this->dateFrom))
            ->when($this->dateTo,         fn ($q) => $q->where('invoices.invoice_date', '<=', $this->dateTo))
            ->when($this->filterLotId,    fn ($q) => $q->where('invoice_items.wine_lot_id', $this->filterLotId))
            ->when($this->filterClientId, fn ($q) => $q->where('invoices.client_id', $this->filterClientId))
            ->selectRaw("
                wine_lots.id           as lot_id,
                wine_lots.name         as lot_name,
                wine_lots.vintage      as lot_vintage,
                DATE_FORMAT(invoices.invoice_date, '%Y-%m') as month,
                SUM(invoice_items.quantity) as total_qty,
                SUM(invoice_items.total)    as total_amount
            ")
            ->groupBy('wine_lots.id', 'wine_lots.name', 'wine_lots.vintage', 'month')
            ->orderBy('month')
            ->orderBy('wine_lots.name')
            ->get();
    }

    private function buildPivot(Collection $rows): array
    {
        $months = $rows->pluck('month')->unique()->sort()->values()->toArray();
        $lots   = [];

        foreach ($rows as $row) {
            $id = $row->lot_id;
            if (!isset($lots[$id])) {
                $label = $row->lot_name . ($row->lot_vintage ? ' (' . $row->lot_vintage . ')' : '');
                $lots[$id] = ['name' => $label, 'months' => [], 'total' => 0.0];
            }

            $val = $this->metric === 'amount'
                ? (float) $row->total_amount
                : (float) $row->total_qty;

            $lots[$id]['months'][$row->month] = $val;
            $lots[$id]['total'] += $val;
        }

        uasort($lots, fn ($a, $b) => strcmp($a['name'], $b['name']));

        $colTotals = [];
        foreach ($months as $month) {
            $colTotals[$month] = array_sum(
                array_map(fn ($l) => $l['months'][$month] ?? 0.0, $lots)
            );
        }

        return [
            'months'     => $months,
            'lots'       => $lots,
            'colTotals'  => $colTotals,
            'grandTotal' => array_sum(array_column($lots, 'total')),
        ];
    }

    // ── Export ────────────────────────────────────────────────────────────────

    public function export()
    {
        $pivot    = $this->buildPivot($this->fetchRows());
        $filename = 'insights_lotes_' . $this->dateFrom . '_' . $this->dateTo . '.xlsx';

        return Excel::download(
            new ProductLotInsightsExport($pivot, $this->metric),
            $filename
        );
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        $userId = Auth::id();
        $rows   = $this->fetchRows();
        $pivot  = $this->buildPivot($rows);

        $lots = ProductLot::where('user_id', $userId)
            ->where('archived', false)
            ->orderByDesc('vintage')
            ->orderBy('name')
            ->get(['id', 'name', 'vintage']);

        $clients = Client::where('user_id', $userId)
            ->where('active', true)
            ->orderByRaw("COALESCE(NULLIF(company_name,''), CONCAT(first_name,' ',last_name))")
            ->get(['id', 'first_name', 'last_name', 'company_name']);

        return view('livewire.winery.cellar.product-lots.insights', [
            'pivot'   => $pivot,
            'lots'    => $lots,
            'clients' => $clients,
        ])->layout('layouts.app');
    }
}
