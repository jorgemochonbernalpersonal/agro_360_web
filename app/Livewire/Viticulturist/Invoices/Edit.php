<?php

namespace App\Livewire\Viticulturist\Invoices;

use App\Models\Invoice;
use App\Models\Client;
use App\Models\Tax;
use App\Models\Harvest;
use App\Models\Campaign;
use App\Livewire\Concerns\WithToastNotifications;
use App\Services\HarvestStockService;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Edit extends Component
{
    use WithToastNotifications;

    public Invoice $invoice;

    public $client_id = '';
    public $client_address_id = '';
    public $invoice_date = '';
    public $delivery_note_date = '';
    public $delivery_status = '';
    public $payment_status = '';
    public $items = [];
    public $observations = '';
    public $observations_invoice = '';
    public $delivery_note_code = ''; // Código de albarán (solo lectura)
    public $invoice_number = '';     // Número de factura (solo lectura si ya está facturada)

    // Modal de confirmación de facturación
    public $showInvoiceModal = false;
    public $invoice_date_modal = '';

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
        return $this->invoice->delivery_status === 'delivered' || $this->invoice->delivery_status === 'cancelled';
    }

    public $availableClients = [];
    public $availableAddresses = [];
    public $availableTaxes = [];
    public $availableHarvests = [];
    public $selectedHarvestId = '';
    public $selectedCampaign = '';

    public function mount($invoice)
    {
        // Si es un modelo, usarlo directamente; si es un ID, buscarlo
        if ($invoice instanceof Invoice) {
            $this->invoice = $invoice;
        } else {
            $user = Auth::user();
            $this->invoice = Invoice::forUser($user->id)
                ->with([
                    'items', 
                    'client.addresses.municipality',
                    'client.addresses.province',
                    'client.addresses.autonomousCommunity'
                ])
                ->findOrFail($invoice);
        }
        
        $this->loadInvoiceData();
    }

    public function loadInvoiceData()
    {
        $this->client_id           = $this->invoice->client_id;
        $this->client_address_id   = $this->invoice->client_address_id ?? '';
        $this->invoice_date        = $this->invoice->invoice_date
            ? $this->invoice->invoice_date->format('Y-m-d') : '';
        $this->delivery_note_date  = $this->invoice->delivery_note_date
            ? $this->invoice->delivery_note_date->format('Y-m-d') : '';
        $this->delivery_status     = $this->invoice->delivery_status;
        $this->payment_status      = $this->invoice->payment_status;
        $this->observations        = $this->invoice->observations ?? '';
        $this->observations_invoice = $this->invoice->observations_invoice ?? '';
        $this->delivery_note_code  = $this->invoice->delivery_note_code ?? '';
        $this->invoice_number      = $this->invoice->invoice_number ?? '';

        $this->items = $this->invoice->items->map(function ($item) {
            // Cargar stock disponible actual para items de cosecha
            $availableQty = null;
            $totalWeight  = null;
            if ($item->harvest_id) {
                $latestStock = \App\Models\HarvestStock::where('harvest_id', $item->harvest_id)
                    ->latest('id')
                    ->first();
                // Disponible = stock actual + lo que ya tiene reservado este item
                $currentAvail = $latestStock ? (float) $latestStock->available_qty : 0;
                $availableQty = $currentAvail + (float) $item->quantity; // devolver lo de este item al pool
                $totalWeight  = $item->harvest ? (float) $item->harvest->total_weight : null;
            }

            return [
                'id'                  => $item->id,
                'harvest_id'          => $item->harvest_id,
                'name'                => $item->name,
                'description'         => $item->description ?? '',
                'sku'                 => $item->sku ?? '',
                'quantity'            => $item->quantity,
                'available_qty'       => $availableQty,
                'total_weight'        => $totalWeight,
                'unit_price'          => $item->unit_price,
                'discount_percentage' => $item->discount_percentage,
                'tax_id'              => $item->tax_id,
                'concept_type'        => $item->concept_type,
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
        $this->toastSuccess('Cambios cancelados. Se restauraron los valores originales.');
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

        // IDs de cosechas ya presentes en esta factura (no repetir)
        $currentHarvestIds = collect($this->items)
            ->pluck('harvest_id')
            ->filter()
            ->toArray();

        $harvests = Harvest::whereHas('activity', function ($q) use ($user) {
            $q->where('viticulturist_id', $user->id);
        })
        ->with(['activity.plot', 'plotPlanting.grapeVariety', 'activity.campaign', 'container'])
        ->when($this->selectedCampaign, fn ($q) =>
            $q->whereHas('activity', fn ($q) => $q->where('campaign_id', $this->selectedCampaign))
        )
        ->where('total_weight', '>', 0)
        ->orderBy('harvest_start_date', 'desc')
        ->get();

        $this->availableHarvests = $harvests
            ->map(function ($harvest) {
                $latestStock = \App\Models\HarvestStock::where('harvest_id', $harvest->id)
                    ->latest('id')
                    ->first();
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
        if (!$this->selectedHarvestId) {
            return;
        }

        $harvest = Harvest::with(['activity.plot', 'plotPlanting.grapeVariety'])
            ->find($this->selectedHarvestId);

        if (!$harvest) {
            $this->toastError('Cosecha no encontrada.');
            return;
        }

        // Verificar que la cosecha no esté ya en los items locales (esta factura)
        foreach ($this->items as $item) {
            if (isset($item['harvest_id']) && $item['harvest_id'] == $harvest->id) {
                $this->toastError('Esta cosecha ya está en la factura actual.');
                return;
            }
        }

        // Stock disponible real
        $latestStock  = \App\Models\HarvestStock::where('harvest_id', $harvest->id)->latest('id')->first();
        $availableQty = $latestStock ? (float) $latestStock->available_qty : (float) $harvest->total_weight;

        if ($availableQty <= 0) {
            $this->toastError('Esta cosecha no tiene stock disponible para facturar.');
            return;
        }

        $user       = Auth::user();
        $defaultTax = $user->defaultTax()->first()
            ?? $this->availableTaxes->where('code', 'IVA')->where('rate', 21)->first()
            ?? $this->availableTaxes->first();

        $grapeVarietyName = $harvest->plotPlanting->grapeVariety->name ?? 'Uva';
        $plotName         = $harvest->activity->plot->name ?? '';
        $itemName         = $grapeVarietyName . ($plotName ? ' - ' . $plotName : '');

        $this->items[] = [
            'id'                  => null,
            'harvest_id'          => $harvest->id,
            'name'                => $itemName,
            'description'         => 'Cosecha del ' . $harvest->harvest_start_date->format('d/m/Y') .
                                     ($harvest->plotPlanting->grapeVariety ? ' - Variedad: ' . $harvest->plotPlanting->grapeVariety->name : ''),
            'sku'                 => 'HARV-' . $harvest->id,
            'quantity'            => $availableQty,
            'available_qty'       => $availableQty,
            'total_weight'        => (float) $harvest->total_weight,
            'unit_price'          => $harvest->price_per_kg ?? 0,
            'discount_percentage' => 0,
            'tax_id'              => $defaultTax ? $defaultTax->id : null,
            'concept_type'        => 'harvest',
        ];

        $this->selectedHarvestId = '';
        $this->toastSuccess('Cosecha añadida a la factura.');
    }

    public function updatedClientId($value)
    {
        if ($value) {
            $client = Client::with([
                'addresses.municipality',
                'addresses.province',
                'addresses.autonomousCommunity'
            ])->find($value);
            
            if ($client) {
                // Cargar automáticamente la primera dirección del cliente
                $primaryAddress = $client->addresses->first();
                
                if ($primaryAddress) {
                    $this->client_address_id = $primaryAddress->id;
                } else {
                    // Si no tiene dirección, mostrar error
                    $this->client_address_id = '';
                    $this->addError('client_id', 'Este cliente no tiene ninguna dirección configurada. Por favor, añade una dirección al cliente primero.');
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
            $this->toastError('Solo se puede facturar un albarán en estado borrador.');
            return;
        }

        $this->invoice_date_modal = $this->invoice_date ?: now()->format('Y-m-d');
        $this->showInvoiceModal   = true;
    }

    public function closeInvoiceModal()
    {
        $this->showInvoiceModal   = false;
        $this->invoice_date_modal = '';
    }

    public function markAsSent()
    {
        $this->validate(
            ['invoice_date_modal' => 'required|date'],
            ['invoice_date_modal.required' => 'Debes indicar la fecha de la factura.']
        );

        try {
            DB::transaction(function () {
                $settings = \App\Models\InvoicingSetting::getOrCreateForUser(Auth::user()->id);

                $this->invoice->update([
                    'status'         => 'sent',
                    'invoice_date'   => $this->invoice_date_modal,
                    'invoice_number' => $settings->generateAndIncrementInvoiceCode(),
                ]);
            });

            $this->toastSuccess('Factura emitida correctamente.');
            $this->closeInvoiceModal();
            return redirect()->route('viticulturist.invoices.index');
        } catch (\Exception $e) {
            $this->toastError('Error al facturar: ' . $e->getMessage());
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

    /**
     * Calcular totales de la factura
     */
    public function getSubtotalProperty(): float
    {
        $subtotal = 0;
        foreach ($this->items as $item) {
            $itemSubtotal = ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
            $itemDiscount = $itemSubtotal * (($item['discount_percentage'] ?? 0) / 100);
            $subtotal += $itemSubtotal - $itemDiscount;
        }
        return round($subtotal, 2);
    }

    public function getDiscountAmountProperty(): float
    {
        $discountAmount = 0;
        foreach ($this->items as $item) {
            $itemSubtotal = ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
            $itemDiscount = $itemSubtotal * (($item['discount_percentage'] ?? 0) / 100);
            $discountAmount += $itemDiscount;
        }
        return round($discountAmount, 2);
    }

    public function getTaxAmountProperty(): float
    {
        $taxAmount = 0;
        foreach ($this->items as $item) {
            $itemSubtotal = ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
            $itemDiscount = $itemSubtotal * (($item['discount_percentage'] ?? 0) / 100);
            $itemSubtotalAfterDiscount = $itemSubtotal - $itemDiscount;
            
            $tax = ($item['tax_id'] ?? null) ? Tax::find($item['tax_id']) : null;
            $taxRate = $tax ? $tax->rate : 0;
            $itemTax = $itemSubtotalAfterDiscount * ($taxRate / 100);
            $taxAmount += $itemTax;
        }
        return round($taxAmount, 2);
    }

    public function getTotalAmountProperty(): float
    {
        return round($this->subtotal + $this->taxAmount, 2);
    }


    protected function rules(): array
    {
        // Si está bloqueada (entregada o cancelada), solo validar payment_status
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

        // Validación normal para facturas no bloqueadas
        return [
            'client_id' => 'required|exists:clients,id',
            'client_address_id' => 'required|exists:client_addresses,id', // AHORA OBLIGATORIO
            'invoice_date' => 'nullable|date', // Solo requerido cuando se factura, no en borrador
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
            'items' => 'required|array|min:1', // Mínimo 1 item
            'items.*.name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.sku' => 'nullable|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
            'items.*.tax_id' => 'nullable|exists:taxes,id',
            'items.*.concept_type' => 'nullable|in:harvest,service,product,other',
            'delivery_note_date' => 'nullable|date',
            'observations' => 'nullable|string',
            'observations_invoice' => 'nullable|string',
        ];
    }

    public function update()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                // Si está bloqueada (entregada o cancelada), solo actualizar payment_status
                if ($this->isLocked) {
                    $this->invoice->update([
                        'payment_status' => $this->payment_status,
                    ]);

                    // Actualizar fecha de pago si cambia el estado
                    if ($this->payment_status === 'paid' && !$this->invoice->payment_date) {
                        $this->invoice->update(['payment_date' => now()]);
                    } elseif ($this->payment_status !== 'paid' && $this->invoice->payment_date) {
                        $this->invoice->update(['payment_date' => null]);
                    }

                    $this->toastSuccess('Estado de pago actualizado exitosamente.');
                    return redirect()->route('viticulturist.invoices.index');
                }

                // Si no está bloqueada, actualizar normalmente
                
                $subtotal       = $this->subtotal;
                $discountAmount = $this->discountAmount;
                $taxAmount      = $this->taxAmount;
                $totalAmount    = $this->totalAmount;

                $this->invoice->update([
                    'client_id'          => $this->client_id,
                    'client_address_id'  => $this->client_address_id ?: null,
                    'invoice_date'       => $this->invoice_date ?: null,
                    'delivery_note_date' => $this->delivery_note_date ?: null,
                    'delivery_status'    => $this->delivery_status,
                    'payment_status'     => $this->payment_status,
                    'subtotal'           => $subtotal,
                    'discount_amount'    => $discountAmount,
                    'tax_base'           => $subtotal,
                    'tax_rate'           => $taxAmount > 0 && $subtotal > 0 ? ($taxAmount / $subtotal) * 100 : 0,
                    'tax_amount'         => $taxAmount,
                    'total_amount'       => $totalAmount,
                    'observations'       => $this->observations ?: null,
                    'observations_invoice' => $this->observations_invoice ?: null,
                ]);

                // Unreserve all existing harvest items before deletion
                // (reads from DB while items still exist; delivery_status not yet changed)
                $existingHarvestItems = $this->invoice->items()
                    ->where('concept_type', 'harvest')
                    ->whereNotNull('harvest_id')
                    ->get();
                HarvestStockService::unreserveItems($existingHarvestItems, $this->invoice);

                // Eliminar items existentes
                $this->invoice->items()->delete();

                // Crear nuevos items y mover stock según el nuevo delivery_status.
                // InvoiceItemObserver se deshabilita para evitar doble movimiento:
                // el bulk-delete anterior no disparó Observer.deleting (masa), pero
                // items()->create() sí dispararía Observer.created (ContainerStockService).
                // HarvestStockService::moveOnItemSave es la fuente de verdad aquí.
                $newDeliveryStatus = $this->delivery_status;

                \App\Models\InvoiceItem::withoutObservers(function () use ($newDeliveryStatus) {
                    foreach ($this->items as $itemData) {
                        $itemSubtotal = $itemData['quantity'] * $itemData['unit_price'];
                        $itemDiscount = $itemSubtotal * ($itemData['discount_percentage'] / 100);
                        $itemSubtotalAfterDiscount = $itemSubtotal - $itemDiscount;

                        $tax = $itemData['tax_id'] ? Tax::find($itemData['tax_id']) : null;
                        $taxRate = $tax ? $tax->rate : 0;
                        $itemTax = $itemSubtotalAfterDiscount * ($taxRate / 100);
                        $itemTotal = $itemSubtotalAfterDiscount + $itemTax;

                        $newItem = $this->invoice->items()->create([
                            'harvest_id' => $itemData['harvest_id'] ?? null,
                            'name' => $itemData['name'],
                            'description' => $itemData['description'] ?? null,
                            'sku' => $itemData['sku'] ?? null,
                            'quantity' => $itemData['quantity'],
                            'unit_price' => $itemData['unit_price'],
                            'discount_percentage' => $itemData['discount_percentage'],
                            'discount_amount' => $itemDiscount,
                            'tax_id' => $itemData['tax_id'] ?: null,
                            'tax_name' => $tax ? $tax->name : null,
                            'tax_rate' => $taxRate,
                            'tax_base' => $itemSubtotalAfterDiscount,
                            'tax_amount' => $itemTax,
                            'subtotal' => $itemSubtotalAfterDiscount,
                            'total' => $itemTotal,
                            'concept_type' => $itemData['concept_type'] ?? 'other',
                        ]);

                        // HarvestStockService es la única fuente de stock en Edit
                        HarvestStockService::moveOnItemSave($this->invoice, $newItem, $newDeliveryStatus);
                    }
                });
                
                // Registrar en audit log
                $this->invoice->logAction(
                    'updated',
                    'Factura actualizada',
                    [
                        'client_id' => ['old' => $this->invoice->getOriginal('client_id'), 'new' => $this->client_id],
                        'total_amount' => ['old' => $this->invoice->getOriginal('total_amount'), 'new' => $totalAmount],
                        'delivery_status' => ['old' => $this->invoice->getOriginal('delivery_status'), 'new' => $this->delivery_status],
                        'payment_status' => ['old' => $this->invoice->getOriginal('payment_status'), 'new' => $this->payment_status],
                        'items_count' => count($this->items),
                    ]
                );
            });

            $this->toastSuccess('Factura actualizada exitosamente.');
            return redirect()->route('viticulturist.invoices.index');
        } catch (\Exception $e) {
            $this->toastError('Error al actualizar la factura: ' . $e->getMessage());
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
}
