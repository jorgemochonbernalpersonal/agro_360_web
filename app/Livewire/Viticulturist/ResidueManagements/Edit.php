<?php

namespace App\Livewire\Viticulturist\ResidueManagements;

use App\Livewire\Viticulturist\AbstractEdit;
use App\Models\Campaign;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\ResidueManagement;
use App\Models\Unit;

class Edit extends AbstractEdit
{
    public ResidueManagement $residueManagement;

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

    public function mount(ResidueManagement $residueManagement): void
    {
        $this->authorizeOwnership($residueManagement);

        $this->residueManagement  = $residueManagement;
        $this->campaign_id        = (string) $residueManagement->campaign_id;
        $this->plot_id            = (string) ($residueManagement->plot_id ?? '');
        $this->plot_planting_id   = (string) ($residueManagement->plot_planting_id ?? '');
        $this->date               = $residueManagement->date->format('Y-m-d');
        $this->practice_type      = $residueManagement->practice_type;
        $this->material_type      = $residueManagement->material_type;
        $this->estimated_quantity = (string) ($residueManagement->estimated_quantity ?? '');
        $this->quantity_unit      = $residueManagement->quantity_unit ?? 'kg';
        $this->justification      = (string) ($residueManagement->justification ?? '');
        $this->notes              = (string) ($residueManagement->notes ?? '');
    }

    protected function rules(): array
    {
        $rules = [
            'campaign_id'        => $this->campaignOwnershipRule(),
            'plot_id'            => $this->plotOwnershipRule(false),
            'plot_planting_id'   => $this->plotPlantingOwnershipRule(),
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

    protected function performUpdate(): void
    {
        $this->residueManagement->update([
            'campaign_id'        => $this->campaign_id,
            'plot_id'            => $this->plot_id ?: null,
            'plot_planting_id'   => $this->plot_planting_id ?: null,
            'date'               => $this->date,
            'practice_type'      => $this->practice_type,
            'material_type'      => $this->material_type,
            'estimated_quantity' => $this->estimated_quantity ?: null,
            'quantity_unit'      => $this->quantity_unit ?: null,
            'justification'      => $this->practice_type === 'burning' ? ($this->justification ?: null) : null,
            'notes'              => $this->notes ?: null,
        ]);
    }

    protected function successMessage(): string
    {
        return __('Gestión de residuos actualizada.');
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
                ->with(['plot', 'grapeVariety'])->active()->get(),
            'practiceTypes' => ResidueManagement::PRACTICE_TYPES,
            'materialTypes' => ResidueManagement::MATERIAL_TYPES,
            'units'         => Unit::active()->where('category', 'weight')->orderBy('name')->get(),
        ];
    }
}
