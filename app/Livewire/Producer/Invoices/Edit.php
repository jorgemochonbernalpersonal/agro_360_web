<?php

namespace App\Livewire\Producer\Invoices;

use App\Livewire\Concerns\WithInvoiceEditRules;
use App\Livewire\Concerns\WithInvoiceFormRules;
use App\Livewire\Concerns\WithProducerInvoiceItems;
use App\Livewire\Concerns\WithProducerInvoiceStatuses;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\HarvestStock;
use App\Models\Invoice;
use App\Services\ProducerInvoiceService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

/**
 * @property-read bool $isLocked
 * @property-read bool $isInvoiced
 */
class Edit extends Component
{
    use WithInvoiceEditRules, WithInvoiceFormRules, WithProducerInvoiceItems, WithProducerInvoiceStatuses, WithToastNotifications;

    public Invoice $invoice;

    public string $client_id = '';

    public string $client_address_id = '';

    public string $invoice_date = '';

    public string $delivery_note_date = '';

    public string $delivery_status = '';

    public string $payment_status = '';

    public string $payment_type = '';

    public string $payment_date = '';

    public string $observations = '';

    public string $observations_invoice = '';

    public string $delivery_note_code = '';

    public string $invoice_number = '';

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

    // ── Mount ─────────────────────────────────────────────────────────────────

    public function mount($invoice): void
    {
        $invoiceKey = $invoice instanceof Invoice ? $invoice->id : $invoice;

        $this->invoice = Invoice::where('user_id', Auth::id())
            ->where('invoice_type', 'producer_sale')
            ->with([
                'items.harvest',
                'items.wineLot',
                'client.addresses.municipality',
                'client.addresses.province',
                'client.addresses.autonomousCommunity',
            ])
            ->findOrFail($invoiceKey);

        $this->loadInvoiceData();
    }

    public function loadInvoiceData(): void
    {
        $this->client_id = (string) $this->invoice->client_id;
        $this->client_address_id = (string) ($this->invoice->client_address_id ?? '');
        $this->invoice_date = $this->invoice->invoice_date
            ? $this->invoice->invoice_date->format('Y-m-d') : '';
        $this->delivery_note_date = $this->invoice->delivery_note_date
            ? $this->invoice->delivery_note_date->format('Y-m-d') : '';
        $this->delivery_status = $this->invoice->delivery_status ?? 'pending';
        $this->payment_status = $this->invoice->payment_status ?? 'unpaid';
        $this->payment_type = $this->invoice->payment_type ?? '';
        $this->payment_date = $this->invoice->payment_date
            ? $this->invoice->payment_date->format('Y-m-d') : '';
        $this->observations = $this->invoice->observations ?? '';
        $this->observations_invoice = $this->invoice->observations_invoice ?? '';
        $this->delivery_note_code = $this->invoice->delivery_note_code ?? '';
        $this->invoice_number = $this->invoice->invoice_number ?? '';

        // Batch-load latest HarvestStock per item to avoid N+1
        $itemHarvestIds = $this->invoice->items->pluck('harvest_id')->filter();
        $itemLatestStocks = HarvestStock::whereIn('harvest_id', $itemHarvestIds)
            ->whereRaw('id = (SELECT MAX(hs2.id) FROM harvest_stocks hs2 WHERE hs2.harvest_id = harvest_stocks.harvest_id)')
            ->get()
            ->keyBy('harvest_id');

        $this->items = $this->invoice->items->map(function ($item) use ($itemLatestStocks) {
            $availableQty = null;

            if ($item->harvest_id) {
                $latestStock = $itemLatestStocks->get($item->harvest_id);
                $currentAvail = $latestStock ? (float) $latestStock->available_qty : 0;
                $availableQty = $currentAvail + (float) $item->quantity;
            } elseif ($item->wine_lot_id && $item->wineLot) {
                $availableQty = (float) $item->wineLot->available_quantity + (float) $item->quantity;
            }

            return [
                'id' => $item->id,
                'harvest_id' => $item->harvest_id,
                'wine_lot_id' => $item->wine_lot_id ? (int) $item->wine_lot_id : null,
                'concept_type' => $item->concept_type ?? 'other',
                'name' => $item->name,
                'description' => $item->description ?? '',
                'sku' => $item->sku ?? '',
                'quantity' => $item->quantity,
                'unit' => $item->unit ?? 'unidades',
                'available_qty' => $availableQty,
                'unit_price' => $item->unit_price,
                'discount_percentage' => $item->discount_percentage,
                'tax_id' => (string) ($item->tax_id ?? $this->defaultTaxId),
            ];
        })->toArray();

        $this->loadData();
        $this->updatedClientId($this->client_id);
    }

    public function loadData(): void
    {
        $this->availableClients = Client::forUser(Auth::id())->active()->get();
        $this->loadTaxes();
        $this->loadHarvests();
        $this->loadLots();
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function update()
    {
        if ($this->isLocked) {
            $this->toastError(__('Esta factura no se puede modificar. Usa "Guardar estados" para cambiar el estado de pago.'));

            return;
        }

        $this->validate();

        $taxRates = $this->availableTaxes->keyBy('id');

        try {
            app(ProducerInvoiceService::class)->updateInvoice(
                $this->invoice,
                [
                    'client_id' => $this->client_id,
                    'client_address_id' => $this->client_address_id ?: null,
                    'invoice_date' => $this->invoice_date ?: null,
                    'delivery_note_date' => $this->delivery_note_date ?: null,
                    'observations' => $this->observations ?: null,
                    'observations_invoice' => $this->observations_invoice ?: null,
                ],
                $this->items,
                $taxRates,
                Auth::id(),
            );

            $this->toastSuccess(__('Factura actualizada correctamente.'));

            return $this->redirect(route('producer.invoices.mixed.index'), navigate: true);

        } catch (\Exception $e) {
            Log::error('Error al actualizar factura de productor: '.$e->getMessage(), [
                'invoice_id' => $this->invoice->id,
                'user_id' => Auth::id(),
                'exception' => $e,
            ]);
            $this->toastError($e instanceof \RuntimeException
                ? $e->getMessage()
                : __('Error al actualizar la factura. Inténtalo de nuevo.'));
        }
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        $campaigns = Campaign::where('viticulturist_id', Auth::id())
            ->orderBy('year', 'desc')
            ->get();

        return view('livewire.producer.invoices.edit', [
            'campaigns' => $campaigns,
            'isLocked' => $this->isLocked,
            'isInvoiced' => $this->isInvoiced,
        ])->layout('layouts.app', ['title' => __('Editar albarán - Agro365')]);
    }

    // ── Validation ────────────────────────────────────────────────────────────

    protected function rules(): array
    {
        if ($this->isLocked) {
            return $this->invoiceLockedRules();
        }

        return array_merge($this->invoiceUnlockedBaseRules('harvest,wine,service,other'), [
            'payment_type' => 'nullable|in:cash,transfer,check,other',
        ]);
    }
}
