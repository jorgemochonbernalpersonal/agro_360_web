<?php

namespace App\Livewire\Viticulturist\Invoices;

use App\Livewire\Concerns\WithInvoiceClientAddress;
use App\Livewire\Concerns\WithInvoiceFormRules;
use App\Livewire\Concerns\WithInvoiceHarvestItems;
use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\MarketedHarvest;
use App\Models\Tax;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use RuntimeException;

class Create extends Component
{
    use WithInvoiceClientAddress, WithInvoiceFormRules, WithInvoiceHarvestItems,
        WithRoleAwareRedirect, WithToastNotifications;

    public $client_id = '';

    public $client_address_id = '';

    public $invoice_date = '';

    public $delivery_note_date = '';

    public $items = [];

    public $observations = '';

    public $observations_invoice = '';

    public $payment_type = '';

    public $delivery_note_code = '';

    public $delivery_note_code_auto = '';

    public $delivery_note_code_modified = false;

    public $availableClients = [];

    public $availableTaxes = [];

    public $fromHarvestRoute = false;

    public $requiredHarvestId = null;

    public $harvestAdded = false;

    public ?int $marketedHarvestId = null;

    protected InvoiceService $invoiceService;

    public function boot(InvoiceService $invoiceService): void
    {
        $this->invoiceService = $invoiceService;
    }

    public function mount(): void
    {
        $this->invoice_date = now()->format('Y-m-d');
        $this->delivery_note_date = now()->format('Y-m-d');

        $harvestId = request()->query('harvest_id');
        $this->fromHarvestRoute = $harvestId !== null;

        if ($harvestId && $this->fromHarvestRoute) {
            $this->requiredHarvestId = $harvestId;
        }

        $marketedHarvestId = request()->query('marketed_harvest_id');
        if ($marketedHarvestId) {
            $this->marketedHarvestId = (int) $marketedHarvestId;
        }

        $this->loadData();

        $user = Auth::user();
        $settings = \App\Models\InvoicingSetting::getOrCreateForUser($user->id);
        $this->delivery_note_code_auto = $settings->getDeliveryNotePreview();
        $this->delivery_note_code = $this->delivery_note_code_auto;

        if ($this->requiredHarvestId && ! $this->harvestAdded) {
            $this->selectedHarvestId = $this->requiredHarvestId;
            $this->addHarvestToInvoice();
        }
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

    public function updatedDeliveryNoteCode($value): void
    {
        $this->delivery_note_code_modified = $value !== $this->delivery_note_code_auto;
    }

    public function addItem(): void
    {
        $this->items[] = [
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
        if ($this->fromHarvestRoute && isset($this->items[$index]['harvest_id']) && $this->items[$index]['harvest_id']) {
            $harvestItemsCount = count(array_filter($this->items, fn ($i) => ! empty($i['harvest_id'])));

            if ($harvestItemsCount <= 1) {
                $this->toastError(__('Debes mantener al menos una cosecha en la factura.'));

                return;
            }
        }

        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function save()
    {
        if ($this->fromHarvestRoute) {
            $hasHarvest = ! empty(array_filter($this->items, fn ($i) => ! empty($i['harvest_id'])));

            if (! $hasHarvest) {
                $this->addError('items', __('Debes seleccionar al menos una cosecha para facturar.'));

                return;
            }
        }

        $this->validate();

        $user = Auth::user();
        $deliveryNoteCode = null;

        try {
            DB::transaction(function () use ($user, &$deliveryNoteCode) {
                $deliveryNoteCode = $this->invoiceService->generateDeliveryNoteCode(
                    $user->id,
                    $this->delivery_note_code_modified,
                    $this->delivery_note_code,
                );

                $taxIds = collect($this->items)->pluck('tax_id')->filter()->unique()->values()->all();
                $taxRates = empty($taxIds) ? collect() : Tax::whereIn('id', $taxIds)->get()->keyBy('id');
                $totals = $this->invoiceService->calculateVatTotals($this->items, $taxRates);

                $invoice = Invoice::create([
                    'user_id' => $user->id,
                    'client_id' => $this->client_id,
                    'client_address_id' => $this->client_address_id ?: null,
                    'order_date' => $this->invoice_date,
                    'invoice_number' => null,
                    'delivery_note_code' => $deliveryNoteCode,
                    'invoice_date' => $this->invoice_date,
                    'delivery_note_date' => $this->delivery_note_date ?: now(),
                    'subtotal' => $totals['tax_base'],
                    'discount_amount' => $totals['discount_amount'],
                    'tax_base' => $totals['tax_base'],
                    'tax_rate' => $totals['effective_tax_rate'],
                    'tax_amount' => $totals['tax_amount'],
                    'total_amount' => $totals['total'],
                    'status' => 'draft',
                    'delivery_status' => 'pending',
                    'payment_status' => 'unpaid',
                    'payment_type' => $this->payment_type ?: null,
                    'observations' => $this->observations ?: null,
                    'observations_invoice' => $this->observations_invoice ?: null,
                ]);

                foreach ($this->items as $itemData) {
                    $tax = $taxRates->get($itemData['tax_id'] ?? null);
                    $line = $this->invoiceService->calculateVatLine($itemData, $tax);

                    $invoice->items()->create([
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

                $invoice->logAction('created', 'Factura creada', [
                    'client_id' => $this->client_id,
                    'total_amount' => $totals['total'],
                    'items_count' => count($this->items),
                    'delivery_note_code' => $deliveryNoteCode,
                ]);

                // NOTE: InvoiceItemObserver.created already called ContainerStockService::reserveStock()
                // for each item above. Do NOT call HarvestStockService here — would double-reserve.

                if ($this->marketedHarvestId) {
                    MarketedHarvest::where('viticulturist_id', $user->id)
                        ->where('id', $this->marketedHarvestId)
                        ->update(['invoice_id' => $invoice->id]);
                }
            });

            $this->toastSuccess("Albarán {$deliveryNoteCode} creado. Emítelo para generar el número de factura.");

            return $this->viticulturistRoleRedirect('invoices.index');
        } catch (\Exception $e) {
            $this->toastError($e instanceof RuntimeException ? $e->getMessage() : __('Error al crear la factura. Inténtalo de nuevo.'));
        }
    }

    public function render()
    {
        $user = Auth::user();
        $campaigns = Campaign::where('viticulturist_id', $user->id)->orderBy('year', 'desc')->get();

        return view('livewire.viticulturist.invoices.create', [
            'campaigns' => $campaigns,
        ])->layout('layouts.app');
    }

    protected function rules(): array
    {
        return $this->invoiceCreateRules('harvest,service,product,other');
    }

    protected function messages(): array
    {
        return [
            'client_address_id.required' => __('Debes seleccionar un cliente con dirección. Este cliente no tiene direcciones configuradas.'),
            'items.required' => __('Debes añadir al menos un item a la factura.'),
            'items.min' => __('Debes añadir al menos un item a la factura.'),
        ];
    }
}
