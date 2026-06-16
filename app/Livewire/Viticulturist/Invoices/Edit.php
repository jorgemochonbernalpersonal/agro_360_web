<?php

namespace App\Livewire\Viticulturist\Invoices;

use App\Livewire\Concerns\WithInvoiceFormRules;
use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\Harvest;
use App\Models\Invoice;
use App\Models\Tax;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Edit extends Component
{
    use WithInvoiceFormRules, WithRoleAwareRedirect, WithToastNotifications;

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

    public $delivery_note_code = ''; // Código de albarán (solo lectura)

    public $invoice_number = '';     // Número de factura (solo lectura si ya está facturada)

    // Modal de confirmación de facturación
    public $showInvoiceModal = false;

    public $invoice_date_modal = '';

    // Modales de estados (entrega + cobro)
    public bool $showDeliveryModal = false;

    public string $pendingDeliveryStatus = '';

    public bool $showPaymentDateModal = false;

    public $availableClients = [];

    public $availableAddresses = [];

    public $availableTaxes = [];

    public $availableHarvests = [];

    public $selectedHarvestId = '';

    public $selectedCampaign = '';

    protected InvoiceService $invoiceService;

    public function boot(InvoiceService $invoiceService): void
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * Determina si la factura ya está facturada (no se puede modificar el código)
     */
    public function getIsInvoicedProperty(): bool
    {
        return $this->invoice->status === 'sent' || $this->invoice->status === 'paid';
    }

    /**
     * Determina si la factura está bloqueada (entregada o cancelada)
     * Cuando está bloqueada, solo se puede cambiar el estado de pago
     */
    public function getIsLockedProperty(): bool
    {
        return $this->invoice->delivery_status === 'delivered'
            || $this->invoice->delivery_status === 'cancelled'
            || $this->invoice->status === 'cancelled';
    }

    public function mount($invoice)
    {
        // Si es un modelo, usarlo directamente; si es un ID, buscarlo
        if ($invoice instanceof Invoice) {
            // 404 (no 403) para no revelar la existencia de facturas ajenas — fijado por test
            abort_unless(Auth::user()->can('update', $invoice), 404);
            $this->invoice = $invoice;
        } else {
            $user = Auth::user();
            $this->invoice = Invoice::forUser($user->id)
                ->with([
                    'items',
                    'client.addresses.municipality',
                    'client.addresses.province',
                    'client.addresses.autonomousCommunity',
                ])
                ->findOrFail($invoice);
        }

        $this->loadInvoiceData();
    }

    public function loadInvoiceData()
    {
        $this->client_id = $this->invoice->client_id;
        $this->client_address_id = $this->invoice->client_address_id ?? '';
        $this->invoice_date = $this->invoice->invoice_date
            ? $this->invoice->invoice_date->format('Y-m-d') : '';
        $this->delivery_note_date = $this->invoice->delivery_note_date
            ? $this->invoice->delivery_note_date->format('Y-m-d') : '';
        $this->delivery_status = $this->invoice->delivery_status;
        $this->payment_status = $this->invoice->payment_status;
        $this->payment_type = $this->invoice->payment_type ?? '';
        $this->payment_date = $this->invoice->payment_date
            ? $this->invoice->payment_date->format('Y-m-d') : '';
        $this->observations = $this->invoice->observations ?? '';
        $this->observations_invoice = $this->invoice->observations_invoice ?? '';
        $this->delivery_note_code = $this->invoice->delivery_note_code ?? '';
        $this->invoice_number = $this->invoice->invoice_number ?? '';

        // Batch-load latest HarvestStock per harvest item (evita N+1)
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

    /**
     * Cancelar edición y resetear todo a los valores originales
     */
    public function cancel()
    {
        // Recargar datos originales de la factura
        $this->invoice->refresh();
        $this->loadInvoiceData();
        $this->toastSuccess(__('Cambios cancelados. Se restauraron los valores originales.'));
    }

    public function loadData()
    {
        $user = Auth::user();
        $this->availableClients = Client::forUser($user->id)->active()->get();

        // Cargar solo los impuestos habilitados por el usuario
        $this->availableTaxes = $user->taxes()->orderByPivot('order')->get();
        if ($this->availableTaxes->isEmpty()) {
            $this->availableTaxes = Tax::active()->orderBy('rate')->get();
        }

        $this->loadHarvests();
    }

    public function loadHarvests()
    {
        $user = Auth::user();

        $harvests = Harvest::whereHas('activity', function ($q) use ($user) {
            $q->where('viticulturist_id', $user->id);
        })
            ->with(['activity.plot', 'plotPlanting.grapeVariety', 'activity.campaign', 'container'])
            ->when($this->selectedCampaign, fn ($q) => $q->whereHas('activity', fn ($q) => $q->where('campaign_id', $this->selectedCampaign))
            )
            ->where('total_weight', '>', 0)
            ->orderBy('harvest_start_date', 'desc')
            ->get();

        // Batch-load latest HarvestStock por cosecha (evita N+1)
        $harvestIds = $harvests->pluck('id');
        $latestStocks = \App\Models\HarvestStock::whereIn('harvest_id', $harvestIds)
            ->whereRaw('id = (SELECT MAX(hs2.id) FROM harvest_stocks hs2 WHERE hs2.harvest_id = harvest_stocks.harvest_id)')
            ->get()
            ->keyBy('harvest_id');

        $this->availableHarvests = $harvests
            ->map(function ($harvest) use ($latestStocks) {
                $latestStock = $latestStocks->get($harvest->id);
                $harvest->available_qty_computed = $latestStock
                    ? (float) $latestStock->available_qty
                    : (float) $harvest->total_weight;

                return $harvest;
            })
            ->filter(fn ($h) => $h->available_qty_computed > 0)
            ->values();
    }

    public function updatedSelectedCampaign()
    {
        $this->loadHarvests();
        $this->selectedHarvestId = '';
    }

    public function addHarvestToInvoice()
    {
        if (! $this->selectedHarvestId) {
            return;
        }

        $harvest = Harvest::with(['activity.plot', 'plotPlanting.grapeVariety'])
            ->find($this->selectedHarvestId);

        if (! $harvest) {
            $this->toastError(__('Cosecha no encontrada.'));

            return;
        }

        // Verificar que la cosecha no esté ya en los items locales (esta factura)
        foreach ($this->items as $item) {
            if (isset($item['harvest_id']) && $item['harvest_id'] == $harvest->id) {
                $this->toastError(__('Esta cosecha ya está en la factura actual.'));

                return;
            }
        }

        // Stock disponible real
        $latestStock = \App\Models\HarvestStock::where('harvest_id', $harvest->id)->latest('id')->first();
        $availableQty = $latestStock ? (float) $latestStock->available_qty : (float) $harvest->total_weight;

        if ($availableQty <= 0) {
            $this->toastError(__('Esta cosecha no tiene stock disponible para facturar.'));

            return;
        }

        $user = Auth::user();
        $defaultTax = $user->defaultTax()->first()
            ?? $this->availableTaxes->where('code', 'IVA')->where('rate', 21)->first()
            ?? $this->availableTaxes->first();

        $grapeVarietyName = $harvest->plotPlanting->grapeVariety->name ?? 'Uva';
        $plotName = $harvest->activity->plot->name ?? '';
        $itemName = $grapeVarietyName.($plotName ? ' - '.$plotName : '');

        $this->items[] = [
            'id' => null,
            'harvest_id' => $harvest->id,
            'name' => $itemName,
            'description' => __('Cosecha del ').$harvest->harvest_start_date->format('d/m/Y').
                                     ($harvest->plotPlanting->grapeVariety ? ' - Variedad: '.$harvest->plotPlanting->grapeVariety->name : ''),
            'sku' => __('HARV-').$harvest->id,
            'quantity' => $availableQty,
            'unit' => 'kg',
            'available_qty' => $availableQty,
            'total_weight' => (float) $harvest->total_weight,
            'unit_price' => $harvest->price_per_kg ?? 0,
            'discount_percentage' => 0,
            'tax_id' => $defaultTax ? $defaultTax->id : null,
            'concept_type' => 'harvest',
        ];

        $this->selectedHarvestId = '';
        $this->toastSuccess(__('Cosecha añadida a la factura.'));
    }

    public function updatedClientId($value)
    {
        if ($value) {
            $client = Client::with([
                'addresses.municipality',
                'addresses.province',
                'addresses.autonomousCommunity',
            ])->find($value);

            if ($client) {
                // Cargar automáticamente la primera dirección del cliente
                $primaryAddress = $client->addresses->first();

                if ($primaryAddress) {
                    $this->client_address_id = $primaryAddress->id;
                } else {
                    // Si no tiene dirección, mostrar error
                    $this->client_address_id = '';
                    $this->addError('client_id', __('Este cliente no tiene ninguna dirección configurada. Por favor, añade una dirección al cliente primero.'));
                }

                $this->availableAddresses = $client->addresses;
            } else {
                $this->availableAddresses = collect();
            }
        } else {
            $this->availableAddresses = collect();
        }
    }

    public function openInvoiceModal()
    {
        if ($this->invoice->status !== 'draft') {
            $this->toastError(__('Solo se puede facturar un albarán en estado borrador.'));

            return;
        }

        $this->invoice_date_modal = $this->invoice_date ?: now()->format('Y-m-d');
        $this->showInvoiceModal = true;
    }

    public function closeInvoiceModal()
    {
        $this->showInvoiceModal = false;
        $this->invoice_date_modal = '';
    }

    // ── Guardar estados (entrega + cobro) ─────────────────────────────────────

    public function saveStatuses(): void
    {
        if ($this->invoice->status === 'cancelled') {
            $this->toastError(__('No se puede modificar una factura cancelada.'));

            return;
        }

        // Si cobro = pagado y no hay fecha → pedir fecha de pago
        if ($this->payment_status === 'paid' && ! $this->payment_date) {
            $this->showPaymentDateModal = true;

            return;
        }

        // Si la entrega cambia a un estado que mueve stock → confirmar
        $originalDelivery = $this->invoice->delivery_status;
        if ($this->delivery_status !== $originalDelivery
            && in_array($this->delivery_status, ['delivered', 'cancelled'])) {
            $this->pendingDeliveryStatus = $this->delivery_status;
            $this->showDeliveryModal = true;

            return;
        }

        $this->persistStatuses();
    }

    // ── Modal: fecha de cobro ─────────────────────────────────────────────────

    public function confirmPaymentDate(): void
    {
        $this->validate(
            ['payment_date' => 'required|date'],
            ['payment_date.required' => __('La fecha de cobro es obligatoria.')]
        );

        $this->showPaymentDateModal = false;

        // Si además hay que confirmar entrega, encadenar ese modal
        $originalDelivery = $this->invoice->delivery_status;
        if ($this->delivery_status !== $originalDelivery
            && in_array($this->delivery_status, ['delivered', 'cancelled'])) {
            $this->pendingDeliveryStatus = $this->delivery_status;
            $this->showDeliveryModal = true;

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
        $this->delivery_status = $this->invoice->delivery_status;
        $this->showDeliveryModal = false;
        $this->pendingDeliveryStatus = '';
    }

    public function confirmDeliveryStatus(): void
    {
        $newStatus = $this->pendingDeliveryStatus;

        if (! in_array($newStatus, ['delivered', 'cancelled'])) {
            $this->closeDeliveryModal();

            return;
        }

        try {
            // delivery_status is purely informational — stock is managed by invoice.status via
            // InvoiceObserver (draft→sent moves reserved→sold, cancelled releases all stock).
            // InvoiceObserver::handleDeliveryStatusChange() deliberately does NOT move stock.
            $this->invoice->update(['delivery_status' => $newStatus]);

            $this->delivery_status = $newStatus;
            $this->showDeliveryModal = false;
            $this->pendingDeliveryStatus = '';

            $this->persistPaymentStatus();

            $label = $newStatus === 'delivered' ? 'entregada' : 'cancelada';
            $this->toastSuccess("Estados guardados. Entrega: {$label}.");

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error al actualizar estado de entrega: '.$e->getMessage(), [
                'invoice_id' => $this->invoice->id,
                'user_id' => Auth::id(),
            ]);
            $this->toastError(__('Error al actualizar el estado de entrega.'));
            $this->closeDeliveryModal();
        }
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

    public function addItem()
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

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        // Los cálculos se actualizarán automáticamente con las propiedades computadas
    }

    // El subtotal del viticultor es la base NETA (tras descuentos), por convención.
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
                // Observer routes: draft → ContainerStockService::unreserveStock()
                //                  sent  → ContainerStockService::releaseFromInvoice()
                $this->invoice->load('items.harvest');
                $this->invoice->items->each(fn ($item) => $item->delete());

                // Recreate items — pre-load taxes in one query to avoid N+1.
                // InvoiceItemObserver::created() fires per item and routes:
                //   draft → ContainerStockService::reserveStock()
                //   sent  → ContainerStockService::directSale()
                $taxIds = collect($this->items)->pluck('tax_id')->filter()->unique()->values()->all();
                $taxRates = empty($taxIds) ? collect() : Tax::whereIn('id', $taxIds)->get()->keyBy('id');
                $totals = $this->invoiceService->calculateVatTotals($this->items, $taxRates);

                foreach ($this->items as $itemData) {
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
                    // delivery_status y payment_status se gestionan vía saveStatuses()
                    'subtotal' => $totals['tax_base'],
                    'discount_amount' => $totals['discount_amount'],
                    'tax_base' => $totals['tax_base'],
                    'tax_rate' => $totals['effective_tax_rate'],
                    'tax_amount' => $totals['tax_amount'],
                    'total_amount' => $totals['total'],
                    'observations' => $this->observations ?: null,
                    'observations_invoice' => $this->observations_invoice ?: null,
                ]);

                $this->invoice->logAction(
                    'updated',
                    'Factura actualizada',
                    [
                        'client_id' => ['old' => $this->invoice->getOriginal('client_id'), 'new' => $this->client_id],
                        'total_amount' => ['old' => $this->invoice->getOriginal('total_amount'), 'new' => $totals['total']],
                        'items_count' => count($this->items),
                    ]
                );
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
        $campaigns = Campaign::where('viticulturist_id', $user->id)
            ->orderBy('year', 'desc')
            ->get();

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

    /**
     * Totales VAT en vivo para la UI. Misma fuente de verdad que update()
     * (InvoiceService::calculateVatTotals), de modo que el total mostrado no puede
     * diverger del persistido. Las tasas se cargan de BD (no de $availableTaxes, que
     * puede llegar como array plano tras la serialización de Livewire).
     */
    private function vatTotals(): array
    {
        $taxIds = collect($this->items)->pluck('tax_id')->filter()->unique()->values()->all();
        $taxRates = empty($taxIds) ? collect() : Tax::whereIn('id', $taxIds)->get()->keyBy('id');

        return $this->invoiceService->calculateVatTotals($this->items, $taxRates);
    }

    private function persistStatuses(): void
    {
        $this->invoice->update([
            'delivery_status' => $this->delivery_status,
            'payment_status' => $this->payment_status,
            'payment_type' => $this->payment_type ?: null,
            'payment_date' => $this->payment_status === 'paid' ? ($this->payment_date ?: null) : null,
        ]);
        $this->invoice->refresh();
        $this->toastSuccess(__('Estados actualizados correctamente.'));
    }

    private function persistPaymentStatus(): void
    {
        $this->invoice->update([
            'payment_status' => $this->payment_status,
            'payment_type' => $this->payment_type ?: null,
            'payment_date' => $this->payment_status === 'paid' ? ($this->payment_date ?: null) : null,
        ]);
        $this->invoice->refresh();
    }
}
