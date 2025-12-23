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

class Create extends Component
{
    use WithToastNotifications;

    public $client_id = '';
    public $client_address_id = '';
    public $invoice_date = '';
    public $due_date = '';
    public $delivery_note_date = ''; // Fecha del albarán (editable)
    public $items = [];
    public $observations = '';
    public $observations_invoice = '';
    public $delivery_note_code = ''; // Código de albarán (editable)
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
        
        $this->loadData();
        
        // Generar código de albarán automáticamente
        $user = Auth::user();
        $settings = \App\Models\InvoicingSetting::getOrCreateForUser($user->id);
        $this->delivery_note_code_auto = $settings->generateDeliveryNoteCode();
        $this->delivery_note_code = $this->delivery_note_code_auto;
        
        // Si hay una cosecha requerida, añadirla automáticamente después de cargar datos
        if ($this->requiredHarvestId && !$this->harvestAdded) {
            $this->selectedHarvestId = $this->requiredHarvestId;
            $this->addHarvestToInvoice();
            $this->harvestAdded = true;
        }
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
        
        // VALIDACIÓN CRÍTICA: Verificar que la cosecha no esté ya facturada en DB
        $alreadyInvoiced = \App\Models\InvoiceItem::where('harvest_id', $harvest->id)
            ->whereHas('invoice', function($q) {
                $q->where('status', '!=', 'cancelled') // Excluir facturas canceladas
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
        $this->harvestAdded = true;
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
                $this->toastError('Debes mantener al menos una cosecha en la factura.');
                return;
            }
        }
        
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    protected function rules(): array
    {
        $rules = [
            'client_id' => 'required|exists:clients,id',
            'client_address_id' => 'required|exists:client_addresses,id', // AHORA OBLIGATORIO
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'delivery_note_date' => 'required|date|before_or_equal:today',
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
        ];
        
        // Si viene desde la ruta de facturar cosecha, validar que haya al menos una cosecha
        if ($this->fromHarvestRoute) {
            $rules['items'] = 'required|array|min:1';
        }
        
        return $rules;
    }
    
    protected function messages(): array
    {
        return [
            'client_address_id.required' => 'Debes seleccionar un cliente con dirección. Este cliente no tiene direcciones configuradas.',
            'items.required' => 'Debes añadir al menos un item a la factura.',
            'items.min' => 'Debes añadir al menos un item a la factura.',
        ];
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
            
            if (!$hasHarvest) {
                $this->addError('items', 'Debes seleccionar al menos una cosecha para facturar.');
                return;
            }
        }
        
        $this->validate();

        $user = Auth::user();

        try {
            DB::transaction(function () use ($user) {
                // Obtener settings de invoicing
                $settings = \App\Models\InvoicingSetting::getOrCreateForUser($user->id);
                
                // Generar código de albarán atómicamente (previene race conditions)
                $deliveryNoteCode = $this->delivery_note_code_modified 
                    ? $this->delivery_note_code 
                    : $settings->generateAndIncrementDeliveryNoteCode();
                
                // NO generamos invoice_number aquí, solo cuando se aprueba la factura (en el observer)

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

                // Crear factura (sin invoice_number, se asignará cuando se apruebe)
                $invoice = Invoice::create([
                    'user_id' => $user->id,
                    'client_id' => $this->client_id,
                    'client_address_id' => $this->client_address_id ?: null,
                    'order_date' => $this->invoice_date, // Fecha de pedido
                    // invoice_number se asignará cuando se apruebe la factura (en InvoiceObserver)
                    'delivery_note_code' => $deliveryNoteCode,
                    'invoice_date' => $this->invoice_date,
                    'delivery_note_date' => $this->delivery_note_date ?: now(), // Usar fecha del formulario
                    'due_date' => $this->due_date ?: null,
                    'subtotal' => $subtotal,
                    'discount_amount' => $discountAmount,
                    'tax_base' => $subtotal,
                    'tax_rate' => $taxAmount > 0 ? ($taxAmount / $subtotal) * 100 : 0,
                    'tax_amount' => $taxAmount,
                    'total_amount' => $totalAmount,
                    'status' => 'draft',
                    'delivery_status' => 'pending', // Estado inicial de entrega
                    'payment_status' => 'unpaid', // Estado inicial de pago
                    'observations' => $this->observations ?: null,
                    'observations_invoice' => $this->observations_invoice ?: null,
                ]);

                // Crear items
                foreach ($this->items as $itemData) {
                    $itemSubtotal = $itemData['quantity'] * $itemData['unit_price'];
                    $itemDiscount = $itemSubtotal * ($itemData['discount_percentage'] / 100);
                    $itemSubtotalAfterDiscount = $itemSubtotal - $itemDiscount;
                    
                    $tax = $itemData['tax_id'] ? Tax::find($itemData['tax_id']) : null;
                    $taxRate = $tax ? $tax->rate : 0;
                    $itemTax = $itemSubtotalAfterDiscount * ($taxRate / 100);
                    $itemTotal = $itemSubtotalAfterDiscount + $itemTax;

                    $invoice->items()->create([
                        'harvest_id' => $itemData['harvest_id'] ?? null,
                        'name' => $itemData['name'],
                        'description' => $itemData['description'] ?? null,
                        'sku' => $itemData['sku'] ?? null,
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'discount_percentage' => $itemData['discount_percentage'] ?? 0,
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
                $invoice->logAction(
                    'created',
                    'Factura creada',
                    [
                        'client_id' => $this->client_id,
                        'total_amount' => $totalAmount,
                        'items_count' => count($this->items),
                        'delivery_note_code' => $deliveryNoteCode,
                    ]
                );
            });

            $this->toastSuccess('Factura creada exitosamente.');
            return redirect()->route('viticulturist.invoices.index');
        } catch (\Exception $e) {
            $this->toastError('Error al crear la factura: ' . $e->getMessage());
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
}
