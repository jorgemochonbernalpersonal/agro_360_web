<?php

namespace App\Livewire\Viticulturist\Supplies;

use App\Models\Supply;
use App\Models\SupplyPurchase;
use App\Models\Campaign;
use App\Livewire\Concerns\WithToastNotifications;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithToastNotifications;

    // Filters
    public $filterType = '';
    public $filterLow = false;

    // Supply form
    public bool $showModal = false;
    public ?int $editingId = null;

    public $name = '';
    public $commercial_name = '';
    public $registration_number = '';
    public $supply_type = 'phytosanitary';
    public $unit_of_measurement = 'L';
    public $initial_stock = 0;
    public $current_stock = 0;
    public $min_stock_alert = '';
    public $expiry_date = '';
    public $notes = '';

    // Purchase form
    public bool $showPurchaseModal = false;
    public ?int $purchaseSupplyId = null;
    public string $purchaseSupplyName = '';

    public $p_invoice_date = '';
    public $p_invoice_number = '';
    public $p_quantity = '';
    public $p_unit_of_measurement = 'L';
    public $p_price_per_unit = '';
    public $p_total_cost = '';
    public $p_supplier_name = '';
    public $p_campaign_id = '';
    public $p_notes = '';

    public function mount()
    {
        $user = Auth::user();
        $campaign = Campaign::getOrCreateActiveForYear($user->id);
        $this->p_campaign_id = $campaign?->id ?? '';
        $this->p_invoice_date = now()->format('Y-m-d');
    }

    // -- Supply CRUD --

    public function openCreate()
    {
        $this->resetSupplyForm();
        $this->showModal = true;
    }

    public function openEdit(int $id)
    {
        $supply = Supply::where('viticulturist_id', Auth::id())->findOrFail($id);
        $this->editingId = $id;
        $this->name = $supply->name;
        $this->commercial_name = $supply->commercial_name ?? '';
        $this->registration_number = $supply->registration_number ?? '';
        $this->supply_type = $supply->supply_type;
        $this->unit_of_measurement = $supply->unit_of_measurement;
        $this->initial_stock = $supply->initial_stock;
        $this->current_stock = $supply->current_stock;
        $this->min_stock_alert = $supply->min_stock_alert ?? '';
        $this->expiry_date = $supply->expiry_date?->format('Y-m-d') ?? '';
        $this->notes = $supply->notes ?? '';
        $this->showModal = true;
    }

    public function saveSupply()
    {
        $this->validate($this->supplyRules());
        $user = Auth::user();

        $data = [
            'viticulturist_id'  => $user->id,
            'name'              => $this->name,
            'commercial_name'   => $this->commercial_name ?: null,
            'registration_number' => $this->registration_number ?: null,
            'supply_type'       => $this->supply_type,
            'unit_of_measurement' => $this->unit_of_measurement,
            'initial_stock'     => $this->initial_stock ?: 0,
            'current_stock'     => $this->current_stock ?: 0,
            'min_stock_alert'   => $this->min_stock_alert ?: null,
            'expiry_date'       => $this->expiry_date ?: null,
            'notes'             => $this->notes ?: null,
            'active'            => true,
        ];

        if ($this->editingId) {
            Supply::where('viticulturist_id', $user->id)->findOrFail($this->editingId)->update($data);
            $this->toastSuccess('Insumo actualizado.');
        } else {
            Supply::create($data);
            $this->toastSuccess('Insumo registrado en el almacén.');
        }

        $this->showModal = false;
        $this->resetSupplyForm();
    }

    public function deactivate(int $id)
    {
        Supply::where('viticulturist_id', Auth::id())->findOrFail($id)->update(['active' => false]);
        $this->toastSuccess('Insumo archivado.');
    }

    // -- Purchase CRUD --

    public function openPurchase(int $supplyId)
    {
        $supply = Supply::where('viticulturist_id', Auth::id())->findOrFail($supplyId);
        $this->purchaseSupplyId = $supplyId;
        $this->purchaseSupplyName = $supply->name;
        $this->p_unit_of_measurement = $supply->unit_of_measurement;
        $this->resetPurchaseForm();
        $this->showPurchaseModal = true;
    }

    public function updatedPQuantity($v)
    {
        if ($v && $this->p_price_per_unit) {
            $this->p_total_cost = round($v * $this->p_price_per_unit, 2);
        }
    }

    public function updatedPPricePerUnit($v)
    {
        if ($v && $this->p_quantity) {
            $this->p_total_cost = round($this->p_quantity * $v, 2);
        }
    }

    public function savePurchase()
    {
        $this->validate($this->purchaseRules());
        $user = Auth::user();

        SupplyPurchase::create([
            'supply_id'          => $this->purchaseSupplyId,
            'viticulturist_id'   => $user->id,
            'campaign_id'        => $this->p_campaign_id ?: null,
            'invoice_date'       => $this->p_invoice_date,
            'invoice_number'     => $this->p_invoice_number ?: null,
            'quantity'           => $this->p_quantity,
            'unit_of_measurement'=> $this->p_unit_of_measurement,
            'price_per_unit'     => $this->p_price_per_unit ?: null,
            'total_cost'         => $this->p_total_cost ?: null,
            'supplier_name'      => $this->p_supplier_name ?: null,
            'notes'              => $this->p_notes ?: null,
        ]);

        $this->toastSuccess('Compra registrada. Stock actualizado.');
        $this->showPurchaseModal = false;
        $this->resetPurchaseForm();
    }

    protected function supplyRules(): array
    {
        return [
            'name'              => 'required|string|max:255',
            'supply_type'       => 'required|in:' . implode(',', array_keys(Supply::SUPPLY_TYPES)),
            'unit_of_measurement' => 'required|string|max:20',
            'initial_stock'     => 'nullable|numeric|min:0',
            'current_stock'     => 'nullable|numeric|min:0',
            'min_stock_alert'   => 'nullable|numeric|min:0',
            'expiry_date'       => 'nullable|date',
        ];
    }

    protected function purchaseRules(): array
    {
        return [
            'p_invoice_date'       => 'required|date',
            'p_quantity'           => 'required|numeric|min:0.001',
            'p_unit_of_measurement'=> 'required|string|max:20',
            'p_price_per_unit'     => 'nullable|numeric|min:0',
            'p_total_cost'         => 'nullable|numeric|min:0',
            'p_supplier_name'      => 'nullable|string|max:255',
        ];
    }

    protected function resetSupplyForm()
    {
        $this->editingId = null;
        $this->name = '';
        $this->commercial_name = '';
        $this->registration_number = '';
        $this->supply_type = 'phytosanitary';
        $this->unit_of_measurement = 'L';
        $this->initial_stock = 0;
        $this->current_stock = 0;
        $this->min_stock_alert = '';
        $this->expiry_date = '';
        $this->notes = '';
        $this->resetValidation();
    }

    protected function resetPurchaseForm()
    {
        $this->p_invoice_date = now()->format('Y-m-d');
        $this->p_invoice_number = '';
        $this->p_quantity = '';
        $this->p_price_per_unit = '';
        $this->p_total_cost = '';
        $this->p_supplier_name = '';
        $this->p_notes = '';
        $this->resetValidation();
    }

    public function render()
    {
        $user = Auth::user();

        $query = Supply::where('viticulturist_id', $user->id)->active();
        if ($this->filterType) {
            $query->where('supply_type', $this->filterType);
        }
        if ($this->filterLow) {
            $query->lowStock();
        }

        $supplies = $query->orderBy('name')->paginate(20);
        $campaigns = Campaign::forViticulturist($user->id)->orderByDesc('year')->get();

        return view('livewire.viticulturist.supplies.index', [
            'supplies'    => $supplies,
            'campaigns'   => $campaigns,
            'supplyTypes' => Supply::SUPPLY_TYPES,
        ]);
    }
}
