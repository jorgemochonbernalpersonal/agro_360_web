<?php

namespace App\Livewire\Winery\Billing\ProductSale\Traits;

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

trait WithQuickInvoiceModal
{
    public bool $quickModal = false;

    public string $quickClientId = '';

    public string $quickClientAddressId = '';

    public string $quickLotId = '';

    public string $quickConceptName = '';

    public string $quickQty = '';

    public string $quickPrice = '';

    public string $quickTaxId = '';

    public string $quickPaymentType = '';

    public array $quickAvailableAddresses = [];

    public float $quickAvailableQty = 0;

    public function openQuickModal(): void
    {
        $this->resetQuickModal();
        $user = Auth::user();
        $taxes = $user->taxes()->orderByPivot('order')->get();
        if ($taxes->isEmpty()) {
            $taxes = Tax::active()->orderBy('rate')->get();
        }
        $default = $user->defaultTax()->first() ?? $taxes->first();
        $this->quickTaxId = (string) ($default->id ?? '');
        $this->quickModal = true;
    }

    public function closeQuickModal(): void
    {
        $this->quickModal = false;
        $this->resetQuickModal();
        $this->resetValidation();
    }

    public function updatedQuickClientId(string $value): void
    {
        if ($value) {
            $client = Client::with('addresses')->find($value);
            $this->quickAvailableAddresses = ($client->addresses ?? collect())->all();
            $primary = $client?->addresses->firstWhere('is_default', true) ?? $client?->addresses->first();
            $this->quickClientAddressId = $primary ? (string) $primary->id : '';
        } else {
            $this->quickAvailableAddresses = [];
            $this->quickClientAddressId = '';
        }
    }

    public function updatedQuickLotId(string $value): void
    {
        if ($value) {
            $lot = ProductLot::where('user_id', Auth::id())->find($value);
            if ($lot) {
                $this->quickConceptName = $lot->name.($lot->vintage ? " ({$lot->vintage})" : '');
                $this->quickPrice = (string) ($lot->price_per_unit ?? 0);
                $this->quickAvailableQty = (float) $lot->available_quantity;
            }
        }
    }

    public function confirmQuick(): void
    {
        $this->validate(
            [
                'quickClientId' => 'required|exists:clients,id',
                'quickClientAddressId' => 'required|exists:client_addresses,id',
                'quickLotId' => 'required|exists:wine_lots,id',
                'quickConceptName' => 'required|string|max:255',
                'quickQty' => 'required|numeric|min:0.001',
                'quickPrice' => 'required|numeric|min:0',
                'quickTaxId' => 'nullable|exists:taxes,id',
                'quickPaymentType' => 'nullable|in:cash,transfer,check,other',
            ],
            [
                'quickClientId.required' => __('Selecciona un cliente.'),
                'quickClientAddressId.required' => __('Selecciona una dirección.'),
                'quickLotId.required' => __('Selecciona un lote de producto.'),
                'quickConceptName.required' => __('El concepto es obligatorio.'),
                'quickQty.required' => __('La cantidad es obligatoria.'),
                'quickPrice.required' => __('El precio es obligatorio.'),
            ]
        );

        $client = Client::where('user_id', Auth::id())->findOrFail($this->quickClientId);
        $taxes = Auth::user()->taxes()->get()->keyBy('id');
        if ($taxes->isEmpty()) {
            $taxes = Tax::active()->get()->keyBy('id');
        }

        $qty = (float) $this->quickQty;
        $price = (float) $this->quickPrice;
        $tax = $this->quickTaxId ? ($taxes[$this->quickTaxId] ?? null) : null;
        $taxRate = $tax ? (float) $tax->rate : 0;
        $subtotal = round($qty * $price, 3);
        $taxAmt = round($subtotal * ($taxRate / 100), 3);
        $total = $subtotal + $taxAmt;

        try {
            DB::transaction(function () use ($client, $tax, $qty, $price, $taxRate, $subtotal, $taxAmt, $total) {
                $settings = InvoicingSetting::getOrCreateForUser(Auth::id());
                $noteCode = $settings->generateAndIncrementDeliveryNoteCode();

                $invoice = Invoice::create([
                    'user_id' => Auth::id(),
                    'client_id' => $client->id,
                    'client_address_id' => $this->quickClientAddressId ?: null,
                    'invoice_type' => 'wine_sale',
                    'delivery_note_code' => $noteCode,
                    'delivery_note_date' => now()->toDateString(),
                    'invoice_date' => now()->toDateString(),
                    'delivery_status' => 'pending',
                    'status' => 'draft',
                    'payment_status' => 'unpaid',
                    'payment_type' => $this->quickPaymentType ?: null,
                    'billing_first_name' => $client->first_name,
                    'billing_last_name' => $client->last_name,
                    'billing_company_name' => $client->company_name,
                    'billing_email' => $client->email,
                    'billing_phone' => $client->phone,
                    'subtotal' => $subtotal,
                    'discount_amount' => 0,
                    'tax_base' => $subtotal,
                    'tax_amount' => $taxAmt,
                    'total_amount' => $total,
                ]);

                $lot = ProductLot::where('user_id', Auth::id())->lockForUpdate()->findOrFail($this->quickLotId);
                $createdItem = InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'wine_lot_id' => $lot->id,
                    'concept_type' => 'wine',
                    'name' => $this->quickConceptName,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'discount_percentage' => 0,
                    'discount_amount' => 0,
                    'tax_id' => $tax?->id,
                    'tax_name' => $tax?->name,
                    'tax_rate' => $taxRate,
                    'subtotal' => $subtotal,
                    'tax_base' => $subtotal,
                    'tax_amount' => $taxAmt,
                    'total' => $total,
                ]);

                ProductStockService::moveOnCreate($invoice, $createdItem, $lot, $qty);
            });

            $this->closeQuickModal();
            $this->toastSuccess(__('Albarán rápido creado correctamente.'));

        } catch (\Exception $e) {
            Log::error('Error al crear albarán rápido: '.$e->getMessage(), ['user_id' => Auth::id()]);
            $this->toastError($e instanceof \RuntimeException ? $e->getMessage() : __('Error al crear el albarán.'));
        }
    }

    private function resetQuickModal(): void
    {
        $this->quickClientId = '';
        $this->quickClientAddressId = '';
        $this->quickLotId = '';
        $this->quickConceptName = '';
        $this->quickQty = '';
        $this->quickPrice = '';
        $this->quickTaxId = '';
        $this->quickPaymentType = '';
        $this->quickAvailableAddresses = [];
        $this->quickAvailableQty = 0;
    }
}
