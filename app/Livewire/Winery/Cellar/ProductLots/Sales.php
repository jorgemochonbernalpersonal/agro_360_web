<?php

namespace App\Livewire\Winery\Cellar\ProductLots;

use App\Models\InvoiceItem;
use App\Models\ProductLot;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Sales extends Component
{
    use WithPagination;

    public ProductLot $lot;

    public string $search = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
    ];

    public function mount(ProductLot $lot): void
    {
        $this->authorize('update', $lot);
        $this->lot = $lot;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->resetPage();
    }

    public function render()
    {
        $query = InvoiceItem::query()
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->leftJoin('clients', 'clients.id', '=', 'invoices.client_id')
            ->where('invoice_items.wine_lot_id', $this->lot->id)
            ->where('invoices.status', '!=', 'cancelled')
            ->when($this->dateFrom, fn ($q) => $q->where('invoices.invoice_date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->where('invoices.invoice_date', '<=', $this->dateTo))
            ->when($this->search, function ($q) {
                $term = '%'.mb_strtolower($this->search).'%';
                $q->where(function ($q2) use ($term) {
                    $q2->whereRaw("LOWER(IFNULL(invoices.invoice_number,'')) LIKE ?", [$term])
                        ->orWhereRaw("LOWER(IFNULL(invoices.delivery_note_code,'')) LIKE ?", [$term])
                        ->orWhereRaw("LOWER(IFNULL(clients.first_name,'')) LIKE ?", [$term])
                        ->orWhereRaw("LOWER(IFNULL(clients.last_name,'')) LIKE ?", [$term])
                        ->orWhereRaw("LOWER(IFNULL(clients.company_name,'')) LIKE ?", [$term]);
                });
            })
            ->selectRaw("
                invoice_items.id,
                invoice_items.quantity,
                invoice_items.unit_price,
                invoice_items.discount_percentage,
                invoice_items.total,
                invoices.id              as invoice_id,
                invoices.invoice_number,
                invoices.delivery_note_code,
                invoices.invoice_date,
                invoices.payment_status,
                invoices.gift,
                COALESCE(
                    NULLIF(clients.company_name, ''),
                    CONCAT(IFNULL(clients.first_name,''), ' ', IFNULL(clients.last_name,''))
                )                        as client_name
            ")
            ->orderByDesc('invoices.invoice_date')
            ->orderByDesc('invoices.id');

        $items = $query->paginate(20);

        // Totales globales del lote (sin filtros)
        $totals = InvoiceItem::query()
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->where('invoice_items.wine_lot_id', $this->lot->id)
            ->where('invoices.status', '!=', 'cancelled')
            ->selectRaw('
                SUM(invoice_items.quantity) as total_qty,
                SUM(invoice_items.total)    as total_amount,
                COUNT(DISTINCT invoices.id) as invoice_count
            ')
            ->first();

        return view('livewire.winery.cellar.product-lots.sales', [
            'items' => $items,
            'totals' => $totals,
        ])->layout('layouts.app');
    }
}
