<?php

namespace App\Livewire\Viticulturist\ResidueManagements;

use App\Livewire\Viticulturist\AbstractCreate;
use App\Models\Campaign;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\ResidueManagement;
use App\Models\Unit;

class Create extends AbstractCreate
{
    public string $campaign_id        = '';
    public string $plot_id            = '';
    public string $plot_planting_id   = '';
    public string $date               = '';
    public string $practice_type      = '';
    public string $material_type      = '';
    public string $estimated_quantity = '';
    public string $quantity_unit      = 'kg';
    public string $justification      = '';
    public string $notes              = '';

    public function mount(): void
    {
        $campaign = Campaign::getOrCreateActiveForYear($this->viticulturistId());
        $this->campaign_id = (string) ($campaign?->id ?? '');
        $this->date        = now()->format('Y-m-d');
    }

    protected function rules(): array
    {
        $rules = [
            'campaign_id'        => 'required|exists:campaigns,id',
            'plot_id'            => 'nullable|exists:plots,id',
            'plot_planting_id'   => 'nullable|exists:plot_plantings,id',
            'date'               => 'required|date',
            'practice_type'      => 'required|in:' . implode(',', array_keys(ResidueManagement::PRACTICE_TYPES)),
            'material_type'      => 'required|in:' . implode(',', array_keys(ResidueManagement::MATERIAL_TYPES)),
            'estimated_quantity' => 'nullable|numeric|min:0',
            'quantity_unit'      => 'nullable|exists:units,symbol',
            'justification'      => 'nullable|string',
            'notes'              => 'nullable|string',
        ];

        if ($this->practice_type === 'burning') {
            $rules['justification'] = 'required|string|min:20';
        }

        return $rules;
    }

    protected function performCreate(): void
    {
        ResidueManagement::create([
            'campaign_id'        => $this->campaign_id,
            'plot_id'            => $this->plot_id ?: null,
            'plot_planting_id'   => $this->plot_planting_id ?: null,
            'viticulturist_id'   => $this->viticulturistId(),
            'date'               => $this->date,
            'practice_type'      => $this->practice_type,
            'material_type'      => $this->material_type,
            'estimated_quantity' => $this->estimated_quantity ?: null,
            'quantity_unit'      => $this->quantity_unit ?: null,
            'justification'      => $this->practice_type === 'burning' ? ($this->justification ?: null) : null,
            'notes'              => $this->notes ?: null,
            'active'             => true,
        ]);
    }

    protected function successMessage(): string
    {
        return 'Gestión de residuos registrada.';
    }

    protected function indexRoute(): string
    {
        return 'viticulturist.residue-managements.index';
    }

    protected function viewData(): array
    {
        $id = $this->viticulturistId();

        return [
            'campaigns'     => Campaign::forViticulturist($id)->orderByDesc('year')->get(),
            'plots'         => Plot::where('viticulturist_id', $id)->active()->get(),
            'plantings'     => PlotPlanting::whereHas('plot', fn($q) => $q->where('viticulturist_id', $id))
                ->with(['plot', 'grape'])->active()->get(),
            'practiceTypes' => ResidueManagement::PRACTICE_TYPES,
            'materialTypes' => ResidueManagement::MATERIAL_TYPES,
            'units'         => Unit::active()->where('category', 'weight')->orderBy('name')->get(),
        ];
    }
}
