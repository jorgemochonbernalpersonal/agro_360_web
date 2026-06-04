<?php

namespace App\Livewire\Viticulturist\Billing\HarvestSale;

use App\Livewire\Concerns\WithHarvestSaleStock;
use App\Livewire\Concerns\WithInvoiceActions;
use App\Livewire\Viticulturist\AbstractIndex;
use App\Models\Invoice;
use App\Models\MarketedHarvest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Index extends AbstractIndex
{
    use WithHarvestSaleStock, WithInvoiceActions;

    public string $search = '';

    public string $buyerFilter = '';

    public string $paymentFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'buyerFilter' => ['except' => ''],
        'paymentFilter' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingBuyerFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPaymentFilter(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = $this->buyerFilter = $this->paymentFilter = '';
        $this->resetPage();
    }

    // ── Actions ───────────────────────────────────────────────────────────────

    public function markDelivered(int $invoiceId): void
    {
        $invoice = $this->findInvoice($invoiceId, ['items']);
        if (! $invoice) {
            return;
        }

        if ($invoice->status === 'cancelled') {
            $this->toastError(__('No se puede marcar como entregada una factura cancelada.'));

            return;
        }
        if ($invoice->delivery_status === 'delivered') {
            $this->toastError(__('Esta factura ya está marcada como entregada.'));

            return;
        }

        try {
            DB::transaction(function () use ($invoice) {
                foreach ($invoice->items as $item) {
                    if ($item->harvest_id && $item->quantity > 0) {
                        $this->sellHarvestStock(
                            $item->harvest_id,
                            (float) $item->quantity,
                            $invoice->id
                        );
                        $item->update(['delivery_status' => 'delivered']);
                    }
                }
                $invoice->update(['delivery_status' => 'delivered']);
            });

            $this->toastSuccess(__('Factura marcada como entregada.'));
        } catch (\Exception $e) {
            Log::error('Error al marcar entrega de factura vendimia: '.$e->getMessage());
            $this->toastError($e instanceof \RuntimeException ? $e->getMessage() : __('Error al marcar como entregada.'));
        }
    }

    public function cancel(int $invoiceId): void
    {
        $invoice = $this->findInvoice($invoiceId, ['items']);
        if (! $invoice) {
            return;
        }

        if ($invoice->status === 'cancelled') {
            $this->toastError(__('Esta factura ya está cancelada.'));

            return;
        }
        if ($invoice->payment_status === 'paid') {
            $this->toastError(__('No se puede cancelar una factura ya pagada.'));

            return;
        }

        try {
            DB::transaction(function () use ($invoice) {
                // Release stock for each item
                foreach ($invoice->items as $item) {
                    if ($item->harvest_id && $item->quantity > 0) {
                        $this->releaseHarvestStock(
                            $item->harvest_id,
                            (float) $item->quantity,
                            $invoice->id
                        );
                        $item->update(['delivery_status' => 'cancelled']);
                    }
                }

                // Release marketed_harvests
                MarketedHarvest::where('invoice_id', $invoice->id)
                    ->update(['invoice_id' => null]);

                $invoice->update(['status' => 'cancelled', 'delivery_status' => 'cancelled']);
            });

            $this->toastSuccess(__('Factura cancelada. Los kg quedan disponibles de nuevo.'));
        } catch (\Exception $e) {
            Log::error('Error al cancelar factura vendimia: '.$e->getMessage());
            $this->toastError(__('Error al cancelar la factura.'));
        }
    }

    protected function filterDefaults(): array
    {
        return ['search' => '', 'buyerFilter' => '', 'paymentFilter' => ''];
    }

    protected function getEmailRecipient(Invoice $invoice): ?string
    {
        return $invoice->billing_email;
    }

    protected function markPaidSuccessMessage(): string
    {
        return __('Factura marcada como pagada.');
    }

    protected function sendEmailSuccessMessage(string $email): string
    {
        return "Factura enviada a {$email}.";
    }

    // ── Query ─────────────────────────────────────────────────────────────────

    protected function baseQuery(): Builder
    {
        return Invoice::where('user_id', Auth::id())
            ->where('invoice_type', 'harvest_sale');
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $term = '%'.mb_strtolower($this->search).'%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(IFNULL(invoice_number,\'\')) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(IFNULL(delivery_note_code,\'\')) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(IFNULL(billing_company_name,\'\')) LIKE ?', [$term]);
            });
        }

        if ($this->buyerFilter) {
            $query->where('billing_company_name', $this->buyerFilter);
        }

        if ($this->paymentFilter) {
            match ($this->paymentFilter) {
                'unpaid' => $query->where('payment_status', 'unpaid'),
                'paid' => $query->where('payment_status', 'paid'),
                default => null,
            };
        }
    }

    protected function applyOrderBy(Builder $query): void
    {
        $query->orderByDesc('invoice_date')->orderByDesc('id');
    }

    protected function defaultOrderBy(): array
    {
        return ['invoice_date', 'desc'];
    }

    protected function perPage(): int
    {
        return 15;
    }

    protected function viewData(mixed $entries): array
    {
        $buyers = Invoice::where('user_id', Auth::id())
            ->where('invoice_type', 'harvest_sale')
            ->whereNotNull('billing_company_name')
            ->distinct()
            ->orderBy('billing_company_name')
            ->pluck('billing_company_name');

        return [
            'invoices' => $entries,
            'buyers' => $buyers,
        ];
    }

    // ── Helper ────────────────────────────────────────────────────────────────

    private function findInvoice(int $id, array $with = []): ?Invoice
    {
        return Invoice::where('user_id', Auth::id())
            ->where('invoice_type', 'harvest_sale')
            ->with($with)
            ->find($id);
    }
}
