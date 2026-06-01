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
use App\Services\ProductStockService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Create extends Component
{
    use WithToastNotifications;

    public string $client_id             = '';
    public string $client_address_id     = '';
    public string $invoice_date          = '';
    public string $delivery_note_date    = '';
    public string $payment_type          = '';
    public string $observations          = '';
    public string $observations_invoice  = '';
    public string $delivery_note_code    = '';
    public string $delivery_note_code_auto = '';
    public bool   $delivery_note_code_modified = false;

    public array $items = [];

    public string $selectedHarvestId = '';
    public string $selectedCampaign  = '';
    public string $selectedLotId     = '';

    public $availableClients   = [];
    public $availableAddresses = [];
    public $availableTaxes     = [];
    public $availableHarvests  = [];
    public $availableLots      = [];

    protected string $defaultTaxId = '';

    public function mount(): void
    {
        $this->invoice_date       = now()->toDateString();
        $this->delivery_note_date = now()->toDateString();

        $user = Auth::user();

        $this->availableTaxes = $user->taxes()->orderByPivot('order')->get();
        if ($this->availableTaxes->isEmpty()) {
            $this->availableTaxes = Tax::active()->orderBy('rate')->get();
        }

        $defaultTax         = $user->defaultTax()->first() ?? $this->availableTaxes->first();
        $this->defaultTaxId = (string) ($defaultTax?->id ?? '');

        $settings = InvoicingSetting::getOrCreateForUser($user->id);
        $this->delivery_note_code_auto = $settings->getDeliveryNotePreview();
        $this->delivery_note_code      = $this->delivery_note_code_auto;

        $this->loadData();
    }

    public function loadData(): void
    {
        $user = Auth::user();
        $this->availableClients = Client::forUser($user->id)->active()->get();
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
        ->when($this->selectedCampaign, function ($q) {
            $q->whereHas('activity', fn ($q) => $q->where('campaign_id', $this->selectedCampaign));
        })
        ->where('total_weight', '>', 0)
        ->orderBy('harvest_start_date', 'desc')
        ->get();

        $harvestIds   = $harvests->pluck('id');
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
        $this->availableLots = ProductLot::where('user_id', Auth::id())
            ->where('archived', false)
            ->where('available_quantity', '>', 0)
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
                $this->client_address_id  = '';
            }
        } else {
            $this->availableAddresses = collect();
            $this->client_address_id  = '';
        }
    }

    public function updatedDeliveryNoteCode(string $value): void
    {
        $this->delivery_note_code_modified = ($value !== $this->delivery_note_code_auto);
    }

    // ── Add harvest item ──────────────────────────────────────────────────────

    public function addHarvestToInvoice(): void
    {
        if (!$this->selectedHarvestId) {
            return;
        }

        $harvest = Harvest::with(['activity.plot', 'plotPlanting.grapeVariety'])
            ->find($this->selectedHarvestId);

        if (!$harvest) {
            $this->toastError(__('Cosecha no encontrada.'));
            return;
        }

        foreach ($this->items as $item) {
            if (isset($item['harvest_id']) && (int) $item['harvest_id'] === $harvest->id) {
                $this->toastError(__('Esta cosecha ya está en la factura actual.'));
                return;
            }
        }

        $latestStock  = HarvestStock::where('harvest_id', $harvest->id)->latest('id')->first();
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
        $plotName         = $harvest->activity->plot->name ?? '';
        $itemName         = $grapeVarietyName . ($plotName ? ' - ' . $plotName : '');

        $this->items[] = [
            'harvest_id'          => $harvest->id,
            'wine_lot_id'         => null,
            'concept_type'        => 'harvest',
            'name'                => $itemName,
            'description'         => __('Cosecha del ') . $harvest->harvest_start_date->format('d/m/Y') .
                                     ($harvest->plotPlanting->grapeVariety ? ' - Variedad: ' . $harvest->plotPlanting->grapeVariety->name : ''),
            'sku'                 => __('HARV-') . $harvest->id,
            'quantity'            => $availableQty,
            'unit'                => 'kg',
            'available_qty'       => $availableQty,
            'unit_price'          => $harvest->price_per_kg ?? 0,
            'discount_percentage' => 0,
            'tax_id'              => $defaultTax?->id,
        ];

        $this->selectedHarvestId = '';
        $this->toastSuccess(__('Cosecha añadida a la factura.'));
    }

    // ── Add wine lot item ─────────────────────────────────────────────────────

    public function addWineToInvoice(): void
    {
        if (!$this->selectedLotId) {
            return;
        }

        $lot = ProductLot::where('user_id', Auth::id())->find($this->selectedLotId);

        if (!$lot) {
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
            'harvest_id'          => null,
            'wine_lot_id'         => $lot->id,
            'concept_type'        => 'wine',
            'name'                => $lot->name . ($lot->vintage ? " ({$lot->vintage})" : ''),
            'description'         => '',
            'sku'                 => $lot->sku ?? '',
            'quantity'            => 1,
            'unit'                => 'botella',
            'available_qty'       => (float) $lot->available_quantity,
            'unit_price'          => $lot->price_per_unit ? (float) $lot->price_per_unit : 0,
            'discount_percentage' => 0,
            'tax_id'              => $this->defaultTaxId ?: null,
        ];

        $this->selectedLotId = '';
        $this->toastSuccess(__('Producto añadido al albarán.'));
    }

    // ── Add manual item ───────────────────────────────────────────────────────

    public function addItem(): void
    {
        $this->items[] = [
            'harvest_id'          => null,
            'wine_lot_id'         => null,
            'concept_type'        => 'other',
            'name'                => '',
            'description'         => '',
            'sku'                 => '',
            'quantity'            => 1,
            'unit'                => 'unidades',
            'available_qty'       => null,
            'unit_price'          => 0,
            'discount_percentage' => 0,
            'tax_id'              => $this->defaultTaxId ?: null,
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
            $sub      = (float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0);
            $discount = $sub * ((float) ($item['discount_percentage'] ?? 0) / 100);
            $total   += $sub - $discount;
        }
        return round($total, 3);
    }

    public function getDiscountAmountProperty(): float
    {
        $total = 0;
        foreach ($this->items as $item) {
            $sub    = (float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0);
            $total += $sub * ((float) ($item['discount_percentage'] ?? 0) / 100);
        }
        return round($total, 3);
    }

    public function getTaxAmountProperty(): float
    {
        $taxRates = $this->availableTaxes->keyBy('id');
        $total    = 0;
        foreach ($this->items as $item) {
            $sub     = (float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0);
            $discAmt = $sub * ((float) ($item['discount_percentage'] ?? 0) / 100);
            $base    = $sub - $discAmt;
            $rate    = ($item['tax_id'] ?? null)
                ? (float) ($taxRates[$item['tax_id']]?->rate ?? 0)
                : 0;
            $total  += $base * ($rate / 100);
        }
        return round($total, 3);
    }

    public function getTotalAmountProperty(): float
    {
        return round($this->subtotal + $this->taxAmount, 3);
    }

    // ── Validation ────────────────────────────────────────────────────────────

    protected function rules(): array
    {
        return [
            'client_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value && !\App\Models\Client::where('id', $value)->where('user_id', Auth::id())->exists()) {
                        $fail(__('El cliente seleccionado no es válido.'));
                    }
                },
            ],
            'client_address_id'            => 'required|exists:client_addresses,id',
            'invoice_date'                 => 'required|date',
            'delivery_note_date'           => 'required|date|before_or_equal:today',
            'delivery_note_code'           => 'required|string|max:255',
            'payment_type'                 => 'nullable|in:cash,transfer,check,other',
            'observations'                 => 'nullable|string',
            'observations_invoice'         => 'nullable|string',
            'items'                        => 'required|array|min:1',
            'items.*.name'                 => 'required|string|max:255',
            'items.*.description'          => 'nullable|string',
            'items.*.sku'                  => 'nullable|string|max:255',
            'items.*.quantity'             => 'required|numeric|min:0.001',
            'items.*.unit'                 => 'nullable|string|max:20',
            'items.*.unit_price'           => 'required|numeric|min:0',
            'items.*.discount_percentage'  => 'nullable|numeric|min:0|max:100',
            'items.*.tax_id'               => 'nullable|exists:taxes,id',
            'items.*.concept_type'         => 'nullable|in:harvest,wine,service,other',
        ];
    }

    protected function messages(): array
    {
        return [
            'client_address_id.required' => __('Debes seleccionar un cliente con dirección. Este cliente no tiene direcciones configuradas.'),
            'items.required'             => __('Debes añadir al menos un ítem a la factura.'),
            'items.min'                  => __('Debes añadir al menos un ítem a la factura.'),
        ];
    }

    // ── Save ──────────────────────────────────────────────────────────────────

    public function save()
    {
        $this->validate();

        $user         = Auth::user();
        $taxRates     = $this->availableTaxes->keyBy('id');
        $noteCode     = null;

        try {
            DB::transaction(function () use ($user, $taxRates, &$noteCode) {
                $settings = InvoicingSetting::getOrCreateForUser($user->id);

                $noteCode = $this->delivery_note_code_modified
                    ? $this->delivery_note_code
                    : $settings->generateAndIncrementDeliveryNoteCode();

                // Calculate totals
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
                    $tax          = ($item['tax_id'] ?? null) ? $taxRates[$item['tax_id']] ?? null : null;
                    $taxRate      = $tax ? (float) $tax->rate : 0;

                    $subtotal       += $lineSubtotal;
                    $discountAmount += $lineDiscount;
                    $taxAmount      += $lineBase * ($taxRate / 100);
                }

                $taxBase     = $subtotal - $discountAmount;
                $totalAmount = $taxBase + $taxAmount;

                $invoice = Invoice::create([
                    'user_id'              => $user->id,
                    'client_id'            => $this->client_id,
                    'client_address_id'    => $this->client_address_id ?: null,
                    'invoice_type'         => 'producer_sale',
                    'delivery_note_code'   => $noteCode,
                    'delivery_note_date'   => $this->delivery_note_date ?: now(),
                    'order_date'           => $this->invoice_date,
                    'invoice_date'         => $this->invoice_date,
                    'invoice_number'       => null,
                    'status'               => 'draft',
                    'delivery_status'      => 'pending',
                    'payment_status'       => 'unpaid',
                    'payment_type'         => $this->payment_type ?: null,
                    'subtotal'             => round($subtotal, 3),
                    'discount_amount'      => round($discountAmount, 3),
                    'tax_base'             => round($taxBase, 3),
                    'tax_rate'             => $taxAmount > 0 && $taxBase > 0 ? round(($taxAmount / $taxBase) * 100, 4) : 0,
                    'tax_amount'           => round($taxAmount, 3),
                    'total_amount'         => round($totalAmount, 3),
                    'observations'         => $this->observations ?: null,
                    'observations_invoice' => $this->observations_invoice ?: null,
                ]);

                $containerStockService = app(ContainerStockService::class);

                // Create items — bypass InvoiceItemObserver, handle stock manually
                InvoiceItem::withoutEvents(function () use ($invoice, $taxRates, $containerStockService) {
                    foreach ($this->items as $item) {
                        $qty           = (float) $item['quantity'];
                        $unitPrice     = (float) $item['unit_price'];
                        $discPct       = (float) ($item['discount_percentage'] ?? 0);
                        $lineSubtotal  = round($qty * $unitPrice, 3);
                        $lineDiscount  = round($lineSubtotal * ($discPct / 100), 3);
                        $lineBase      = round($lineSubtotal - $lineDiscount, 3);
                        $tax           = ($item['tax_id'] ?? null) ? $taxRates[$item['tax_id']] ?? null : null;
                        $taxRate       = $tax ? (float) $tax->rate : 0;
                        $taxAmountLine = round($lineBase * ($taxRate / 100), 3);

                        $createdItem = $invoice->items()->create([
                            'harvest_id'          => $item['harvest_id'] ?? null,
                            'wine_lot_id'         => $item['wine_lot_id'] ?? null,
                            'concept_type'        => $item['concept_type'] ?? 'other',
                            'name'                => $item['name'],
                            'description'         => $item['description'] ?: null,
                            'sku'                 => $item['sku'] ?: null,
                            'quantity'            => $qty,
                            'unit'                => $item['unit'] ?? 'unidades',
                            'unit_price'          => $unitPrice,
                            'discount_percentage' => $discPct,
                            'discount_amount'     => $lineDiscount,
                            'tax_id'              => $tax?->id,
                            'tax_name'            => $tax?->name,
                            'tax_rate'            => $taxRate,
                            'tax_base'            => $lineBase,
                            'tax_amount'          => $taxAmountLine,
                            'subtotal'            => $lineSubtotal,
                            'total'               => $lineBase + $taxAmountLine,
                        ]);

                        // Manual stock movement
                        if (!empty($item['harvest_id'])) {
                            // Ownership guard: la cosecha debe pertenecer al viticultor autenticado
                            // (el harvest_id viene del estado del cliente y no es de fiar).
                            $harvest = Harvest::whereHas('activity', fn ($q) => $q->where('viticulturist_id', Auth::id()))
                                ->find($item['harvest_id']);
                            if (!$harvest) {
                                throw new \RuntimeException("La cosecha #{$item['harvest_id']} no te pertenece.");
                            }
                            $containerStockService->reserveStock($harvest, $createdItem);
                        } elseif (!empty($item['wine_lot_id'])) {
                            $lot = ProductLot::where('user_id', Auth::id())
                                ->lockForUpdate()
                                ->find($item['wine_lot_id']);
                            if ($lot) {
                                ProductStockService::moveOnCreate($invoice, $createdItem, $lot, $qty);
                            }
                        }
                    }
                });
            });

            $this->toastSuccess("Albarán {$noteCode} creado. Emítelo para generar el número de factura.");
            return $this->redirect(route('producer.invoices.mixed.index'), navigate: true);

        } catch (\Exception $e) {
            Log::error('Error al crear factura de productor: ' . $e->getMessage(), [
                'user_id'   => Auth::id(),
                'exception' => $e,
            ]);
            $this->toastError($e instanceof \RuntimeException ? $e->getMessage()  : __('Error al crear la factura. Inténtalo de nuevo.'));
        }
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        $user      = Auth::user();
        $campaigns = Campaign::where('viticulturist_id', $user->id)
            ->orderBy('year', 'desc')
            ->get();

        return view('livewire.producer.invoices.create', [
            'campaigns' => $campaigns,
        ])->layout('layouts.app', ['title' => __('Crear albarán - Agro365')]);
    }
}
