<?php

namespace App\Livewire\Winery\Billing\WineSale;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicingSetting;
use App\Models\Tax;
use App\Models\WineLot;
use App\Services\WineStockService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Edit extends Component
{
    use WithToastNotifications;

    public Invoice $invoice;

    public string $client_id          = '';
    public string $invoice_date       = '';
    public string $delivery_note_date = '';
    public string $observations       = '';
    public string $payment_type       = '';
    public string $payment_status     = '';

    public array $items = [];

    public string $selectedLotId = '';

    public $availableTaxes = [];
    protected string $defaultTaxId = '';

    // Emitir modal
    public bool   $showEmitirModal = false;
    public string $emitirDate      = '';

    public function mount(int $id): void
    {
        $this->invoice = Invoice::where('user_id', Auth::id())
            ->where('invoice_type', 'wine_sale')
            ->with('items.wineLot')
            ->findOrFail($id);

        $this->client_id          = (string) $this->invoice->client_id;
        $this->invoice_date       = $this->invoice->invoice_date
            ? $this->invoice->invoice_date->format('Y-m-d') : '';
        $this->delivery_note_date = $this->invoice->delivery_note_date
            ? $this->invoice->delivery_note_date->format('Y-m-d') : '';
        $this->observations       = $this->invoice->observations ?? '';
        $this->payment_type       = $this->invoice->payment_type ?? '';
        $this->payment_status     = $this->invoice->payment_status ?? 'unpaid';

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
            'available_qty'       => $item->wineLot ? (float) $item->wineLot->available_quantity : null,
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

        $lot = WineLot::where('user_id', Auth::id())->find($this->selectedLotId);

        if (!$lot) {
            $this->toastError('Lote no encontrado.');
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
        $this->toastSuccess('Producto añadido.');
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

    // ── Emitir ────────────────────────────────────────────────────────────────

    public function openEmitirModal(): void
    {
        $this->emitirDate      = now()->toDateString();
        $this->showEmitirModal = true;
    }

    public function closeEmitirModal(): void
    {
        $this->showEmitirModal = false;
        $this->emitirDate      = '';
        $this->resetValidation();
    }

    public function markAsSent(): void
    {
        $this->validate(
            ['emitirDate' => 'required|date'],
            ['emitirDate.required' => 'La fecha de factura es obligatoria.']
        );

        if ($this->invoice->status !== 'draft') {
            $this->toastError('Esta factura ya no está en borrador.');
            $this->closeEmitirModal();
            return;
        }

        try {
            $invoiceNumber = null;
            DB::transaction(function () use (&$invoiceNumber) {
                $settings      = InvoicingSetting::getOrCreateForUser(Auth::id());
                $invoiceNumber = $settings->generateAndIncrementInvoiceCode();

                $this->invoice->update([
                    'invoice_number' => $invoiceNumber,
                    'invoice_date'   => $this->emitirDate,
                    'status'         => 'sent',
                ]);
            });

            $this->closeEmitirModal();
            $this->invoice->refresh();
            $this->toastSuccess("Factura {$invoiceNumber} emitida correctamente.");

        } catch (\Exception $e) {
            Log::error('Error al emitir factura de vino: ' . $e->getMessage(), [
                'invoice_id' => $this->invoice->id,
                'user_id'    => Auth::id(),
            ]);
            $this->toastError('Error al emitir la factura.');
        }
    }

    // ── Validation ────────────────────────────────────────────────────────────

    protected function rules(): array
    {
        if ($this->isLocked) {
            return [
                'payment_status' => 'required|in:unpaid,partial,paid',
                'payment_type'   => 'nullable|in:cash,transfer,check,other',
            ];
        }

        return [
            'client_id'                   => 'required|exists:clients,id',
            'invoice_date'                => 'required|date',
            'delivery_note_date'          => 'nullable|date',
            'payment_type'                => 'nullable|in:cash,transfer,check,other',
            'payment_status'              => 'required|in:unpaid,partial,paid',
            'observations'                => 'nullable|string',
            'items'                       => 'required|array|min:1',
            'items.*.name'                => 'required|string|max:255',
            'items.*.wine_lot_id'         => 'nullable|exists:wine_lots,id',
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
        $attrs = ['client_id' => 'cliente', 'invoice_date' => 'fecha'];
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
            $this->invoice->update([
                'payment_status' => $this->payment_status,
                'payment_type'   => $this->payment_type ?: null,
            ]);
            $this->toastSuccess('Estado de cobro actualizado.');
            return $this->redirect(route('winery.invoices.products.index'), navigate: true);
        }

        $client   = Client::where('user_id', Auth::id())->findOrFail($this->client_id);
        $taxRates = $this->availableTaxes->keyBy('id');

        try {
            DB::transaction(function () use ($client, $taxRates) {
                // 1. Restaurar stock de todos los ítems con lote
                WineStockService::moveForInvoice($this->invoice, 'cancel');

                // 2. Borrar líneas antiguas
                InvoiceItem::withoutObservers(fn () => $this->invoice->items()->delete());

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

                // 4. Actualizar cabecera
                $this->invoice->update([
                    'client_id'            => $client->id,
                    'invoice_date'         => $this->invoice_date,
                    'delivery_note_date'   => $this->delivery_note_date ?: null,
                    'billing_first_name'   => $client->first_name,
                    'billing_last_name'    => $client->last_name,
                    'billing_company_name' => $client->company_name,
                    'billing_email'        => $client->email,
                    'billing_phone'        => $client->phone,
                    'subtotal'             => round($subtotal, 3),
                    'discount_amount'      => round($discountAmount, 3),
                    'tax_base'             => round($taxBase, 3),
                    'tax_amount'           => round($taxAmount, 3),
                    'total_amount'         => round($total, 3),
                    'payment_status'       => $this->payment_status,
                    'payment_type'         => $this->payment_type ?: null,
                    'observations'         => $this->observations ?: null,
                ]);

                // 5. Crear líneas nuevas + mover stock
                InvoiceItem::withoutObservers(function () use ($taxRates) {
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
                            ? WineLot::where('user_id', Auth::id())->lockForUpdate()->find($item['wine_lot_id'])
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
                            'discount_amount'     => $lineDiscount,
                            'tax_id'              => $tax?->id,
                            'tax_name'            => $tax?->name,
                            'tax_rate'            => $taxRate,
                            'subtotal'            => $lineSubtotal,
                            'tax_base'            => $lineBase,
                            'tax_amount'          => $taxAmountLine,
                            'total'               => $lineBase + $taxAmountLine,
                        ]);

                        if ($lot) {
                            WineStockService::moveOnCreate($this->invoice, $createdItem, $lot, $qty);
                        }
                    }
                });
            });

            $this->toastSuccess('Factura actualizada correctamente.');
            return $this->redirect(route('winery.invoices.products.index'), navigate: true);

        } catch (\Exception $e) {
            Log::error('Error al editar factura de vino: ' . $e->getMessage(), [
                'invoice_id' => $this->invoice->id,
                'user_id'    => Auth::id(),
                'exception'  => $e,
            ]);
            $this->toastError($e instanceof \RuntimeException ? $e->getMessage() : 'Error al guardar los cambios.');
        }
    }

    public function render()
    {
        $clients  = Client::where('user_id', Auth::id())->where('active', true)->orderBy('first_name')->orderBy('company_name')->get();
        $wineLots = WineLot::where('user_id', Auth::id())->where('archived', false)
            ->where('available_quantity', '>', 0)
            ->orderByDesc('vintage')->orderBy('name')->get();

        return view('livewire.winery.billing.products.edit', [
            'clients'        => $clients,
            'wineLots'       => $wineLots,
            'availableTaxes' => $this->availableTaxes,
        ])->layout('layouts.app');
    }
}
