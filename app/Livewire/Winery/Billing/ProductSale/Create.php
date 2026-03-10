<?php

namespace App\Livewire\Winery\Billing\ProductSale;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicingSetting;
use App\Models\ProductLot;
use App\Models\Tax;
use App\Services\ProductStockService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Create extends Component
{
    use WithToastNotifications;

    public string $client_id          = '';
    public string $client_address_id  = '';
    public string $order_date         = '';
    public string $delivery_note_date = '';
    public string $observations          = '';
    public string $observations_invoice  = '';
    public string $payment_type       = '';
    public string $delivery_note_code = '';
    public bool   $is_gift            = false;

    public array $items = [];

    public string $selectedLotId = '';

    public $availableTaxes    = [];
    public $availableAddresses = [];
    protected string $defaultTaxId = '';

    public function mount(): void
    {
        $this->order_date         = now()->toDateString();
        $this->delivery_note_date = now()->toDateString();

        $user = Auth::user();
        $this->availableTaxes = $user->taxes()->orderByPivot('order')->get();
        if ($this->availableTaxes->isEmpty()) {
            $this->availableTaxes = Tax::active()->orderBy('rate')->get();
        }

        $defaultTax         = $user->defaultTax()->first() ?? $this->availableTaxes->first();
        $this->defaultTaxId = (string) ($defaultTax?->id ?? '');

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
                $this->client_address_id  = '';
            }
        } else {
            $this->availableAddresses = collect();
            $this->client_address_id  = '';
        }
    }

    // ── Computed totals ───────────────────────────────────────────────────────

    public function getSubtotalProperty(): float
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += (float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0);
        }
        return round($total, 3);
    }

    public function getDiscountAmountProperty(): float
    {
        $total = 0;
        foreach ($this->items as $item) {
            $sub = (float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0);
            $total += $sub * ((float) ($item['discount_percentage'] ?? 0) / 100);
        }
        return round($total, 3);
    }

    public function getTaxAmountProperty(): float
    {
        $taxRates = $this->availableTaxes->keyBy('id');
        $total = 0;
        foreach ($this->items as $item) {
            $sub     = (float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0);
            $discAmt = $sub * ((float) ($item['discount_percentage'] ?? 0) / 100);
            $base    = $sub - $discAmt;
            $rate    = $item['tax_id'] ? (float) ($taxRates[$item['tax_id']]?->rate ?? 0) : 0;
            $total  += $base * ($rate / 100);
        }
        return round($total, 3);
    }

    public function getTotalAmountProperty(): float
    {
        return round($this->subtotal - $this->discountAmount + $this->taxAmount, 3);
    }

    // ── Añadir producto desde selector ───────────────────────────────────────

    public function addProductToInvoice(): void
    {
        if (!$this->selectedLotId) return;

        $lot = ProductLot::where('user_id', Auth::id())->find($this->selectedLotId);

        if (!$lot) {
            $this->toastError('Lote no encontrado.');
            return;
        }

        if ((float) $lot->available_quantity <= 0) {
            $this->toastError('Este lote no tiene stock disponible para facturar.');
            return;
        }

        foreach ($this->items as $item) {
            if (isset($item['wine_lot_id']) && (int) $item['wine_lot_id'] === $lot->id) {
                $this->toastError('Este lote ya está en la factura.');
                return;
            }
        }

        $this->items[] = [
            'wine_lot_id'         => $lot->id,
            'name'                => $lot->name . ($lot->vintage ? " ({$lot->vintage})" : ''),
            'description'         => '',
            'sku'                 => $lot->sku ?? '',
            'quantity'            => 1,
            'available_qty'       => (float) $lot->available_quantity,
            'unit_price'          => $lot->price_per_unit ? (float) $lot->price_per_unit : 0,
            'discount_percentage' => 0,
            'tax_id'              => $this->defaultTaxId ?: null,
            'concept_type'        => 'wine',
        ];

        $this->selectedLotId = '';
        $this->toastSuccess('Producto añadido al albarán.');
    }

    // ── Añadir concepto manual ────────────────────────────────────────────────

    public function addItem(): void
    {
        $this->items[] = [
            'wine_lot_id'         => null,
            'name'                => '',
            'description'         => '',
            'sku'                 => '',
            'quantity'            => 1,
            'available_qty'       => null,
            'unit_price'          => 0,
            'discount_percentage' => 0,
            'tax_id'              => $this->defaultTaxId ?: null,
            'concept_type'        => 'other',
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    // ── Validation ────────────────────────────────────────────────────────────

    protected function rules(): array
    {
        return [
            'client_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value && !\App\Models\Client::where('id', $value)->where('user_id', \Illuminate\Support\Facades\Auth::id())->exists()) {
                        $fail('El cliente seleccionado no es válido.');
                    }
                },
            ],
            'client_address_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value && $this->client_id && !\App\Models\ClientAddress::where('id', $value)->where('client_id', $this->client_id)->exists()) {
                        $fail('La dirección seleccionada no pertenece al cliente.');
                    }
                },
            ],
            'order_date'                   => 'required|date',
            'delivery_note_date'           => 'nullable|date',
            'delivery_note_code'           => 'required|string|max:255',
            'payment_type'                 => 'nullable|in:cash,transfer,check,other',
            'observations'                 => 'nullable|string',
            'observations_invoice'         => 'nullable|string',
            'items'                        => 'required|array|min:1',
            'items.*.name'                 => 'required|string|max:255',
            'items.*.wine_lot_id' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value && !\App\Models\ProductLot::where('id', $value)->where('user_id', \Illuminate\Support\Facades\Auth::id())->exists()) {
                        $fail('El lote de vino seleccionado no es válido.');
                    }
                },
            ],
            'items.*.quantity'             => 'required|numeric|min:0.001',
            'items.*.unit_price'           => 'required|numeric|min:0',
            'items.*.tax_id'               => 'nullable|exists:taxes,id',
            'items.*.discount_percentage'  => 'nullable|numeric|min:0|max:100',
            'items.*.description'          => 'nullable|string',
            'items.*.sku'                  => 'nullable|string|max:100',
        ];
    }

    protected function validationAttributes(): array
    {
        $attrs = [
            'client_id'          => 'cliente',
            'order_date'         => 'fecha de pedido',
            'delivery_note_date' => 'fecha de albarán',
        ];
        foreach ($this->items as $i => $_) {
            $attrs["items.{$i}.name"]     = 'concepto';
            $attrs["items.{$i}.quantity"] = 'cantidad';
            $attrs["items.{$i}.unit_price"] = 'precio unitario';
        }
        return $attrs;
    }

    // ── Save ──────────────────────────────────────────────────────────────────

    public function save()
    {
        $this->validate();

        $client   = Client::where('user_id', Auth::id())->findOrFail($this->client_id);
        $taxRates = $this->availableTaxes->keyBy('id');

        try {
            DB::beginTransaction();

            $settings = InvoicingSetting::getOrCreateForUser(Auth::id());
            $noteCode = $settings->generateAndIncrementDeliveryNoteCode();

            // Calcular totales
            $subtotal       = 0;
            $discountAmount = 0;
            $taxAmount      = 0;

            foreach ($this->items as $item) {
                $qty          = (float) $item['quantity'];
                $unitPrice    = (float) $item['unit_price'];
                $discPct      = (float) ($item['discount_percentage'] ?? 0);
                $lineSubtotal = $qty * $unitPrice;
                $lineDiscount = $lineSubtotal * ($discPct / 100);
                $lineBase     = $lineSubtotal - $lineDiscount;
                $tax          = $item['tax_id'] ? $taxRates[$item['tax_id']] ?? null : null;
                $taxRate      = $tax ? (float) $tax->rate : 0;

                $subtotal       += $lineSubtotal;
                $discountAmount += $lineDiscount;
                $taxAmount      += $lineBase * ($taxRate / 100);
            }

            $taxBase = $subtotal - $discountAmount;
            $total   = $taxBase + $taxAmount;

            $multiplyGift = $this->is_gift ? 0 : 1;

            // Crear factura en borrador (sin número de factura)
            $invoice = Invoice::create([
                'user_id'              => Auth::id(),
                'client_id'            => $client->id,
                'client_address_id'    => $this->client_address_id ?: null,
                'invoice_type'         => 'wine_sale',
                'delivery_note_code'   => $noteCode,
                'delivery_note_date'   => $this->delivery_note_date ?: null,
                'order_date'           => $this->order_date,
                'invoice_date'         => null,
                'delivery_status'      => 'pending',
                'status'               => 'draft',
                'payment_status'       => 'unpaid',
                'payment_type'         => $this->payment_type ?: null,
                'gift'                 => $this->is_gift,
                'billing_first_name'   => $client->first_name,
                'billing_last_name'    => $client->last_name,
                'billing_company_name' => $client->company_name,
                'billing_email'        => $client->email,
                'billing_phone'        => $client->phone,
                'subtotal'             => round($subtotal * $multiplyGift, 3),
                'discount_amount'      => round($discountAmount * $multiplyGift, 3),
                'tax_base'             => round($taxBase * $multiplyGift, 3),
                'tax_amount'           => round($taxAmount * $multiplyGift, 3),
                'total_amount'         => round($total * $multiplyGift, 3),
                'observations'         => $this->observations ?: null,
                'observations_invoice' => $this->observations_invoice ?: null,
            ]);

            // Crear líneas
            foreach ($this->items as $item) {
                $qty          = (float) $item['quantity'];
                $unitPrice    = (float) $item['unit_price'];
                $discPct      = (float) ($item['discount_percentage'] ?? 0);
                $tax          = $item['tax_id'] ? $taxRates[$item['tax_id']] ?? null : null;
                $taxRate      = $tax ? (float) $tax->rate : 0;
                $lineSubtotal = round($qty * $unitPrice, 3);
                $lineDiscount = round($lineSubtotal * ($discPct / 100), 3);
                $lineBase     = round($lineSubtotal - $lineDiscount, 3);
                $taxAmountLine = round($lineBase * ($taxRate / 100), 3);

                $lot = $item['wine_lot_id']
                    ? ProductLot::where('user_id', Auth::id())->lockForUpdate()->find($item['wine_lot_id'])
                    : null;

                $createdItem = InvoiceItem::create([
                    'invoice_id'          => $invoice->id,
                    'wine_lot_id'         => $lot?->id,
                    'concept_type'        => $item['concept_type'] ?? ($lot ? 'wine' : 'other'),
                    'name'                => $item['name'],
                    'description'         => $item['description'] ?: null,
                    'sku'                 => $item['sku'] ?: ($lot?->sku ?? null),
                    'quantity'            => $qty,
                    'unit_price'          => $unitPrice,
                    'discount_percentage' => $discPct,
                    'discount_amount'     => $lineDiscount * $multiplyGift,
                    'tax_id'              => $tax?->id,
                    'tax_name'            => $tax?->name,
                    'tax_rate'            => $taxRate,
                    'subtotal'            => $lineSubtotal * $multiplyGift,
                    'tax_base'            => $lineBase * $multiplyGift,
                    'tax_amount'          => $taxAmountLine * $multiplyGift,
                    'total'               => ($lineBase + $taxAmountLine) * $multiplyGift,
                ]);

                if ($lot) {
                    ProductStockService::moveOnCreate($invoice, $createdItem, $lot, $qty);
                }
            }

            DB::commit();

            $this->toastSuccess("Albarán {$noteCode} creado. Emítelo para generar el número de factura.");
            return $this->redirect(route('winery.invoices.products.index'), navigate: true);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear factura de productos: ' . $e->getMessage(), [
                'user_id'   => Auth::id(),
                'exception' => $e,
            ]);
            $this->toastError($e instanceof \RuntimeException ? $e->getMessage() : 'Error al crear la factura. Inténtalo de nuevo.');
        }
    }

    public function render()
    {
        $clients     = Client::where('user_id', Auth::id())->where('active', true)->orderBy('first_name')->orderBy('company_name')->get();
        $productLots = ProductLot::where('user_id', Auth::id())->where('archived', false)
            ->where('available_quantity', '>', 0)
            ->orderByDesc('vintage')->orderBy('name')->get();

        return view('livewire.winery.billing.products.create', [
            'clients'        => $clients,
            'wineLots'       => $productLots,
            'availableTaxes' => $this->availableTaxes,
        ])->layout('layouts.app');
    }
}
