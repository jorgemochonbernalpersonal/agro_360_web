<?php

namespace App\Livewire\Viticulturist\Invoices;

use App\Livewire\Concerns\WithInvoiceFormRules;
use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\Harvest;
use App\Models\Invoice;
use App\Models\MarketedHarvest;
use App\Models\Tax;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use RuntimeException;

class Create extends Component
{
    use WithInvoiceFormRules, WithRoleAwareRedirect, WithToastNotifications;

    public $client_id = '';

    public $client_address_id = '';

    public $invoice_date = '';

    public $delivery_note_date = ''; // Fecha del albarán (editable)

    public $items = [];

    public $observations = '';

    public $observations_invoice = '';

    public $payment_type = '';

    public $delivery_note_code = ''; // Código de albarán (auto-generado, editable)

    public $delivery_note_code_auto = ''; // Código generado automáticamente

    public $delivery_note_code_modified = false; // Flag para saber si el usuario lo modificó

    public $availableClients = [];

    public $availableAddresses = [];

    public $availableTaxes = [];

    public $availableHarvests = [];

    public $selectedHarvestId = '';

    public $selectedCampaign = '';

    public $fromHarvestRoute = false; // Indica si viene desde la ruta de facturar cosecha

    public $requiredHarvestId = null; // ID de cosecha requerida si viene desde harvest route

    public $harvestAdded = false; // Flag para evitar añadir la cosecha múltiples veces

    public ?int $marketedHarvestId = null; // ID de MarketedHarvest a vincular tras guardar

    protected InvoiceService $invoiceService;

    public function boot(InvoiceService $invoiceService): void
    {
        $this->invoiceService = $invoiceService;
    }

    public function mount()
    {
        $this->invoice_date = now()->format('Y-m-d');
        $this->delivery_note_date = now()->format('Y-m-d'); // Default a hoy

        // Detectar si viene desde la ruta de facturar cosecha o tiene harvest_id en query
        $harvestId = request()->query('harvest_id');
        $this->fromHarvestRoute = $harvestId !== null;

        // Si viene con harvest_id, cargarlo automáticamente
        if ($harvestId && $this->fromHarvestRoute) {
            $this->requiredHarvestId = $harvestId;
        }

        // Vincular con MarketedHarvest si viene el parámetro
        $marketedHarvestId = request()->query('marketed_harvest_id');
        if ($marketedHarvestId) {
            $this->marketedHarvestId = (int) $marketedHarvestId;
        }

        $this->loadData();

        // Vista previa de códigos (sin modificar contadores en BD)
        $user = Auth::user();
        $settings = \App\Models\InvoicingSetting::getOrCreateForUser($user->id);
        $this->delivery_note_code_auto = $settings->getDeliveryNotePreview();
        $this->delivery_note_code = $this->delivery_note_code_auto;

        // Si hay una cosecha requerida, añadirla automáticamente después de cargar datos
        if ($this->requiredHarvestId && ! $this->harvestAdded) {
            $this->selectedHarvestId = $this->requiredHarvestId;
            $this->addHarvestToInvoice();
            $this->harvestAdded = true;
        }
    }

    public function loadData()
    {
        $user = Auth::user();
        $this->availableClients = Client::forUser($user->id)->active()->get();

        // Cargar solo los impuestos habilitados por el usuario, ordenados por preferencia
        $this->availableTaxes = $user->taxes()->orderByPivot('order')->get();

        // Fallback: si el usuario no ha configurado sus impuestos aún, mostrar todos
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
            ->when($this->selectedCampaign, function ($q) {
                $q->whereHas('activity', fn ($q) => $q->where('campaign_id', $this->selectedCampaign));
            })
            ->where('total_weight', '>', 0)
            ->orderBy('harvest_start_date', 'desc')
            ->get();

        // Cargar el último HarvestStock por cosecha en una sola query (evita N+1)
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

        // Stock disponible real (permite ventas parciales de la misma cosecha)
        $latestStock = \App\Models\HarvestStock::where('harvest_id', $harvest->id)
            ->latest('id')
            ->first();

        $availableQty = $latestStock
            ? (float) $latestStock->available_qty
            : (float) $harvest->total_weight;

        if ($availableQty <= 0) {
            $this->toastError(__('Esta cosecha no tiene stock disponible para facturar.'));

            return;
        }

        // Impuesto por defecto del usuario
        $user = Auth::user();
        $defaultTax = $user->defaultTax()->first()
            ?? $this->availableTaxes->where('code', 'IVA')->where('rate', 21)->first()
            ?? $this->availableTaxes->first();

        $grapeVarietyName = $harvest->plotPlanting->grapeVariety->name ?? 'Uva';
        $plotName = $harvest->activity->plot->name ?? '';
        $itemName = $grapeVarietyName.($plotName ? ' - '.$plotName : '');

        $this->items[] = [
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
        $this->harvestAdded = true;
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
                $this->client_address_id = '';
            }
        } else {
            $this->availableAddresses = collect();
            $this->client_address_id = '';
        }
    }

    public function updatedDeliveryNoteCode($value)
    {
        // Marcar como modificado si el usuario cambió el código
        if ($value !== $this->delivery_note_code_auto) {
            $this->delivery_note_code_modified = true;
        } else {
            $this->delivery_note_code_modified = false;
        }
    }

    public function addItem()
    {
        $this->items[] = [
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
        // Si viene desde harvest route y es el último item con cosecha, no permitir eliminar
        if ($this->fromHarvestRoute && isset($this->items[$index]['harvest_id']) && $this->items[$index]['harvest_id']) {
            $harvestItemsCount = 0;
            foreach ($this->items as $item) {
                if (isset($item['harvest_id']) && $item['harvest_id']) {
                    $harvestItemsCount++;
                }
            }

            if ($harvestItemsCount <= 1) {
                $this->toastError(__('Debes mantener al menos una cosecha en la factura.'));

                return;
            }
        }

        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function save()
    {
        // Validación adicional: si viene desde harvest route, debe tener al menos una cosecha
        if ($this->fromHarvestRoute) {
            $hasHarvest = false;
            foreach ($this->items as $item) {
                if (isset($item['harvest_id']) && $item['harvest_id']) {
                    $hasHarvest = true;
                    break;
                }
            }

            if (! $hasHarvest) {
                $this->addError('items', __('Debes seleccionar al menos una cosecha para facturar.'));

                return;
            }
        }

        $this->validate();

        $user = Auth::user();

        $deliveryNoteCode = null;

        try {
            DB::transaction(function () use ($user, &$deliveryNoteCode) {
                // Generar código de albarán atómicamente (previene race conditions)
                $deliveryNoteCode = $this->invoiceService->generateDeliveryNoteCode(
                    $user->id,
                    $this->delivery_note_code_modified,
                    $this->delivery_note_code,
                );

                // El número de factura se asigna al emitir (markAsSent), no al crear el borrador.
                $invoiceNumber = null;

                // Calcular totales — pre-cargar impuestos para evitar N+1
                $taxIds = collect($this->items)->pluck('tax_id')->filter()->unique()->values()->all();
                $taxRates = empty($taxIds) ? collect() : Tax::whereIn('id', $taxIds)->get()->keyBy('id');
                $totals = $this->invoiceService->calculateVatTotals($this->items, $taxRates);

                // Crear factura con número asignado desde el inicio (como el albarán)
                $invoice = Invoice::create([
                    'user_id' => $user->id,
                    'client_id' => $this->client_id,
                    'client_address_id' => $this->client_address_id ?: null,
                    'order_date' => $this->invoice_date,
                    'invoice_number' => null,
                    'delivery_note_code' => $deliveryNoteCode,
                    'invoice_date' => $this->invoice_date,
                    'delivery_note_date' => $this->delivery_note_date ?: now(), // Usar fecha del formulario
                    'subtotal' => $totals['tax_base'],
                    'discount_amount' => $totals['discount_amount'],
                    'tax_base' => $totals['tax_base'],
                    'tax_rate' => $totals['effective_tax_rate'],
                    'tax_amount' => $totals['tax_amount'],
                    'total_amount' => $totals['total'],
                    'status' => 'draft',
                    'delivery_status' => 'pending', // Estado inicial de entrega
                    'payment_status' => 'unpaid', // Estado inicial de pago
                    'payment_type' => $this->payment_type ?: null,
                    'observations' => $this->observations ?: null,
                    'observations_invoice' => $this->observations_invoice ?: null,
                ]);

                // Crear items (usa $taxRates pre-cargada — sin N+1)
                foreach ($this->items as $itemData) {
                    $tax = $taxRates->get($itemData['tax_id'] ?? null);
                    $line = $this->invoiceService->calculateVatLine($itemData, $tax);

                    $invoice->items()->create([
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

                // Registrar en audit log
                $invoice->logAction(
                    'created',
                    'Factura creada',
                    [
                        'client_id' => $this->client_id,
                        'total_amount' => $totals['total'],
                        'items_count' => count($this->items),
                        'delivery_note_code' => $deliveryNoteCode,
                    ]
                );

                // NOTE: InvoiceItemObserver.created already called ContainerStockService::reserveStock()
                // for each item above. Do NOT call HarvestStockService here — would double-reserve.

                // Vincular con Cosecha Comercializada si procede
                if ($this->marketedHarvestId) {
                    MarketedHarvest::where('viticulturist_id', $user->id)
                        ->where('id', $this->marketedHarvestId)
                        ->update(['invoice_id' => $invoice->id]);
                }
            });

            $this->toastSuccess("Albarán {$deliveryNoteCode} creado. Emítelo para generar el número de factura.");

            return $this->viticulturistRoleRedirect('invoices.index');
        } catch (\Exception $e) {
            $this->toastError($e instanceof RuntimeException ? $e->getMessage() : __('Error al crear la factura. Inténtalo de nuevo.'));
        }
    }

    public function render()
    {
        $user = Auth::user();
        $campaigns = Campaign::where('viticulturist_id', $user->id)
            ->orderBy('year', 'desc')
            ->get();

        return view('livewire.viticulturist.invoices.create', [
            'campaigns' => $campaigns,
        ])->layout('layouts.app');
    }

    protected function rules(): array
    {
        return $this->invoiceCreateRules('harvest,service,product,other');
    }

    protected function messages(): array
    {
        return [
            'client_address_id.required' => __('Debes seleccionar un cliente con dirección. Este cliente no tiene direcciones configuradas.'),
            'items.required' => __('Debes añadir al menos un item a la factura.'),
            'items.min' => __('Debes añadir al menos un item a la factura.'),
        ];
    }
}
