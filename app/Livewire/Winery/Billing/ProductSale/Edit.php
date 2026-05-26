<?php

namespace App\Livewire\Winery\Billing\ProductSale;

use App\Livewire\Concerns\WithRoleAwareRedirect;
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

class Edit extends Component
{
    use WithToastNotifications, WithRoleAwareRedirect;

    public Invoice $invoice;

    public string $client_id          = '';
    public string $observations         = '';
    public string $observations_invoice = '';
    public string $payment_type        = '';
    public string $payment_status     = '';
    public string $payment_date       = '';
    public string $delivery_status    = '';
    public bool   $is_gift            = false;

    // ── Status modals ────────────────────────────────────────────────────────
    public bool   $showDeliveryModal     = false;
    public string $pendingDeliveryStatus = '';
    public bool   $showPaymentDateModal  = false;

    public array $items = [];

    public string $selectedLotId = '';

    public $availableTaxes = [];
    protected string $defaultTaxId = '';

    public function mount(int $id): void
    {
        $this->invoice = Invoice::where('user_id', Auth::id())
            ->where('invoice_type', 'wine_sale')
            ->with('items.wineLot')
            ->findOrFail($id);

        $this->client_id          = (string) $this->invoice->client_id;
        $this->observations         = $this->invoice->observations ?? '';
        $this->observations_invoice = $this->invoice->observations_invoice ?? '';
        $this->payment_type     = $this->invoice->payment_type ?? '';
        $this->payment_status   = $this->invoice->payment_status ?? 'unpaid';
        $this->payment_date     = $this->invoice->payment_date
            ? $this->invoice->payment_date->format('Y-m-d') : '';
        $this->delivery_status  = $this->invoice->delivery_status ?? 'pending';
        $this->is_gift          = (bool) $this->invoice->gift;

        $user = Auth::user();
        $this->availableTaxes = $user->taxes()->orderByPivot('order')->get();
        if ($this->availableTaxes->isEmpty()) {
            $this->availableTaxes = Tax::active()->orderBy('rate')->get();
        }

        $defaultTax         = $user->defaultTax()->first() ?? $this->availableTaxes->first();
        $this->defaultTaxId = (string) ($defaultTax?->id ?? '');

        $this->items = $this->invoice->items->map(fn ($item) => [
            'wine_lot_id'         => $item->wine_lot_id ? (int) $item->wine_lot_id : null,
            'name'                => $item->name,
            'description'         => $item->description ?? '',
            'sku'                 => $item->sku ?? '',
            'quantity'            => (string) $item->quantity,
            'available_qty'       => $item->wineLot ? (float) $item->wineLot->available_quantity + (float) $item->quantity : null,
            'unit_price'          => (string) $item->unit_price,
            'tax_id'              => (string) ($item->tax_id ?? $this->defaultTaxId),
            'discount_percentage' => (string) ($item->discount_percentage ?? 0),
            'concept_type'        => $item->concept_type ?? 'wine',
        ])->toArray();
    }

    // ── Computed properties ───────────────────────────────────────────────────

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

    // ── Añadir producto (solo en borrador) ────────────────────────────────────

    public function addProductToInvoice(): void
    {
        if (!$this->selectedLotId) return;

        $lot = ProductLot::where('user_id', Auth::id())->find($this->selectedLotId);

        if (!$lot) {
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
        $this->toastSuccess(__('Producto añadido.'));
    }

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

    // ── Guardar estados (entrega + cobro) ─────────────────────────────────────

    public function saveStatuses(): void
    {
        if ($this->invoice->status === 'cancelled') {
            $this->toastError(__('No se puede modificar una factura cancelada.'));
            return;
        }

        // Si cobro = pagado y no hay fecha → modal para pedir la fecha
        if ($this->payment_status === 'paid' && !$this->payment_date) {
            $this->showPaymentDateModal = true;
            return;
        }

        // Si la entrega cambia a un estado que mueve stock → confirmar
        $originalDelivery = $this->invoice->delivery_status;
        if ($this->delivery_status !== $originalDelivery
            && in_array($this->delivery_status, ['delivered', 'cancelled'])) {
            $this->pendingDeliveryStatus = $this->delivery_status;
            $this->showDeliveryModal     = true;
            return;
        }

        $this->persistStatuses();
    }

    // ── Modal: fecha de pago ──────────────────────────────────────────────────

    public function confirmPaymentDate(): void
    {
        $this->validate(
            ['payment_date' => 'required|date'],
            ['payment_date.required' => __('La fecha de pago es obligatoria.')]
        );

        $this->showPaymentDateModal = false;

        // Comprobar si además hay que confirmar entrega
        $originalDelivery = $this->invoice->delivery_status;
        if ($this->delivery_status !== $originalDelivery
            && in_array($this->delivery_status, ['delivered', 'cancelled'])) {
            $this->pendingDeliveryStatus = $this->delivery_status;
            $this->showDeliveryModal     = true;
            return;
        }

        $this->persistStatuses();
    }

    public function closePaymentDateModal(): void
    {
        $this->showPaymentDateModal = false;
        $this->resetValidation('payment_date');
    }

    // ── Modal: confirmación entrega (mueve stock) ─────────────────────────────

    public function closeDeliveryModal(): void
    {
        // Revertir el select al valor actual de la BD
        $this->delivery_status       = $this->invoice->delivery_status;
        $this->showDeliveryModal     = false;
        $this->pendingDeliveryStatus = '';
    }

    public function confirmDeliveryStatus(): void
    {
        $newStatus = $this->pendingDeliveryStatus;

        if (!in_array($newStatus, ['delivered', 'cancelled'])) {
            $this->closeDeliveryModal();
            return;
        }

        try {
            DB::transaction(function () use ($newStatus) {
                $this->invoice->load('items.wineLot');
                if (!$this->invoice->corrective) {
                    $action = $newStatus === 'delivered' ? 'deliver' : 'cancel';
                    ProductStockService::moveForInvoice($this->invoice, $action);
                }
                $this->invoice->update(['delivery_status' => $newStatus]);
            });

            $this->delivery_status = $newStatus;
            $this->showDeliveryModal     = false;
            $this->pendingDeliveryStatus = '';

            $this->persistPaymentStatus();

            $label = $newStatus === 'delivered' ? 'entregada' : 'cancelada';
            $this->toastSuccess("Estados guardados. Entrega: {$label}.");

        } catch (\Exception $e) {
            Log::error('Error al actualizar estado de entrega: ' . $e->getMessage(), [
                'invoice_id' => $this->invoice->id,
                'user_id'    => Auth::id(),
            ]);
            $this->toastError($e instanceof \RuntimeException ? $e->getMessage()  : __('Error al actualizar el estado de entrega.'));
            $this->closeDeliveryModal();
        }
    }

    private function persistStatuses(): void
    {
        $this->invoice->update([
            'delivery_status' => $this->delivery_status,
            'payment_status'  => $this->payment_status,
            'payment_type'    => $this->payment_type ?: null,
            'payment_date'    => $this->payment_status === 'paid' ? ($this->payment_date ?: null) : null,
        ]);
        $this->invoice->refresh();
        $this->toastSuccess(__('Estados actualizados correctamente.'));
    }

    private function persistPaymentStatus(): void
    {
        $this->invoice->update([
            'payment_status' => $this->payment_status,
            'payment_type'   => $this->payment_type ?: null,
            'payment_date'   => $this->payment_status === 'paid' ? ($this->payment_date ?: null) : null,
        ]);
        $this->invoice->refresh();
    }

    // ── Validation ────────────────────────────────────────────────────────────

    protected function rules(): array
    {
        return [
            'client_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value && !\App\Models\Client::where('id', $value)->where('user_id', \Illuminate\Support\Facades\Auth::id())->exists()) {
                        $fail(__('El cliente seleccionado no es válido.'));
                    }
                },
            ],
            'payment_type'                => 'nullable|in:cash,transfer,check,other',
            'payment_status'              => 'required|in:unpaid,partial,paid',
            'observations'                => 'nullable|string',
            'observations_invoice'        => 'nullable|string',
            'items'                       => 'required|array|min:1',
            'items.*.name'                => 'required|string|max:255',
            'items.*.wine_lot_id' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value && !\App\Models\ProductLot::where('id', $value)->where('user_id', \Illuminate\Support\Facades\Auth::id())->exists()) {
                        $fail(__('El lote de vino seleccionado no es válido.'));
                    }
                },
            ],
            'items.*.quantity'            => 'required|numeric|min:0.001',
            'items.*.unit_price'          => 'required|numeric|min:0',
            'items.*.tax_id'              => 'nullable|exists:taxes,id',
            'items.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
            'items.*.description'         => 'nullable|string',
            'items.*.sku'                 => 'nullable|string|max:100',
        ];
    }

    protected function validationAttributes(): array
    {
        $attrs = ['client_id' => 'cliente'];
        foreach ($this->items as $i => $_) {
            $attrs["items.{$i}.name"]       = 'concepto';
            $attrs["items.{$i}.quantity"]   = 'cantidad';
            $attrs["items.{$i}.unit_price"] = 'precio unitario';
        }
        return $attrs;
    }

    // ── Save ──────────────────────────────────────────────────────────────────

    public function save()
    {
        $this->validate();

        if ($this->isLocked) {
            $this->toastError(__('Esta factura no se puede modificar.'));
            return;
        }

        $client   = Client::where('user_id', Auth::id())->findOrFail($this->client_id);
        $taxRates = $this->availableTaxes->keyBy('id');

        try {
            DB::transaction(function () use ($client, $taxRates) {
                // 1. Recargar items frescos del DB antes de restaurar stock
                $this->invoice->load('items.wineLot');
                ProductStockService::moveForInvoice($this->invoice, 'cancel');

                // 2. Borrar líneas antiguas
                InvoiceItem::withoutEvents(fn () => $this->invoice->items()->delete());

                // 3. Calcular totales
                $subtotal = $discountAmount = $taxAmount = 0;

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

                // 4. Actualizar cabecera
                $this->invoice->update([
                    'client_id'            => $client->id,
                    'billing_first_name'   => $client->first_name,
                    'billing_last_name'    => $client->last_name,
                    'billing_company_name' => $client->company_name,
                    'billing_email'        => $client->email,
                    'billing_phone'        => $client->phone,
                    'gift'                 => $this->is_gift,
                    'subtotal'             => round($subtotal * $multiplyGift, 3),
                    'discount_amount'      => round($discountAmount * $multiplyGift, 3),
                    'tax_base'             => round($taxBase * $multiplyGift, 3),
                    'tax_amount'           => round($taxAmount * $multiplyGift, 3),
                    'total_amount'         => round($total * $multiplyGift, 3),
                    'payment_status'       => $this->payment_status,
                    'payment_type'         => $this->payment_type ?: null,
                    'observations'         => $this->observations ?: null,
                    'observations_invoice' => $this->observations_invoice ?: null,
                ]);

                // 5. Crear líneas nuevas + mover stock
                InvoiceItem::withoutEvents(function () use ($taxRates, $multiplyGift) {
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

                        $createdItem = $this->invoice->items()->create([
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
                            ProductStockService::moveOnCreate($this->invoice, $createdItem, $lot, $qty);
                        }
                    }
                });
            });

            $this->toastSuccess(__('Factura actualizada correctamente.'));
            return $this->roleRedirect('invoices.products.index');

        } catch (\Exception $e) {
            Log::error('Error al editar factura de productos: ' . $e->getMessage(), [
                'invoice_id' => $this->invoice->id,
                'user_id'    => Auth::id(),
                'exception'  => $e,
            ]);
            $this->toastError($e instanceof \RuntimeException ? $e->getMessage()  : __('Error al guardar los cambios.'));
        }
    }

    public function render()
    {
        $clients  = Client::where('user_id', Auth::id())->where('active', true)->orderBy('first_name')->orderBy('company_name')->get();
        $existingLotIds = collect($this->items)->pluck('wine_lot_id')->filter()->values()->all();
        $productLots = ProductLot::where('user_id', Auth::id())->where('archived', false)
            ->where(function ($q) use ($existingLotIds) {
                $q->where('available_quantity', '>', 0)
                  ->orWhereIn('id', $existingLotIds);
            })
            ->orderByDesc('vintage')->orderBy('name')->get();

        return view('livewire.winery.billing.products.edit', [
            'clients'        => $clients,
            'wineLots'       => $productLots,
            'availableTaxes' => $this->availableTaxes,
            'isLocked'       => $this->isLocked,
            'isInvoiced'     => $this->isInvoiced,
        ])->layout('layouts.app');
    }
}
