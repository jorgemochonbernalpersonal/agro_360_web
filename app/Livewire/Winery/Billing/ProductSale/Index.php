<?php

namespace App\Livewire\Winery\Billing\ProductSale;

use App\Livewire\Concerns\WithInvoiceActions;
use App\Livewire\Winery\AbstractIndex;
use App\Livewire\Winery\Billing\ProductSale\Traits\WithCorrectiveModal;
use App\Livewire\Winery\Billing\ProductSale\Traits\WithEmitirModal;
use App\Livewire\Winery\Billing\ProductSale\Traits\WithExportModal;
use App\Livewire\Winery\Billing\ProductSale\Traits\WithProductSaleListActions;
use App\Livewire\Winery\Billing\ProductSale\Traits\WithQuickInvoiceModal;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    use WithCorrectiveModal,
        WithEmitirModal,
        WithExportModal,
        WithInvoiceActions,
        WithProductSaleListActions,
        WithQuickInvoiceModal;

    public string $search = '';

    public string $filterStatus = '';

    public string $filterPaymentStatus = '';

    public string $filterDeliveryStatus = '';

    public bool $filterGift = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'filterPaymentStatus' => ['except' => ''],
        'filterDeliveryStatus' => ['except' => ''],
        'filterGift' => ['except' => false],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterPaymentStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterDeliveryStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterGift(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->filterStatus = '';
        $this->filterPaymentStatus = '';
        $this->filterDeliveryStatus = '';
        $this->filterGift = false;
        $this->resetPage();
    }

    protected function filterDefaults(): array
    {
        return [
            'search' => '',
            'filterStatus' => '',
            'filterPaymentStatus' => '',
            'filterDeliveryStatus' => '',
            'filterGift' => false,
        ];
    }

    protected function markPaidSuccessMessage(): string
    {
        return __('Factura marcada como cobrada.');
    }

    protected function baseQuery(): Builder
    {
        return Invoice::where('user_id', $this->wineryId())
            ->where('invoice_type', 'wine_sale')
            ->with(['client', 'items'])
            ->withCount('correctives');
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $term = '%'.mb_strtolower($this->search).'%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(IFNULL(invoice_number,\'\')) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(IFNULL(delivery_note_code,\'\')) LIKE ?', [$term])
                    ->orWhereHas('client', function ($q2) use ($term) {
                        $q2->whereRaw('LOWER(IFNULL(first_name,\'\')) LIKE ?', [$term])
                            ->orWhereRaw('LOWER(IFNULL(company_name,\'\')) LIKE ?', [$term]);
                    });
            });
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterPaymentStatus) {
            $query->where('payment_status', $this->filterPaymentStatus);
        }

        if ($this->filterDeliveryStatus) {
            $query->where('delivery_status', $this->filterDeliveryStatus);
        }

        if ($this->filterGift) {
            $query->where('gift', true);
        }
    }

    protected function applyOrderBy(Builder $query): void
    {
        $query->orderByDesc('created_at')->orderByDesc('id');
    }

    protected function defaultOrderBy(): array
    {
        return ['created_at', 'desc'];
    }

    protected function perPage(): int
    {
        return 15;
    }

    protected function viewData(mixed $entries): array
    {
        $giftCount = Invoice::where('user_id', $this->wineryId())
            ->where('invoice_type', 'wine_sale')
            ->where('gift', true)
            ->where('status', '!=', 'cancelled')
            ->when($this->search, function ($q) {
                $term = '%'.mb_strtolower($this->search).'%';
                $q->where(function ($q2) use ($term) {
                    $q2->whereRaw('LOWER(IFNULL(invoice_number,\'\')) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(IFNULL(delivery_note_code,\'\')) LIKE ?', [$term]);
                });
            })
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterPaymentStatus, fn ($q) => $q->where('payment_status', $this->filterPaymentStatus))
            ->when($this->filterDeliveryStatus, fn ($q) => $q->where('delivery_status', $this->filterDeliveryStatus))
            ->count();

        return ['invoices' => $entries, 'giftCount' => $giftCount];
    }

    protected function resolveViewName(): string
    {
        return 'livewire.winery.billing.products.index';
    }

    private function findInvoice(int $id, array $with = []): ?Invoice
    {
        return Invoice::where('user_id', $this->wineryId())
            ->where('invoice_type', 'wine_sale')
            ->with($with)
            ->find($id);
    }
}
