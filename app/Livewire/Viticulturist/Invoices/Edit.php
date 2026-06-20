<?php

namespace App\Livewire\Viticulturist\Invoices;

use App\Livewire\Concerns\WithInvoiceClientAddress;
use App\Livewire\Concerns\WithInvoiceEditRules;
use App\Livewire\Concerns\WithInvoiceFormRules;
use App\Livewire\Concerns\WithInvoiceHarvestItems;
use App\Livewire\Concerns\WithInvoiceStatusManagement;
use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Tax;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use RuntimeException;

/**
 * @property-read bool $isLocked
 */
class Edit extends Component
{
    use WithInvoiceClientAddress, WithInvoiceEditRules, WithInvoiceFormRules,
        WithInvoiceHarvestItems, WithInvoiceStatusManagement,
        WithRoleAwareRedirect, WithToastNotifications;

    public Invoice $invoice;

    public $client_id = '';

    public $client_address_id = '';

    public $invoice_date = '';

    public $delivery_note_date = '';

    public $delivery_status = '';

    public $payment_status = '';

    public $payment_type = '';

    public $payment_date = '';

    public $items = [];

    public $observations = '';

    public $observations_invoice = '';

    public $delivery_note_code = '';

    public $invoice_number = '';

    public bool $showInvoiceModal = false;

    public $invoice_date_modal = '';

    public $availableClients = [];

    public $availableTaxes = [];

    protected InvoiceService $invoiceService;

    public function boot(InvoiceService $invoiceService): void
    {
        $this->invoiceService = $invoiceService;
    }

    public function getIsInvoicedProperty(): bool
    {
        return $this->invoice->status === 'sent' || $this->invoice->status === 'paid';
    }

    public function getIsLockedProperty(): bool
    {
        return $this->invoice->delivery_status === 'delivered'
            || $this->invoice->delivery_status === 'cancelled'
            || $this->invoice->status === 'cancelled';
    }

    public function mount($invoice): void
    {
        if ($invoice instanceof Invoice) {
            // 404 (no 403) para no revelar la existencia de facturas ajenas — fijado por test
            abort_unless(Auth::user()->can('update', $invoice), 404);
            $this->invoice = $invoice;
        } else {
            $user = Auth::user();
            $this->invoice = Invoice::forUser($user->id)
                ->with(['items', 'client.addresses.municipality', 'client.addresses.province', 'client.addresses.autonomousCommunity'])
                ->findOrFail($invoice);
        }

        $this->loadInvoiceData();
    }

    public function loadInvoiceData(): void
    {
        $this->client_id = $this->invoice->client_id;
        $this->client_address_id = $this->invoice->client_address_id ?? '';
        $this->invoice_date = $this->invoice->invoice_date?->format('Y-m-d') ?? '';
        $this->delivery_note_date = $this->invoice->delivery_note_date?->format('Y-m-d') ?? '';
        $this->delivery_status = $this->invoice->delivery_status;
        $this->payment_status = $this->invoice->payment_status;
        $this->payment_type = $this->invoice->payment_type ?? '';
        $this->payment_date = $this->invoice->payment_date?->format('Y-m-d') ?? '';
        $this->observations = $this->invoice->observations ?? '';
        $this->observations_invoice = $this->invoice->observations_invoice ?? '';
        $this->delivery_note_code = $this->invoice->delivery_note_code ?? '';
        $this->invoice_number = $this->invoice->invoice_number ?? '';

        $itemHarvestIds = $this->invoice->items->pluck('harvest_id')->filter();
        $itemLatestStocks = \App\Models\HarvestStock::whereIn('harvest_id', $itemHarvestIds)
            ->whereRaw('id = (SELECT MAX(hs2.id) FROM harvest_stocks hs2 WHERE hs2.harvest_id = harvest_stocks.harvest_id)')
            ->get()
            ->keyBy('harvest_id');

        $this->items = $this->invoice->items->map(function ($item) use ($itemLatestStocks) {
            $availableQty = null;
            $totalWeight = null;
            if ($item->harvest_id) {
                $latestStock = $itemLatestStocks->get($item->harvest_id);
                $currentAvail = $latestStock ? (float) $latestStock->available_qty : 0;
                $availableQty = $currentAvail + (float) $item->quantity;
                $totalWeight = $item->harvest ? (float) $item->harvest->total_weight : null;
            }

            return [
                'id' => $item->id,
                'harvest_id' => $item->harvest_id,
                'name' => $item->name,
                'description' => $item->description ?? '',
                'sku' => $item->sku ?? '',
                'quantity' => $item->quantity,
                'unit' => $item->unit ?? 'kg',
                'available_qty' => $availableQty,
                'total_weight' => $totalWeight,
                'unit_price' => $item->unit_price,
                'discount_percentage' => $item->discount_percentage,
                'tax_id' => $item->tax_id,
                'concept_type' => $item->concept_type,
            ];
        })->toArray();

        $this->loadData();
        $this->updatedClientId($this->client_id);
    }

    public function cancel(): void
    {
        $this->invoice->refresh();
        $this->loadInvoiceData();
        $this->toastSuccess(__('Cambios cancelados. Se restauraron los valores originales.'));
    }

    public function loadData(): void
    {
        $user = Auth::user();
        $this->availableClients = Client::forUser($user->id)->active()->get();
        $this->availableTaxes = $user->taxes()->orderByPivot('order')->get();

        if ($this->availableTaxes->isEmpty()) {
            $this->availableTaxes = Tax::active()->orderBy('rate')->get();
        }

        $this->loadHarvests();
    }

    public function openInvoiceModal(): void
    {
        if ($this->invoice->status !== 'draft') {
            $this->toastError(__('Solo se puede facturar un albarán en estado borrador.'));

            return;
        }

        $this->invoice_date_modal = $this->invoice_date ?: now()->format('Y-m-d');
        $this->showInvoiceModal = true;
    }

    public function closeInvoiceModal(): void
    {
        $this->showInvoiceModal = false;
        $this->invoice_date_modal = '';
    }

    public function markAsSent()
    {
        $this->validate(
            ['invoice_date_modal' => 'required|date'],
            ['invoice_date_modal.required' => __('Debes indicar la fecha de la factura.')]
        );

        try {
            DB::transaction(function () {
                $settings = \App\Models\InvoicingSetting::getOrCreateForUser(Auth::user()->id);

                $this->invoice->update([
                    'status' => 'sent',
                    'invoice_date' => $this->invoice_date_modal,
                    'invoice_number' => $settings->generateAndIncrementInvoiceCode(),
                ]);
            });

            $this->toastSuccess(__('Factura emitida correctamente.'));
            $this->closeInvoiceModal();

            return $this->viticulturistRoleRedirect('invoices.index');
        } catch (\Exception $e) {
            $this->toastError($e instanceof RuntimeException ? $e->getMessage() : __('Error al facturar. Inténtalo de nuevo.'));
        }
    }

    public function addItem(): void
    {
        $this->items[] = [
            'id' => null,
            'harvest_id' => null,
            'name' => '',
            'description' => '',
            'sku' => '',
            'quantity' => 1,
            'unit' => 'unidades',
            'unit_price' => 0,
            'discount_percentage' => 0,
            'tax_id' => null,
            'concept_type' => 'other',
        ];
    }

    public function removeItem($index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function getSubtotalProperty(): float
    {
        return $this->vatTotals()['tax_base'];
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

    public function update()
    {
        if ($this->isLocked) {
            $this->toastError(__('Esta factura no se puede modificar. Usa "Guardar estados" para cambiar el estado de pago.'));

            return;
        }

        $this->validate();

        try {
            DB::transaction(function () {
                // Delete old items individually so InvoiceItemObserver::deleting() fires per item.
                // Bulk delete ($query->delete()) does NOT trigger model events.
                $this->invoice->load('items.harvest');
                $this->invoice->items->each(fn ($item) => $item->delete());

                $taxIds = collect($this->items)->pluck('tax_id')->filter()->unique()->values()->all();
                $taxRates = empty($taxIds) ? collect() : Tax::whereIn('id', $taxIds)->get()->keyBy('id');
                $totals = $this->invoiceService->calculateVatTotals($this->items, $taxRates);

                foreach ($this->items as $itemData) {
                    if (! empty($itemData['harvest_id'])) {
                        $this->invoiceService->validateViticulturistHarvestOwnership(
                            (int) $itemData['harvest_id'],
                            Auth::id(),
                        );
                    }

                    $tax = $taxRates->get($itemData['tax_id'] ?? null);
                    $line = $this->invoiceService->calculateVatLine($itemData, $tax);

                    $this->invoice->items()->create([
                        'harvest_id' => $itemData['harvest_id'] ?? null,
                        'name' => $itemData['name'],
                        'description' => $itemData['description'] ?? null,
                        'sku' => $itemData['sku'] ?? null,
                        'quantity' => $line['quantity'],
                        'unit' => $itemData['unit'] ?? 'unidades',
                        'unit_price' => $line['unit_price'],
                        'discount_percentage' => $line['discount_percentage'],
                        'discount_amount' => $line['discount_amount'],
                        'tax_id' => $itemData['tax_id'] ?: null,
                        'tax_name' => $tax?->name,
                        'tax_rate' => $line['tax_rate'],
                        'tax_base' => $line['tax_base'],
                        'tax_amount' => $line['tax_amount'],
                        // El subtotal de línea del viticultor es la base NETA, por convención.
                        'subtotal' => $line['tax_base'],
                        'total' => $line['total'],
                        'concept_type' => $itemData['concept_type'] ?? 'other',
                    ]);
                }

                $this->invoice->update([
                    'client_id' => $this->client_id,
                    'client_address_id' => $this->client_address_id ?: null,
                    'invoice_date' => $this->invoice_date ?: null,
                    'delivery_note_date' => $this->delivery_note_date ?: null,
                    'subtotal' => $totals['tax_base'],
                    'discount_amount' => $totals['discount_amount'],
                    'tax_base' => $totals['tax_base'],
                    'tax_rate' => $totals['effective_tax_rate'],
                    'tax_amount' => $totals['tax_amount'],
                    'total_amount' => $totals['total'],
                    'observations' => $this->observations ?: null,
                    'observations_invoice' => $this->observations_invoice ?: null,
                ]);

                $this->invoice->logAction('updated', 'Factura actualizada', [
                    'client_id' => ['old' => $this->invoice->getOriginal('client_id'), 'new' => $this->client_id],
                    'total_amount' => ['old' => $this->invoice->getOriginal('total_amount'), 'new' => $totals['total']],
                    'items_count' => count($this->items),
                ]);
            });

            $this->toastSuccess(__('Factura actualizada correctamente.'));

            return $this->viticulturistRoleRedirect('invoices.index');
        } catch (\Exception $e) {
            $this->toastError($e instanceof RuntimeException ? $e->getMessage() : __('Error al actualizar la factura. Inténtalo de nuevo.'));
        }
    }

    public function render()
    {
        $user = Auth::user();
        $campaigns = Campaign::where('viticulturist_id', $user->id)->orderBy('year', 'desc')->get();

        return view('livewire.viticulturist.invoices.edit', [
            'campaigns' => $campaigns,
        ])->layout('layouts.app');
    }

    protected function rules(): array
    {
        if ($this->isLocked) {
            return $this->invoiceLockedRules();
        }

        return $this->invoiceUnlockedBaseRules('harvest,service,product,other');
    }

    private function vatTotals(): array
    {
        $taxIds = collect($this->items)->pluck('tax_id')->filter()->unique()->values()->all();
        $taxRates = empty($taxIds) ? collect() : Tax::whereIn('id', $taxIds)->get()->keyBy('id');

        return $this->invoiceService->calculateVatTotals($this->items, $taxRates);
    }
}
