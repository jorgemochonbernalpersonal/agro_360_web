<?php

namespace App\Livewire\Producer\Invoices;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\Harvest;
use App\Models\HarvestStock;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicingSetting;
use App\Models\ProductLot;
use App\Models\Tax;
use App\Services\ContainerStockService;
use App\Services\InvoiceService;
use App\Services\ProductStockService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Edit extends Component
{
    use WithToastNotifications;

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

    public array $items = [];

    // Invoice emission modal
    public bool $showInvoiceModal = false;

    public string $invoice_date_modal = '';

    // Delivery status modal
    public bool $showDeliveryModal = false;

    public string $pendingDeliveryStatus = '';

    // Payment date modal
    public bool $showPaymentDateModal = false;

    public string $selectedHarvestId = '';

    public string $selectedCampaign = '';

    public string $selectedLotId = '';

    public $availableClients = [];

    public $availableAddresses = [];

    public $availableTaxes = [];

    public $availableHarvests = [];

    public $availableLots = [];

    protected InvoiceService $invoiceService;

    protected string $defaultTaxId = '';

    public function boot(InvoiceService $invoiceService): void
    {
        $this->invoiceService = $invoiceService;
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

    // ── Mount ─────────────────────────────────────────────────────────────────

    public function mount($invoice): void
    {
        $user = Auth::user();
        $invoiceKey = $invoice instanceof Invoice ? $invoice->id : $invoice;

        $this->invoice = Invoice::where('user_id', $user->id)
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

        // Batch-load latest HarvestStock per harvest item (avoid N+1)
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
                // Add back the currently-reserved quantity so it shows as available to edit
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
        $user = Auth::user();

        $this->availableClients = Client::forUser($user->id)->active()->get();

        $this->availableTaxes = $user->taxes()->orderByPivot('order')->get();
        if ($this->availableTaxes->isEmpty()) {
            $this->availableTaxes = Tax::active()->orderBy('rate')->get();
        }

        $defaultTax = $user->defaultTax()->first() ?? $this->availableTaxes->first();
        $this->defaultTaxId = (string) ($defaultTax?->id ?? '');

        $this->loadHarvests();
        $this->loadLots();
    }

    public function loadHarvests(): void
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

        $harvestIds = $harvests->pluck('id');
        $latestStocks = HarvestStock::whereIn('harvest_id', $harvestIds)
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

    public function loadLots(): void
    {
        $existingLotIds = collect($this->items)->pluck('wine_lot_id')->filter()->values()->all();

        $this->availableLots = ProductLot::where('user_id', Auth::id())
            ->where('archived', false)
            ->where(function ($q) use ($existingLotIds) {
                $q->where('available_quantity', '>', 0)
                    ->orWhereIn('id', $existingLotIds);
            })
            ->orderBy('name')
            ->get();
    }

    public function updatedSelectedCampaign(): void
    {
        $this->loadHarvests();
        $this->selectedHarvestId = '';
    }

    public function updatedClientId(string $value): void
    {
        if ($value) {
            $client = Client::with([
                'addresses.municipality',
                'addresses.province',
                'addresses.autonomousCommunity',
            ])->find($value);

            if ($client) {
                $primary = $client->addresses->firstWhere('is_default', true)
                    ?? $client->addresses->first();

                if ($primary) {
                    $this->client_address_id = (string) $primary->id;
                } else {
                    $this->client_address_id = '';
                    $this->addError('client_id', __('Este cliente no tiene ninguna dirección configurada. Por favor, añade una dirección al cliente primero.'));
                }

                $this->availableAddresses = $client->addresses;
            } else {
                $this->availableAddresses = collect();
                $this->client_address_id = '';
            }
        } else {
            $this->availableAddresses = collect();
        }
    }

    // ── Add harvest item ──────────────────────────────────────────────────────

    public function addHarvestToInvoice(): void
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

        foreach ($this->items as $item) {
            if (isset($item['harvest_id']) && (int) $item['harvest_id'] === $harvest->id) {
                $this->toastError(__('Esta cosecha ya está en la factura actual.'));

                return;
            }
        }

        $latestStock = HarvestStock::where('harvest_id', $harvest->id)->latest('id')->first();
        $availableQty = $latestStock
            ? (float) $latestStock->available_qty
            : (float) $harvest->total_weight;

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
            'wine_lot_id' => null,
            'concept_type' => 'harvest',
            'name' => $itemName,
            'description' => __('Cosecha del ').$harvest->harvest_start_date->format('d/m/Y').
                                     ($harvest->plotPlanting->grapeVariety ? ' - Variedad: '.$harvest->plotPlanting->grapeVariety->name : ''),
            'sku' => __('HARV-').$harvest->id,
            'quantity' => $availableQty,
            'unit' => 'kg',
            'available_qty' => $availableQty,
            'unit_price' => $harvest->price_per_kg ?? 0,
            'discount_percentage' => 0,
            'tax_id' => $defaultTax?->id,
        ];

        $this->selectedHarvestId = '';
        $this->toastSuccess(__('Cosecha añadida a la factura.'));
    }

    // ── Add wine lot item ─────────────────────────────────────────────────────

    public function addWineToInvoice(): void
    {
        if (! $this->selectedLotId) {
            return;
        }

        $lot = ProductLot::where('user_id', Auth::id())->find($this->selectedLotId);

        if (! $lot) {
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
            'id' => null,
            'harvest_id' => null,
            'wine_lot_id' => $lot->id,
            'concept_type' => 'wine',
            'name' => $lot->name.($lot->vintage ? " ({$lot->vintage})" : ''),
            'description' => '',
            'sku' => $lot->sku ?? '',
            'quantity' => 1,
            'unit' => 'botella',
            'available_qty' => (float) $lot->available_quantity,
            'unit_price' => $lot->price_per_unit ? (float) $lot->price_per_unit : 0,
            'discount_percentage' => 0,
            'tax_id' => $this->defaultTaxId ?: null,
        ];

        $this->selectedLotId = '';
        $this->toastSuccess(__('Producto añadido.'));
    }

    // ── Add manual item ───────────────────────────────────────────────────────

    public function addItem(): void
    {
        $this->items[] = [
            'id' => null,
            'harvest_id' => null,
            'wine_lot_id' => null,
            'concept_type' => 'other',
            'name' => '',
            'description' => '',
            'sku' => '',
            'quantity' => 1,
            'unit' => 'unidades',
            'available_qty' => null,
            'unit_price' => 0,
            'discount_percentage' => 0,
            'tax_id' => $this->defaultTaxId ?: null,
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    // ── Computed totals ───────────────────────────────────────────────────────

    public function getSubtotalProperty(): float
    {
        $total = 0;
        foreach ($this->items as $item) {
            $sub = (float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0);
            $discount = $sub * ((float) ($item['discount_percentage'] ?? 0) / 100);
            $total += $sub - $discount;
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
            $sub = (float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0);
            $discAmt = $sub * ((float) ($item['discount_percentage'] ?? 0) / 100);
            $base = $sub - $discAmt;
            $rate = ($item['tax_id'] ?? null)
                ? (float) ($taxRates[$item['tax_id']]?->rate ?? 0)
                : 0;
            $total += $base * ($rate / 100);
        }

        return round($total, 3);
    }

    public function getTotalAmountProperty(): float
    {
        return round($this->subtotal + $this->taxAmount, 3);
    }

    // ── Status management ─────────────────────────────────────────────────────

    public function saveStatuses(): void
    {
        if ($this->invoice->status === 'cancelled') {
            $this->toastError(__('No se puede modificar una factura cancelada.'));

            return;
        }

        // If payment = paid and no date → request payment date
        if ($this->payment_status === 'paid' && ! $this->payment_date) {
            $this->showPaymentDateModal = true;

            return;
        }

        // If delivery changes to a stock-moving status → confirm
        $originalDelivery = $this->invoice->delivery_status;
        if ($this->delivery_status !== $originalDelivery
            && in_array($this->delivery_status, ['delivered', 'cancelled'])) {
            $this->pendingDeliveryStatus = $this->delivery_status;
            $this->showDeliveryModal = true;

            return;
        }

        $this->persistStatuses();
    }

    // ── Payment date modal ────────────────────────────────────────────────────

    public function confirmPaymentDate(): void
    {
        $this->validate(
            ['payment_date' => 'required|date'],
            ['payment_date.required' => __('La fecha de cobro es obligatoria.')]
        );

        $this->showPaymentDateModal = false;

        // Chain delivery modal if also needed
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

    // ── Delivery confirmation modal ───────────────────────────────────────────

    public function closeDeliveryModal(): void
    {
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
            DB::transaction(function () use ($newStatus) {
                $this->invoice->load('items.harvest', 'items.wineLot');

                $containerStockService = app(ContainerStockService::class);

                if (! $this->invoice->corrective) {
                    if ($newStatus === 'delivered') {
                        // Wine items: reserved → sold
                        ProductStockService::moveForInvoice($this->invoice, 'deliver');

                        // Harvest items: if draft (still reserved), confirm sale; if sent, observer already handled it
                        if ($this->invoice->status === 'draft') {
                            foreach ($this->invoice->items as $item) {
                                if ($item->concept_type === 'harvest' && $item->harvest_id && $item->harvest) {
                                    $containerStockService->confirmSale(
                                        $item->harvest,
                                        $item,
                                        $this->invoice->invoice_number ?? ''
                                    );
                                }
                            }
                        }
                        // If status = 'sent', InvoiceObserver::convertReservationsToSales() already moved harvest stock
                    } else {
                        // cancelled
                        ProductStockService::moveForInvoice($this->invoice, 'cancel');

                        foreach ($this->invoice->items as $item) {
                            if ($item->concept_type === 'harvest' && $item->harvest_id && $item->harvest) {
                                $containerStockService->releaseFromInvoice(
                                    $item->harvest,
                                    $item,
                                    $this->invoice->status
                                );
                            }
                        }
                    }
                }

                $this->invoice->update(['delivery_status' => $newStatus]);
            });

            $this->delivery_status = $newStatus;
            $this->showDeliveryModal = false;
            $this->pendingDeliveryStatus = '';

            $this->persistPaymentStatus();

            $label = $newStatus === 'delivered' ? 'entregada' : 'cancelada';
            $this->toastSuccess("Estados guardados. Entrega: {$label}.");

        } catch (\Exception $e) {
            Log::error('Error al actualizar estado de entrega (producer): '.$e->getMessage(), [
                'invoice_id' => $this->invoice->id,
                'user_id' => Auth::id(),
            ]);
            $this->toastError($e instanceof \RuntimeException ? $e->getMessage() : __('Error al actualizar el estado de entrega.'));
            $this->closeDeliveryModal();
        }
    }

    // ── Invoice emission modal ────────────────────────────────────────────────

    public function openInvoiceModal(): void
    {
        if ($this->invoice->status !== 'draft') {
            $this->toastError(__('Solo se puede facturar un albarán en estado borrador.'));

            return;
        }

        $this->invoice_date_modal = $this->invoice_date ?: now()->toDateString();
        $this->showInvoiceModal = true;
    }

    public function closeInvoiceModal(): void
    {
        $this->showInvoiceModal = false;
        $this->invoice_date_modal = '';
    }

    public function markAsSent(): void
    {
        $this->validate(
            ['invoice_date_modal' => 'required|date'],
            ['invoice_date_modal.required' => __('Debes indicar la fecha de la factura.')]
        );

        try {
            DB::transaction(function () {
                $settings = InvoicingSetting::getOrCreateForUser(Auth::id());

                // InvoiceObserver fires on status change and calls convertReservationsToSales()
                // which moves harvest items from reserved → sold automatically.
                $this->invoice->update([
                    'status' => 'sent',
                    'invoice_date' => $this->invoice_date_modal,
                    'invoice_number' => $settings->generateAndIncrementInvoiceCode(),
                ]);
            });

            $this->toastSuccess(__('Factura emitida correctamente.'));
            $this->closeInvoiceModal();
            $this->redirect(route('producer.invoices.mixed.index'), navigate: true);

        } catch (\Exception $e) {
            Log::error('Error al emitir factura de productor: '.$e->getMessage(), [
                'invoice_id' => $this->invoice->id,
                'user_id' => Auth::id(),
            ]);
            $this->toastError($e instanceof \RuntimeException ? $e->getMessage() : __('Error al facturar. Inténtalo de nuevo.'));
        }
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
            DB::transaction(function () use ($taxRates) {
                // 1. Load current items with their relations for stock reversal
                $this->invoice->load('items.harvest', 'items.wineLot');

                $containerStockService = app(ContainerStockService::class);

                // 2. Cancel wine stock for all wine items
                ProductStockService::moveForInvoice($this->invoice, 'cancel');

                // 3. Cancel harvest stock for all harvest items
                foreach ($this->invoice->items as $item) {
                    if ($item->concept_type === 'harvest' && $item->harvest_id && $item->harvest) {
                        if ($this->invoice->status === 'draft') {
                            $containerStockService->unreserveStock($item->harvest, $item);
                        } else {
                            $containerStockService->releaseFromInvoice(
                                $item->harvest,
                                $item,
                                $this->invoice->status
                            );
                        }
                    }
                }

                // 4. Bulk delete old items (bypass observer — stock already reversed above)
                InvoiceItem::withoutEvents(fn () => $this->invoice->items()->delete());

                // 5. Recalculate totals
                $totals = $this->invoiceService->calculateVatTotals($this->items, $taxRates);

                // 6. Update invoice header — NOT delivery_status / payment_status
                $this->invoice->update([
                    'client_id' => $this->client_id,
                    'client_address_id' => $this->client_address_id ?: null,
                    'invoice_date' => $this->invoice_date ?: null,
                    'delivery_note_date' => $this->delivery_note_date ?: null,
                    'subtotal' => $totals['gross_subtotal'],
                    'discount_amount' => $totals['discount_amount'],
                    'tax_base' => $totals['tax_base'],
                    'tax_rate' => $totals['effective_tax_rate'],
                    'tax_amount' => $totals['tax_amount'],
                    'total_amount' => $totals['total'],
                    'observations' => $this->observations ?: null,
                    'observations_invoice' => $this->observations_invoice ?: null,
                ]);

                // 7. Recreate items and re-reserve stock (bypass observer, manual stock calls)
                InvoiceItem::withoutEvents(function () use ($taxRates, $containerStockService) {
                    foreach ($this->items as $item) {
                        $qty = (float) $item['quantity'];
                        $unitPrice = (float) $item['unit_price'];
                        $discPct = (float) ($item['discount_percentage'] ?? 0);
                        $lineSubtotal = round($qty * $unitPrice, 3);
                        $lineDiscount = round($lineSubtotal * ($discPct / 100), 3);
                        $lineBase = round($lineSubtotal - $lineDiscount, 3);
                        $tax = ($item['tax_id'] ?? null) ? $taxRates[$item['tax_id']] ?? null : null;
                        $taxRate = $tax ? (float) $tax->rate : 0;
                        $taxAmountLine = round($lineBase * ($taxRate / 100), 3);

                        $createdItem = $this->invoice->items()->create([
                            'harvest_id' => $item['harvest_id'] ?? null,
                            'wine_lot_id' => $item['wine_lot_id'] ?? null,
                            'concept_type' => $item['concept_type'] ?? 'other',
                            'name' => $item['name'],
                            'description' => $item['description'] ?: null,
                            'sku' => $item['sku'] ?: null,
                            'quantity' => $qty,
                            'unit' => $item['unit'] ?? 'unidades',
                            'unit_price' => $unitPrice,
                            'discount_percentage' => $discPct,
                            'discount_amount' => $lineDiscount,
                            'tax_id' => $tax?->id,
                            'tax_name' => $tax?->name,
                            'tax_rate' => $taxRate,
                            'tax_base' => $lineBase,
                            'tax_amount' => $taxAmountLine,
                            'subtotal' => $lineSubtotal,
                            'total' => $lineBase + $taxAmountLine,
                        ]);

                        // Manual stock movement
                        if (! empty($item['harvest_id'])) {
                            // Ownership guard: la cosecha debe pertenecer al viticultor autenticado
                            // (el harvest_id viene del estado del cliente y no es de fiar).
                            $harvest = Harvest::whereHas('activity', fn ($q) => $q->where('viticulturist_id', Auth::id()))
                                ->find($item['harvest_id']);
                            if (! $harvest) {
                                throw new \RuntimeException("La cosecha #{$item['harvest_id']} no te pertenece.");
                            }
                            if ($this->invoice->status === 'draft') {
                                $containerStockService->reserveStock($harvest, $createdItem);
                            } else {
                                // status = 'sent': go straight to sold
                                $containerStockService->directSale(
                                    $harvest,
                                    $createdItem,
                                    $this->invoice->invoice_number ?? ''
                                );
                            }
                        } elseif (! empty($item['wine_lot_id'])) {
                            $lot = ProductLot::where('user_id', Auth::id())
                                ->lockForUpdate()
                                ->find($item['wine_lot_id']);
                            if ($lot) {
                                ProductStockService::moveOnCreate($this->invoice, $createdItem, $lot, $qty);
                            }
                        }
                    }
                });

                $this->invoice->logAction(
                    'updated',
                    'Factura de productor actualizada',
                    [
                        'client_id' => ['old' => $this->invoice->getOriginal('client_id'), 'new' => $this->client_id],
                        'total_amount' => ['old' => $this->invoice->getOriginal('total_amount'), 'new' => $totals['total']],
                        'items_count' => count($this->items),
                    ]
                );
            });

            $this->toastSuccess(__('Factura actualizada correctamente.'));

            return $this->redirect(route('producer.invoices.mixed.index'), navigate: true);

        } catch (\Exception $e) {
            Log::error('Error al actualizar factura de productor: '.$e->getMessage(), [
                'invoice_id' => $this->invoice->id,
                'user_id' => Auth::id(),
                'exception' => $e,
            ]);
            $this->toastError($e instanceof \RuntimeException ? $e->getMessage() : __('Error al actualizar la factura. Inténtalo de nuevo.'));
        }
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        $user = Auth::user();
        $campaigns = Campaign::where('viticulturist_id', $user->id)
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
            return [
                'payment_status' => [
                    'required',
                    'in:unpaid,paid,overdue,refunded',
                    new \App\Rules\InvoiceStateCoherence(
                        $this->invoice->status,
                        request()->input('payment_status'),
                        $this->delivery_status
                    ),
                ],
            ];
        }

        return [
            'client_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value && ! \App\Models\Client::where('id', $value)->where('user_id', Auth::id())->exists()) {
                        $fail(__('El cliente seleccionado no es válido.'));
                    }
                },
            ],
            'client_address_id' => 'required|exists:client_addresses,id',
            'invoice_date' => 'nullable|date',
            'delivery_note_date' => 'nullable|date',
            'delivery_status' => [
                'required',
                'in:pending,in_transit,delivered,cancelled',
                new \App\Rules\InvoiceStateCoherence(
                    $this->invoice->status,
                    $this->payment_status,
                    request()->input('delivery_status')
                ),
            ],
            'payment_status' => [
                'required',
                'in:unpaid,paid,overdue,refunded',
                new \App\Rules\InvoiceStateCoherence(
                    $this->invoice->status,
                    request()->input('payment_status'),
                    $this->delivery_status
                ),
            ],
            'payment_type' => 'nullable|in:cash,transfer,check,other',
            'observations' => 'nullable|string',
            'observations_invoice' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.sku' => 'nullable|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit' => 'nullable|string|max:20',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
            'items.*.tax_id' => 'nullable|exists:taxes,id',
            'items.*.concept_type' => 'nullable|in:harvest,wine,service,other',
        ];
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
