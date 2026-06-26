<?php

namespace App\Livewire\Winery\Billing\ProductSale;

use App\Livewire\Concerns\WithProductSaleFormRules;
use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Livewire\Winery\Billing\ProductSale\Traits\WithProductSaleStatusModals;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\ProductLot;
use App\Models\Tax;
use App\Services\InvoiceService;
use App\Services\ProductSaleService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

/**
 * @property-read bool $isLocked
 * @property-read bool $isInvoiced
 */
class Edit extends Component
{
    use WithProductSaleFormRules,
        WithProductSaleStatusModals,
        WithRoleAwareRedirect,
        WithToastNotifications;

    public Invoice $invoice;

    public string $client_id = '';

    public string $observations = '';

    public string $observations_invoice = '';

    public string $payment_type = '';

    public string $payment_status = '';

    public string $payment_date = '';

    public string $delivery_status = '';

    public bool $is_gift = false;

    public array $items = [];

    public string $selectedLotId = '';

    public $availableTaxes = [];

    protected InvoiceService $invoiceService;

    protected string $defaultTaxId = '';

    public function boot(InvoiceService $invoiceService): void
    {
        $this->invoiceService = $invoiceService;
    }

    public function mount(int $id): void
    {
        $this->invoice = Invoice::where('user_id', Auth::id())
            ->where('invoice_type', 'wine_sale')
            ->with('items.wineLot')
            ->findOrFail($id);

        $this->client_id = (string) $this->invoice->client_id;
        $this->observations = $this->invoice->observations ?? '';
        $this->observations_invoice = $this->invoice->observations_invoice ?? '';
        $this->payment_type = $this->invoice->payment_type ?? '';
        $this->payment_status = $this->invoice->payment_status ?? 'unpaid';
        $this->payment_date = $this->invoice->payment_date
            ? $this->invoice->payment_date->format('Y-m-d') : '';
        $this->delivery_status = $this->invoice->delivery_status ?? 'pending';
        $this->is_gift = (bool) $this->invoice->gift;

        $user = Auth::user();
        $this->availableTaxes = $user->taxes()->orderByPivot('order')->get();
        if ($this->availableTaxes->isEmpty()) {
            $this->availableTaxes = Tax::active()->orderBy('rate')->get();
        }

        $defaultTax = $user->defaultTax()->first() ?? $this->availableTaxes->first();
        $this->defaultTaxId = (string) ($defaultTax->id ?? '');

        $this->items = $this->invoice->items->map(fn ($item) => [
            'wine_lot_id' => $item->wine_lot_id ? (int) $item->wine_lot_id : null,
            'name' => $item->name,
            'description' => $item->description ?? '',
            'sku' => $item->sku ?? '',
            'quantity' => (string) $item->quantity,
            'available_qty' => $item->wineLot ? (float) $item->wineLot->available_quantity + (float) $item->quantity : null,
            'unit_price' => (string) $item->unit_price,
            'tax_id' => (string) ($item->tax_id ?? $this->defaultTaxId),
            'discount_percentage' => (string) ($item->discount_percentage ?? 0),
            'concept_type' => $item->concept_type ?? 'wine',
        ])->toArray();
    }

    public function getIsLockedProperty(): bool
    {
        return $this->invoice->delivery_status === 'delivered'
            || $this->invoice->delivery_status === 'cancelled'
            || $this->invoice->status === 'cancelled';
    }

    public function getIsInvoicedProperty(): bool
    {
        return $this->invoice->status === 'sent';
    }

    public function getSubtotalProperty(): float
    {
        return $this->vatTotals()['gross_subtotal'];
    }

    public function getDiscountAmountProperty(): float
    {
        return $this->vatTotals()['discount_amount'];
    }

    public function getTaxAmountProperty(): float
    {
        return $this->vatTotals()['tax_amount'];
    }

    public function getTotalAmountProperty(): float
    {
        return $this->vatTotals()['total'];
    }

    public function addProductToInvoice(): void
    {
        if (! $this->selectedLotId) {
            return;
        }

        $lot = ProductLot::where('user_id', Auth::id())->find($this->selectedLotId);

        if (! $lot) {
            $this->toastError(__('Lote no encontrado.'));

            return;
        }

        foreach ($this->items as $item) {
            if (isset($item['wine_lot_id']) && (int) $item['wine_lot_id'] === $lot->id) {
                $this->toastError(__('Este lote ya está en la factura.'));

                return;
            }
        }

        $this->items[] = [
            'wine_lot_id' => $lot->id,
            'name' => $lot->name.($lot->vintage ? " ({$lot->vintage})" : ''),
            'description' => '',
            'sku' => $lot->sku ?? '',
            'quantity' => 1,
            'available_qty' => (float) $lot->available_quantity,
            'unit_price' => $lot->price_per_unit ? (float) $lot->price_per_unit : 0,
            'discount_percentage' => 0,
            'tax_id' => $this->defaultTaxId ?: null,
            'concept_type' => 'wine',
        ];

        $this->selectedLotId = '';
        $this->toastSuccess(__('Producto añadido.'));
    }

    public function addItem(): void
    {
        $this->items[] = [
            'wine_lot_id' => null,
            'name' => '',
            'description' => '',
            'sku' => '',
            'quantity' => 1,
            'available_qty' => null,
            'unit_price' => 0,
            'discount_percentage' => 0,
            'tax_id' => $this->defaultTaxId ?: null,
            'concept_type' => 'other',
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function save()
    {
        $this->validate();

        if ($this->isLocked) {
            $this->toastError(__('Esta factura no se puede modificar.'));

            return;
        }

        $client = Client::where('user_id', Auth::id())->findOrFail($this->client_id);
        $taxRates = $this->availableTaxes->keyBy('id');

        try {
            app(ProductSaleService::class)->updateSaleInvoice(
                $this->invoice,
                $client,
                [
                    'payment_status' => $this->payment_status,
                    'payment_type' => $this->payment_type ?: null,
                    'observations' => $this->observations ?: null,
                    'observations_invoice' => $this->observations_invoice ?: null,
                ],
                $this->items,
                $taxRates,
                $this->is_gift,
            );

            $this->toastSuccess(__('Factura actualizada correctamente.'));

            return $this->roleRedirect('invoices.products.index');

        } catch (\Exception $e) {
            Log::error('Error al editar factura de productos: '.$e->getMessage(), [
                'invoice_id' => $this->invoice->id,
                'user_id' => Auth::id(),
                'exception' => $e,
            ]);
            $this->toastError($e instanceof \RuntimeException ? $e->getMessage() : __('Error al guardar los cambios.'));
        }
    }

    public function render()
    {
        $clients = Client::where('user_id', Auth::id())->where('active', true)->orderBy('first_name')->orderBy('company_name')->get();
        $existingLotIds = collect($this->items)->pluck('wine_lot_id')->filter()->values()->all();
        $productLots = ProductLot::where('user_id', Auth::id())->where('archived', false)
            ->where(function ($q) use ($existingLotIds) {
                $q->where('available_quantity', '>', 0)
                    ->orWhereIn('id', $existingLotIds);
            })
            ->orderByDesc('vintage')->orderBy('name')->get();

        return view('livewire.winery.billing.products.edit', [
            'clients' => $clients,
            'wineLots' => $productLots,
            'availableTaxes' => $this->availableTaxes,
            'isLocked' => $this->isLocked,
            'isInvoiced' => $this->isInvoiced,
        ])->layout('layouts.app');
    }

    protected function rules(): array
    {
        return array_merge($this->productSaleBaseRules(allowArchivedLots: true), [
            'payment_status' => 'required|in:unpaid,partial,paid',
        ]);
    }

    protected function validationAttributes(): array
    {
        $attrs = ['client_id' => 'cliente'];
        foreach ($this->items as $i => $_) {
            $attrs["items.{$i}.name"] = 'concepto';
            $attrs["items.{$i}.quantity"] = 'cantidad';
            $attrs["items.{$i}.unit_price"] = 'precio unitario';
        }

        return $attrs;
    }

    private function vatTotals(): array
    {
        return $this->invoiceService->calculateVatTotals(
            $this->items,
            $this->availableTaxes->keyBy('id'),
        );
    }
}
