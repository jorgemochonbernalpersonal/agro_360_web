<?php

namespace App\Livewire\Viticulturist\Invoices;

use App\Models\Invoice;
use App\Models\Client;
use App\Models\Tax;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Edit extends Component
{
    use WithToastNotifications;

    public Invoice $invoice;
    public $invoice_id;

    public $client_id = '';
    public $client_address_id = '';
    public $invoice_date = '';
    public $due_date = '';
    public $items = [];
    public $observations = '';
    public $observations_invoice = '';

    public $availableClients = [];
    public $availableAddresses = [];
    public $availableTaxes = [];

    public function mount($invoice)
    {
        $this->invoice_id = $invoice;
        $this->loadInvoice();
    }

    public function loadInvoice()
    {
        $user = Auth::user();
        $this->invoice = Invoice::forUser($user->id)
            ->with(['items', 'client.addresses'])
            ->findOrFail($this->invoice_id);

        $this->client_id = $this->invoice->client_id;
        $this->client_address_id = $this->invoice->client_address_id ?? '';
        $this->invoice_date = $this->invoice->invoice_date->format('Y-m-d');
        $this->due_date = $this->invoice->due_date ? $this->invoice->due_date->format('Y-m-d') : '';
        $this->observations = $this->invoice->observations ?? '';
        $this->observations_invoice = $this->invoice->observations_invoice ?? '';

        $this->items = $this->invoice->items->map(function($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description ?? '',
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
    }

    public function updatedClientId($value)
    {
        if ($value) {
            $client = Client::with('addresses')->find($value);
            $this->availableAddresses = $client ? $client->addresses : collect();
        } else {
            $this->availableAddresses = collect();
        }
    }

    public function addItem()
    {
        $this->items[] = [
            'id' => null,
            'name' => '',
            'description' => '',
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
            'client_address_id' => 'nullable|exists:client_addresses,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
            'items.*.tax_id' => 'nullable|exists:taxes,id',
            'observations' => 'nullable|string',
            'observations_invoice' => 'nullable|string',
        ];
    }

    public function update()
    {
        $this->validate();

        try {
            DB::transaction(function () {
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
                $this->invoice->update([
                    'client_id' => $this->client_id,
                    'client_address_id' => $this->client_address_id ?: null,
                    'invoice_date' => $this->invoice_date,
                    'due_date' => $this->due_date ?: null,
                    'subtotal' => $subtotal,
                    'discount_amount' => $discountAmount,
                    'tax_base' => $subtotal,
                    'tax_rate' => $taxAmount > 0 ? ($taxAmount / $subtotal) * 100 : 0,
                    'tax_amount' => $taxAmount,
                    'total_amount' => $totalAmount,
                    'observations' => $this->observations ?: null,
                    'observations_invoice' => $this->observations_invoice ?: null,
                ]);

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
                        'name' => $itemData['name'],
                        'description' => $itemData['description'] ?? null,
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
            });

            $this->toastSuccess('Factura actualizada exitosamente.');
            return redirect()->route('viticulturist.invoices.show', $this->invoice->id);
        } catch (\Exception $e) {
            $this->toastError('Error al actualizar la factura: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.viticulturist.invoices.edit')
            ->layout('layouts.app');
    }
}
