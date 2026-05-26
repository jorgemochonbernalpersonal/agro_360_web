<?php

namespace App\Livewire\Viticulturist\EnergyUsages;

use App\Livewire\Viticulturist\AbstractCreate;
use App\Models\Campaign;
use App\Models\EnergyUsage;
use App\Models\Machinery;

class Create extends AbstractCreate
{
    public string $campaign_id       = '';
    public string $machinery_id      = '';
    public string $date              = '';
    public string $energy_type       = 'diesel';
    public string $unit              = 'liters';
    public string $quantity          = '';
    public string $cost_per_unit     = '';
    public string $total_cost        = '';
    public string $co2_kg_equivalent = '';
    public string $usage_description = '';
    public string $notes             = '';

    public function mount(): void
    {
        $campaign = Campaign::getOrCreateActiveForYear($this->viticulturistId());
        $this->campaign_id = (string) ($campaign?->id ?? '');
        $this->date        = now()->format('Y-m-d');
    }

    public function updatedQuantity(string $v): void
    {
        $this->recalculate($v, $this->cost_per_unit, $this->energy_type);
    }

    public function updatedCostPerUnit(string $v): void
    {
        $this->recalculate($this->quantity, $v, $this->energy_type);
    }

    public function updatedEnergyType(string $v): void
    {
        $this->recalculate($this->quantity, $this->cost_per_unit, $v);
        $this->unit = match ($v) {
            'electricity' => 'kwh',
            'natural_gas' => 'm3',
            default       => 'liters',
        };
    }

    protected function recalculate(string $qty, string $cost, string $type): void
    {
        if ($qty) {
            $factor = EnergyUsage::CO2_FACTORS[$type] ?? 0;
            $this->co2_kg_equivalent = (string) round((float) $qty * $factor, 3);
        } else {
            $this->co2_kg_equivalent = '';
        }

        if ($qty && $cost) {
            $this->total_cost = (string) round((float) $qty * (float) $cost, 2);
        } else {
            $this->total_cost = '';
        }
    }

    protected function rules(): array
    {
        return [
            'campaign_id'       => $this->campaignOwnershipRule(),
            'date'              => 'required|date',
            'energy_type'       => 'required|in:' . implode(',', array_keys(EnergyUsage::ENERGY_TYPES)),
            'unit'              => 'required|in:' . implode(',', array_keys(EnergyUsage::UNITS)),
            'quantity'          => 'required|numeric|min:0.001',
            'cost_per_unit'     => 'nullable|numeric|min:0',
            'machinery_id'      => 'nullable|exists:machinery,id',
            'usage_description' => 'nullable|string|max:255',
            'notes'             => 'nullable|string',
        ];
    }

    protected function performCreate(): void
    {
        EnergyUsage::create([
            'campaign_id'       => $this->campaign_id,
            'viticulturist_id'  => $this->viticulturistId(),
            'machinery_id'      => $this->machinery_id ?: null,
            'date'              => $this->date,
            'energy_type'       => $this->energy_type,
            'unit'              => $this->unit,
            'quantity'          => $this->quantity,
            'cost_per_unit'     => $this->cost_per_unit ?: null,
            'total_cost'        => $this->total_cost ?: null,
            'co2_kg_equivalent' => $this->co2_kg_equivalent ?: null,
            'usage_description' => $this->usage_description ?: null,
            'notes'             => $this->notes ?: null,
            'active'            => true,
        ]);
    }

    protected function successMessage(): string
    {
        return __('Consumo energético registrado.');
    }

    protected function indexRoute(): string
    {
        return 'viticulturist.energy-usages.index';
    }

    protected function viewData(): array
    {
        return [
            'campaigns'   => Campaign::forViticulturist($this->viticulturistId())->orderByDesc('year')->get(),
            'machinery'   => Machinery::where('viticulturist_id', $this->viticulturistId())->orderBy('name')->get(),
            'energyTypes' => EnergyUsage::ENERGY_TYPES,
            'units'       => EnergyUsage::UNITS,
        ];
    }
}
