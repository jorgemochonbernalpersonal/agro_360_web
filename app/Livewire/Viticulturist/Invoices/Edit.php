<?php

namespace App\Livewire\Viticulturist\Invoices;

use App\Models\Invoice;
use App\Models\Client;
use App\Models\Tax;
use App\Models\Harvest;
use App\Models\Campaign;
use App\Livewire\Concerns\WithToastNotifications;
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
    public $due_date = '';
    public $delivery_status = '';
    public $payment_status = '';
    public $items = [];
    public $observations = '';
    public $observations_invoice = '';
    public $delivery_note_code = ''; // Código de albarán (editable)
    public $invoice_number = ''; // Código de factura (editable)
    public $delivery_note_code_auto = ''; // Código generado automáticamente
    public $invoice_number_auto = ''; // Código de factura generado automáticamente
    public $delivery_note_code_modified = false; // Flag para saber si el usuario lo modificó
    public $invoice_number_modified = false; // Flag para saber si el usuario lo modificó

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
                ->with(['items', 'client.addresses'])
                ->findOrFail($invoice);
        }
        
        $this->loadInvoiceData();
    }

    public function loadInvoiceData()
    {
        $user = Auth::user();
        $settings = \App\Models\InvoicingSetting::getOrCreateForUser($user->id);

        $this->client_id = $this->invoice->client_id;
        $this->client_address_id = $this->invoice->client_address_id ?? '';
        $this->invoice_date = $this->invoice->invoice_date->format('Y-m-d');
        $this->due_date = $this->invoice->due_date ? $this->invoice->due_date->format('Y-m-d') : '';
        $this->delivery_status = $this->invoice->delivery_status;
        $this->payment_status = $this->invoice->payment_status;
        $this->observations = $this->invoice->observations ?? '';
        $this->observations_invoice = $this->invoice->observations_invoice ?? '';
        
        // Cargar códigos existentes o generar automáticamente
        $this->delivery_note_code = $this->invoice->delivery_note_code ?? $settings->generateDeliveryNoteCode();
        $this->delivery_note_code_auto = $this->delivery_note_code;
        
        // Si la factura está aprobada o tiene número, cargarlo; sino generar uno
        if ($this->invoice->invoice_number) {
            $this->invoice_number = $this->invoice->invoice_number;
            $this->invoice_number_auto = $this->invoice_number;
        } else {
            // Solo generar si está aprobada o se va a aprobar
            if ($this->invoice->status !== 'draft') {
                $this->invoice_number_auto = $settings->generateInvoiceCode();
                $this->invoice_number = $this->invoice_number_auto;
            }
        }

        $this->items = $this->invoice->items->map(function($item) {
            return [
                'id' => $item->id,
                'harvest_id' => $item->harvest_id,
                'name' => $item->name,
                'description' => $item->description ?? '',
                'sku' => $item->sku ?? '',
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'discount_percentage' => $item->discount_percentage,
                'tax_id' => $item->tax_id,
                'concept_type' => $item->concept_type,
            ];
        })->toArray();

        $this->loadData();
        $this->updatedClientId($this->client_id);
    }

    public function loadData()
    {
        $user = Auth::user();
        $this->availableClients = Client::forUser($user->id)->active()->get();
        $this->availableTaxes = Tax::active()->get();
        $this->loadHarvests();
    }

    public function loadHarvests()
    {
        $user = Auth::user();
        
        $query = Harvest::whereHas('activity', function($q) use ($user) {
            $q->where('viticulturist_id', $user->id);
        })
        ->with(['activity.plot', 'plotPlanting.grapeVariety', 'activity.campaign'])
        ->whereDoesntHave('invoiceItems'); // Solo cosechas sin facturar

        if ($this->selectedCampaign) {
            $query->whereHas('activity', function($q) {
                $q->where('campaign_id', $this->selectedCampaign);
            });
        }

        $this->availableHarvests = $query->orderBy('harvest_start_date', 'desc')->get();
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

        // Verificar que la cosecha no esté ya en los items locales
        foreach ($this->items as $item) {
            if (isset($item['harvest_id']) && $item['harvest_id'] == $harvest->id) {
                $this->toastError('Esta cosecha ya está en la factura actual.');
                return;
            }
        }
        
        // VALIDACIÓN CRÍTICA: Verificar que la cosecha no esté ya facturada en OTRA factura
        $alreadyInvoiced = \App\Models\InvoiceItem::where('harvest_id', $harvest->id)
            ->whereHas('invoice', function($q) {
                $q->where('status', '!=', 'cancelled') // Excluir facturas canceladas
                  ->where('id', '!=', $this->invoice->id) // Excluir la factura actual
                  ->where('user_id', auth()->id()); // Solo verificar facturas del mismo usuario
            })
            ->exists();
        
        if ($alreadyInvoiced) {
            $this->toastError('Esta cosecha ya está facturada en otra factura válida. No puedes facturar la misma cosecha dos veces.');
            return;
        }

        // Obtener el impuesto por defecto del usuario si existe
        $defaultTax = null;
        $user = Auth::user();
        $userDefaultTax = $user->defaultTax()->first();
        if ($userDefaultTax) {
            $defaultTax = $userDefaultTax;
        } else {
            // Si no hay impuesto por defecto, usar el primero disponible o el IVA general
            $defaultTax = $this->availableTaxes->where('code', 'IVA')->where('rate', 21)->first() 
                        ?? $this->availableTaxes->first();
        }

        // Crear item con datos de la cosecha
        $grapeVarietyName = $harvest->plotPlanting->grapeVariety->name ?? 'Uva';
        $plotName = $harvest->activity->plot->name ?? '';
        $itemName = $grapeVarietyName . ($plotName ? ' - ' . $plotName : '');

        $this->items[] = [
            'id' => null,
            'harvest_id' => $harvest->id,
            'name' => $itemName,
            'description' => 'Cosecha del ' . $harvest->harvest_start_date->format('d/m/Y') . 
                           ($harvest->plotPlanting->grapeVariety ? ' - Variedad: ' . $harvest->plotPlanting->grapeVariety->name : ''),
            'sku' => 'HARV-' . $harvest->id,
            'quantity' => $harvest->total_weight,
            'unit_price' => $harvest->price_per_kg ?? 0,
            'discount_percentage' => 0,
            'tax_id' => $defaultTax ? $defaultTax->id : null,
            'concept_type' => 'harvest',
        ];

        $this->selectedHarvestId = '';
        $this->toastSuccess('Cosecha añadida a la factura.');
    }

    public function updatedClientId($value)
    {
        if ($value) {
            $client = Client::with('addresses')->find($value);
            
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

    public function updatedDeliveryNoteCode($value)
    {
        // Marcar como modificado si el usuario cambió el código
        if ($value !== $this->delivery_note_code_auto) {
            $this->delivery_note_code_modified = true;
        } else {
            $this->delivery_note_code_modified = false;
        }
    }

    public function updatedInvoiceNumber($value)
    {
        // Marcar como modificado si el usuario cambió el código
        if ($value !== $this->invoice_number_auto) {
            $this->invoice_number_modified = true;
        } else {
            $this->invoice_number_modified = false;
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
    }

    protected function rules(): array
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'client_address_id' => 'required|exists:client_addresses,id', // AHORA OBLIGATORIO
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
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
            'observations' => 'nullable|string',
            'observations_invoice' => 'nullable|string',
            'delivery_note_code' => 'required|string|max:255',
            'invoice_number' => 'nullable|string|max:255',
        ];
    }

    public function update()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                $user = Auth::user();
                $settings = \App\Models\InvoicingSetting::getOrCreateForUser($user->id);
                
                // Generar código de albarán atómicamente si no existe o no fue modificado
                $deliveryNoteCode = $this->delivery_note_code_modified 
                    ? $this->delivery_note_code 
                    : ($this->invoice->delivery_note_code ?? $settings->generateAndIncrementDeliveryNoteCode());
                
                // Si la factura está aprobada o se va a aprobar y no tiene número, generarlo atómicamente
                $invoiceNumber = null;
                if ($this->invoice->status !== 'draft' || $this->invoice_number) {
                    $invoiceNumber = $this->invoice_number_modified 
                        ? $this->invoice_number 
                        : ($this->invoice->invoice_number ?? $settings->generateAndIncrementInvoiceCode());
                }
                
                // Calcular totales
                $subtotal = 0;
                $discountAmount = 0;
                $taxAmount = 0;

                foreach ($this->items as $itemData) {
                    $itemSubtotal = $itemData['quantity'] * $itemData['unit_price'];
                    $itemDiscount = $itemSubtotal * ($itemData['discount_percentage'] / 100);
                    $itemSubtotalAfterDiscount = $itemSubtotal - $itemDiscount;
                    
                    $tax = $itemData['tax_id'] ? Tax::find($itemData['tax_id']) : null;
                    $taxRate = $tax ? $tax->rate : 0;
                    $itemTax = $itemSubtotalAfterDiscount * ($taxRate / 100);

                    $subtotal += $itemSubtotalAfterDiscount;
                    $discountAmount += $itemDiscount;
                    $taxAmount += $itemTax;
                }

                $totalAmount = $subtotal + $taxAmount;

                // Actualizar factura
                $updateData = [
                    'client_id' => $this->client_id,
                    'client_address_id' => $this->client_address_id ?: null,
                    'invoice_date' => $this->invoice_date,
                    'due_date' => $this->due_date ?: null,
                    'delivery_status' => $this->delivery_status,
                    'payment_status' => $this->payment_status,
                    'delivery_note_code' => $deliveryNoteCode,
                    'subtotal' => $subtotal,
                    'discount_amount' => $discountAmount,
                    'tax_base' => $subtotal,
                    'tax_rate' => $taxAmount > 0 ? ($taxAmount / $subtotal) * 100 : 0,
                    'tax_amount' => $taxAmount,
                    'total_amount' => $totalAmount,
                    'observations' => $this->observations ?: null,
                    'observations_invoice' => $this->observations_invoice ?: null,
                ];
                
                // Solo actualizar invoice_number si existe o se va a aprobar
                if ($invoiceNumber !== null) {
                    $updateData['invoice_number'] = $invoiceNumber;
                }
                
                $this->invoice->update($updateData);

                // Eliminar items existentes
                $this->invoice->items()->delete();

                // Crear nuevos items
                foreach ($this->items as $itemData) {
                    $itemSubtotal = $itemData['quantity'] * $itemData['unit_price'];
                    $itemDiscount = $itemSubtotal * ($itemData['discount_percentage'] / 100);
                    $itemSubtotalAfterDiscount = $itemSubtotal - $itemDiscount;
                    
                    $tax = $itemData['tax_id'] ? Tax::find($itemData['tax_id']) : null;
                    $taxRate = $tax ? $tax->rate : 0;
                    $itemTax = $itemSubtotalAfterDiscount * ($taxRate / 100);
                    $itemTotal = $itemSubtotalAfterDiscount + $itemTax;

                    $this->invoice->items()->create([
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
                }
                
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
            return redirect()->route('viticulturist.invoices.show', $this->invoice->id);
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
