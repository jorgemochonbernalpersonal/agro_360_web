<?php

namespace App\Livewire\Producer\Invoices;

use App\Livewire\Concerns\WithInvoiceFormRules;
use App\Livewire\Concerns\WithProducerInvoiceItems;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicingSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Create extends Component
{
    use WithInvoiceFormRules, WithProducerInvoiceItems, WithToastNotifications;

    public string $client_id = '';

    public string $client_address_id = '';

    public string $invoice_date = '';

    public string $delivery_note_date = '';

    public string $payment_type = '';

    public string $observations = '';

    public string $observations_invoice = '';

    public string $delivery_note_code = '';

    public string $delivery_note_code_auto = '';

    public bool $delivery_note_code_modified = false;

    public function mount(): void
    {
        $this->invoice_date = now()->toDateString();
        $this->delivery_note_date = now()->toDateString();

        $this->loadTaxes();

        $settings = InvoicingSetting::getOrCreateForUser(Auth::id());
        $this->delivery_note_code_auto = $settings->getDeliveryNotePreview();
        $this->delivery_note_code = $this->delivery_note_code_auto;

        $this->availableClients = Client::forUser(Auth::id())->active()->get();
        $this->loadHarvests();
        $this->loadLots();
    }

    public function updatedDeliveryNoteCode(string $value): void
    {
        $this->delivery_note_code_modified = ($value !== $this->delivery_note_code_auto);
    }

    public function save()
    {
        $this->validate();

        $taxRates = $this->availableTaxes->keyBy('id');
        $noteCode = null;

        try {
            DB::transaction(function () use ($taxRates, &$noteCode) {
                $noteCode = $this->invoiceService->generateDeliveryNoteCode(
                    Auth::id(),
                    $this->delivery_note_code_modified,
                    $this->delivery_note_code,
                );

                $totals = $this->invoiceService->calculateVatTotals($this->items, $taxRates);

                $invoice = Invoice::create([
                    'user_id' => Auth::id(),
                    'client_id' => $this->client_id,
                    'client_address_id' => $this->client_address_id ?: null,
                    'invoice_type' => 'producer_sale',
                    'delivery_note_code' => $noteCode,
                    'delivery_note_date' => $this->delivery_note_date ?: now(),
                    'order_date' => $this->invoice_date,
                    'invoice_date' => $this->invoice_date,
                    'invoice_number' => null,
                    'status' => 'draft',
                    'delivery_status' => 'pending',
                    'payment_status' => 'unpaid',
                    'payment_type' => $this->payment_type ?: null,
                    'subtotal' => $totals['gross_subtotal'],
                    'discount_amount' => $totals['discount_amount'],
                    'tax_base' => $totals['tax_base'],
                    'tax_rate' => $totals['effective_tax_rate'],
                    'tax_amount' => $totals['tax_amount'],
                    'total_amount' => $totals['total'],
                    'observations' => $this->observations ?: null,
                    'observations_invoice' => $this->observations_invoice ?: null,
                ]);

                $stockService = app(\App\Services\UnifiedStockService::class);

                InvoiceItem::withoutEvents(function () use ($invoice, $taxRates, $stockService) {
                    foreach ($this->items as $item) {
                        $tax = ($item['tax_id'] ?? null) ? $taxRates[$item['tax_id']] ?? null : null;
                        $line = $this->invoiceService->calculateVatLine($item, $tax);
                        $qty = $line['quantity'];

                        $createdItem = $invoice->items()->create([
                            'harvest_id' => $item['harvest_id'] ?? null,
                            'wine_lot_id' => $item['wine_lot_id'] ?? null,
                            'concept_type' => $item['concept_type'] ?? 'other',
                            'name' => $item['name'],
                            'description' => $item['description'] ?: null,
                            'sku' => $item['sku'] ?: null,
                            'quantity' => $qty,
                            'unit' => $item['unit'] ?? 'unidades',
                            'unit_price' => $line['unit_price'],
                            'discount_percentage' => $line['discount_percentage'],
                            'discount_amount' => $line['discount_amount'],
                            'tax_id' => $tax?->id,
                            'tax_name' => $tax?->name,
                            'tax_rate' => $line['tax_rate'],
                            'tax_base' => $line['tax_base'],
                            'tax_amount' => $line['tax_amount'],
                            'subtotal' => $line['subtotal'],
                            'total' => $line['total'],
                        ]);

                        $stockService->reserveOrSell($invoice, $createdItem, Auth::id(), $qty);
                    }
                });
            });

            $this->toastSuccess("Albarán {$noteCode} creado. Emítelo para generar el número de factura.");

            return $this->redirect(route('producer.invoices.mixed.index'), navigate: true);

        } catch (\Exception $e) {
            Log::error('Error al crear factura de productor: '.$e->getMessage(), [
                'user_id' => Auth::id(),
                'exception' => $e,
            ]);
            $this->toastError($e instanceof \RuntimeException
                ? $e->getMessage()
                : __('Error al crear la factura. Inténtalo de nuevo.'));
        }
    }

    public function render()
    {
        $campaigns = Campaign::where('viticulturist_id', Auth::id())
            ->orderBy('year', 'desc')
            ->get();

        return view('livewire.producer.invoices.create', [
            'campaigns' => $campaigns,
        ])->layout('layouts.app', ['title' => __('Crear albarán - Agro365')]);
    }

    protected function rules(): array
    {
        return $this->invoiceCreateRules('harvest,wine,service,other');
    }

    protected function messages(): array
    {
        return [
            'client_address_id.required' => __('Debes seleccionar un cliente con dirección. Este cliente no tiene direcciones configuradas.'),
            'items.required' => __('Debes añadir al menos un ítem a la factura.'),
            'items.min' => __('Debes añadir al menos un ítem a la factura.'),
        ];
    }
}
