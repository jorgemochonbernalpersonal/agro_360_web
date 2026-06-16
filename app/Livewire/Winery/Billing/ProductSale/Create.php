<?php

namespace App\Livewire\Winery\Billing\ProductSale;

use App\Livewire\Concerns\WithProductSaleFormRules;
use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicingSetting;
use App\Models\ProductLot;
use App\Models\Tax;
use App\Services\InvoiceService;
use App\Services\ProductStockService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Create extends Component
{
    use WithProductSaleFormRules, WithRoleAwareRedirect, WithToastNotifications;

    public string $client_id = '';

    public string $client_address_id = '';

    public string $order_date = '';

    public string $delivery_note_date = '';

    public string $observations = '';

    public string $observations_invoice = '';

    public string $payment_type = '';

    public string $delivery_note_code = '';

    public bool $is_gift = false;

    public array $items = [];

    public string $selectedLotId = '';

    public $availableTaxes = [];

    public $availableAddresses = [];

    protected InvoiceService $invoiceService;

    protected string $defaultTaxId = '';

    public function boot(InvoiceService $invoiceService): void
    {
        $this->invoiceService = $invoiceService;
    }

    public function mount(): void
    {
        $this->order_date = now()->toDateString();
        $this->delivery_note_date = now()->toDateString();

        $user = Auth::user();
        $this->availableTaxes = $user->taxes()->orderByPivot('order')->get();
        if ($this->availableTaxes->isEmpty()) {
            $this->availableTaxes = Tax::active()->orderBy('rate')->get();
        }

        $defaultTax = $user->defaultTax()->first() ?? $this->availableTaxes->first();
        $this->defaultTaxId = (string) ($defaultTax->id ?? '');

        $settings = InvoicingSetting::getOrCreateForUser(Auth::id());
        $this->delivery_note_code = $settings->getDeliveryNotePreview();
    }

    public function updatedClientId(string $value): void
    {
        if ($value) {
            $client = Client::where('user_id', Auth::id())->with(['addresses.municipality', 'addresses.province'])->find($value);
            if ($client) {
                $this->availableAddresses = $client->addresses;
                $primary = $client->addresses->firstWhere('is_default', true) ?? $client->addresses->first();
                $this->client_address_id = $primary ? (string) $primary->id : '';
            } else {
                $this->availableAddresses = collect();
                $this->client_address_id = '';
            }
        } else {
            $this->availableAddresses = collect();
            $this->client_address_id = '';
        }
    }

    // ── Computed totals ───────────────────────────────────────────────────────

    // El subtotal de venta de producto es el bruto (antes de descuentos), por convención.
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

    // ── Añadir producto desde selector ───────────────────────────────────────

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

        if ((float) $lot->available_quantity <= 0) {
            $this->toastError(__('Este lote no tiene stock disponible para facturar.'));

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
        $this->toastSuccess(__('Producto añadido al albarán.'));
    }

    // ── Añadir concepto manual ────────────────────────────────────────────────

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

    // ── Save ──────────────────────────────────────────────────────────────────

    public function save()
    {
        $this->validate();

        $client = Client::where('user_id', Auth::id())->findOrFail($this->client_id);
        $taxRates = $this->availableTaxes->keyBy('id');

        try {
            DB::beginTransaction();

            $noteCode = $this->invoiceService->generateDeliveryNoteCode(Auth::id(), false, '');

            $totals = $this->invoiceService->calculateVatTotals($this->items, $taxRates);

            $multiplyGift = $this->is_gift ? 0 : 1;

            // Crear factura en borrador (sin número de factura)
            $invoice = Invoice::create([
                'user_id' => Auth::id(),
                'client_id' => $client->id,
                'client_address_id' => $this->client_address_id ?: null,
                'invoice_type' => 'wine_sale',
                'delivery_note_code' => $noteCode,
                'delivery_note_date' => $this->delivery_note_date ?: null,
                'order_date' => $this->order_date,
                'invoice_date' => null,
                'delivery_status' => 'pending',
                'status' => 'draft',
                'payment_status' => 'unpaid',
                'payment_type' => $this->payment_type ?: null,
                'gift' => $this->is_gift,
                'billing_first_name' => $client->first_name,
                'billing_last_name' => $client->last_name,
                'billing_company_name' => $client->company_name,
                'billing_email' => $client->email,
                'billing_phone' => $client->phone,
                'subtotal' => round($totals['gross_subtotal'] * $multiplyGift, 3),
                'discount_amount' => round($totals['discount_amount'] * $multiplyGift, 3),
                'tax_base' => round($totals['tax_base'] * $multiplyGift, 3),
                'tax_amount' => round($totals['tax_amount'] * $multiplyGift, 3),
                'total_amount' => round($totals['total'] * $multiplyGift, 3),
                'observations' => $this->observations ?: null,
                'observations_invoice' => $this->observations_invoice ?: null,
            ]);

            // Crear líneas
            foreach ($this->items as $item) {
                $tax = $item['tax_id'] ? $taxRates[$item['tax_id']] ?? null : null;
                $line = $this->invoiceService->calculateVatLine($item, $tax);
                $qty = $line['quantity'];

                $lot = $item['wine_lot_id']
                    ? ProductLot::where('user_id', Auth::id())->lockForUpdate()->find($item['wine_lot_id'])
                    : null;

                $createdItem = InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'wine_lot_id' => $lot?->id,
                    'concept_type' => $item['concept_type'] ?? ($lot ? 'wine' : 'other'),
                    'name' => $item['name'],
                    'description' => $item['description'] ?: null,
                    'sku' => $item['sku'] ?: ($lot->sku ?? null),
                    'quantity' => $qty,
                    'unit_price' => $line['unit_price'],
                    'discount_percentage' => $line['discount_percentage'],
                    'discount_amount' => $line['discount_amount'] * $multiplyGift,
                    'tax_id' => $tax?->id,
                    'tax_name' => $tax?->name,
                    'tax_rate' => $line['tax_rate'],
                    'subtotal' => $line['subtotal'] * $multiplyGift,
                    'tax_base' => $line['tax_base'] * $multiplyGift,
                    'tax_amount' => $line['tax_amount'] * $multiplyGift,
                    'total' => $line['total'] * $multiplyGift,
                ]);

                if ($lot) {
                    ProductStockService::moveOnCreate($invoice, $createdItem, $lot, $qty);
                }
            }

            DB::commit();

            $this->toastSuccess("Albarán {$noteCode} creado. Emítelo para generar el número de factura.");

            return $this->roleRedirect('invoices.products.index');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear factura de productos: '.$e->getMessage(), [
                'user_id' => Auth::id(),
                'exception' => $e,
            ]);
            $this->toastError($e instanceof \RuntimeException ? $e->getMessage() : __('Error al crear la factura. Inténtalo de nuevo.'));
        }
    }

    public function render()
    {
        $clients = Client::where('user_id', Auth::id())->where('active', true)->orderBy('first_name')->orderBy('company_name')->get();
        $productLots = ProductLot::where('user_id', Auth::id())->where('archived', false)
            ->where('available_quantity', '>', 0)
            ->orderByDesc('vintage')->orderBy('name')->get();

        return view('livewire.winery.billing.products.create', [
            'clients' => $clients,
            'wineLots' => $productLots,
            'availableTaxes' => $this->availableTaxes,
        ])->layout('layouts.app');
    }

    // ── Validation ────────────────────────────────────────────────────────────

    protected function rules(): array
    {
        return array_merge($this->productSaleBaseRules(), [
            'client_address_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value && $this->client_id && ! \App\Models\ClientAddress::where('id', $value)->where('client_id', $this->client_id)->exists()) {
                        $fail(__('La dirección seleccionada no pertenece al cliente.'));
                    }
                },
            ],
            'order_date'         => 'required|date',
            'delivery_note_date' => 'nullable|date',
            'delivery_note_code' => 'required|string|max:255',
        ]);
    }

    protected function validationAttributes(): array
    {
        $attrs = [
            'client_id' => 'cliente',
            'order_date' => 'fecha de pedido',
            'delivery_note_date' => 'fecha de albarán',
        ];
        foreach ($this->items as $i => $_) {
            $attrs["items.{$i}.name"] = 'concepto';
            $attrs["items.{$i}.quantity"] = 'cantidad';
            $attrs["items.{$i}.unit_price"] = 'precio unitario';
        }

        return $attrs;
    }

    /**
     * Totales VAT en vivo para la UI. Misma fuente de verdad que save()
     * (InvoiceService::calculateVatTotals), de modo que el total mostrado no puede
     * diverger del total persistido.
     */
    private function vatTotals(): array
    {
        return $this->invoiceService->calculateVatTotals(
            $this->items,
            $this->availableTaxes->keyBy('id'),
        );
    }
}
