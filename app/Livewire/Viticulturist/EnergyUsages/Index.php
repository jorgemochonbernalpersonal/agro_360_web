<?php

namespace App\Livewire\Viticulturist\EnergyUsages;

use App\Models\Campaign;
use App\Models\EnergyUsage;
use App\Models\Machinery;
use App\Livewire\Concerns\WithToastNotifications;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithToastNotifications;

    // Filters
    public $filterCampaign = '';
    public $filterEnergyType = '';

    // Form
    public bool $showModal = false;
    public ?int $editingId = null;

    public $campaign_id = '';
    public $machinery_id = '';
    public $date = '';
    public $energy_type = 'diesel';
    public $unit = 'liters';
    public $quantity = '';
    public $cost_per_unit = '';
    public $total_cost = '';
    public $co2_kg_equivalent = '';
    public $usage_description = '';
    public $notes = '';

    public function mount()
    {
        $user = Auth::user();
        $campaign = Campaign::getOrCreateActiveForYear($user->id);
        $this->filterCampaign = $campaign?->id ?? '';
        $this->campaign_id = $campaign?->id ?? '';
        $this->date = now()->format('Y-m-d');
    }

    public function updatedQuantity($v)
    {
        $this->recalculate($v, $this->cost_per_unit, $this->energy_type);
    }

    public function updatedCostPerUnit($v)
    {
        $this->recalculate($this->quantity, $v, $this->energy_type);
    }

    public function updatedEnergyType($v)
    {
        $this->recalculate($this->quantity, $this->cost_per_unit, $v);
        // Sugerir unidad
        $this->unit = match($v) {
            'electricity' => 'kwh',
            'natural_gas' => 'm3',
            default       => 'liters',
        };
    }

    protected function recalculate($qty, $cost, $type): void
    {
        if ($qty) {
            $factor = EnergyUsage::CO2_FACTORS[$type] ?? 0;
            $this->co2_kg_equivalent = round($qty * $factor, 3);
        }
        if ($qty && $cost) {
            $this->total_cost = round($qty * $cost, 2);
        }
    }

    public function openCreate()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id)
    {
        $entry = EnergyUsage::where('viticulturist_id', Auth::id())->findOrFail($id);
        $this->editingId = $id;
        $this->campaign_id = $entry->campaign_id;
        $this->machinery_id = $entry->machinery_id ?? '';
        $this->date = $entry->date->format('Y-m-d');
        $this->energy_type = $entry->energy_type;
        $this->unit = $entry->unit;
        $this->quantity = $entry->quantity;
        $this->cost_per_unit = $entry->cost_per_unit ?? '';
        $this->total_cost = $entry->total_cost ?? '';
        $this->co2_kg_equivalent = $entry->co2_kg_equivalent ?? '';
        $this->usage_description = $entry->usage_description ?? '';
        $this->notes = $entry->notes ?? '';
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate($this->rules());
        $user = Auth::user();

        $data = [
            'campaign_id'       => $this->campaign_id,
            'viticulturist_id'  => $user->id,
            'machinery_id'      => $this->machinery_id ?: null,
            'date'              => $this->date,
            'energy_type'       => $this->energy_type,
            'unit'              => $this->unit,
            'quantity'          => $this->quantity,
            'cost_per_unit'     => $this->cost_per_unit ?: null,
            'usage_description' => $this->usage_description ?: null,
            'notes'             => $this->notes ?: null,
            'active'            => true,
        ];

        if ($this->editingId) {
            EnergyUsage::where('viticulturist_id', $user->id)->findOrFail($this->editingId)->update($data);
            $this->toastSuccess('Consumo energético actualizado.');
        } else {
            EnergyUsage::create($data);
            $this->toastSuccess('Consumo energético registrado.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function deactivate(int $id)
    {
        EnergyUsage::where('viticulturist_id', Auth::id())->findOrFail($id)->update(['active' => false]);
        $this->toastSuccess('Registro archivado.');
    }

    protected function rules(): array
    {
        return [
            'campaign_id'    => 'required|exists:campaigns,id',
            'date'           => 'required|date',
            'energy_type'    => 'required|in:' . implode(',', array_keys(EnergyUsage::ENERGY_TYPES)),
            'unit'           => 'required|in:' . implode(',', array_keys(EnergyUsage::UNITS)),
            'quantity'       => 'required|numeric|min:0.001',
            'cost_per_unit'  => 'nullable|numeric|min:0',
            'machinery_id'   => 'nullable|exists:machinery,id',
            'usage_description' => 'nullable|string|max:255',
            'notes'          => 'nullable|string',
        ];
    }

    protected function resetForm()
    {
        $this->editingId = null;
        $this->machinery_id = '';
        $this->date = now()->format('Y-m-d');
        $this->energy_type = 'diesel';
        $this->unit = 'liters';
        $this->quantity = '';
        $this->cost_per_unit = '';
        $this->total_cost = '';
        $this->co2_kg_equivalent = '';
        $this->usage_description = '';
        $this->notes = '';
        $this->resetValidation();
    }

    public function render()
    {
        $user = Auth::user();

        $query = EnergyUsage::where('viticulturist_id', $user->id)->active();
        if ($this->filterCampaign) {
            $query->where('campaign_id', $this->filterCampaign);
        }
        if ($this->filterEnergyType) {
            $query->where('energy_type', $this->filterEnergyType);
        }

        $entries = $query->orderByDesc('date')->paginate(15);
        $campaigns = Campaign::forViticulturist($user->id)->orderByDesc('year')->get();
        $machinery = Machinery::where('viticulturist_id', $user->id)->orderBy('name')->get();

        // Totales CO₂ para campaña activa
        $co2Total = $this->filterCampaign
            ? EnergyUsage::forCampaign($this->filterCampaign)->where('viticulturist_id', $user->id)->active()->sum('co2_kg_equivalent')
            : 0;

        return view('livewire.viticulturist.energy-usages.index', [
            'entries'     => $entries,
            'campaigns'   => $campaigns,
            'machinery'   => $machinery,
            'energyTypes' => EnergyUsage::ENERGY_TYPES,
            'units'       => EnergyUsage::UNITS,
            'co2Total'    => $co2Total,
        ]);
    }
}
