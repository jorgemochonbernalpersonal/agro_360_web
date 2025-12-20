<?php

namespace App\Livewire\Plots\Plantings;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\GrapeVariety;
use App\Models\PlotPlanting;
use App\Models\TrainingSystem;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Edit extends Component
{
    use WithToastNotifications;
    public PlotPlanting $planting;

    public $name = '';
    public $grape_variety_id = '';
    public $area_planted = '';
    public $harvest_limit_kg = '';
    public $planting_year = '';
    public $planting_date = '';
    public $vine_count = '';
    public $density = '';
    public $row_spacing = '';
    public $vine_spacing = '';
    public $rootstock = '';
    public $training_system_id = '';
    public $irrigated = false;
    public $status = 'active';
    public $notes = '';

    public function mount(PlotPlanting $planting): void
    {
        $this->planting = $planting->load('plot');

        // Autorización: solo quien puede actualizar la parcela puede editar la plantación
        if (!Auth::user()->can('update', $this->planting->plot)) {
            abort(403);
        }

        $this->name = $this->planting->name;
        $this->grape_variety_id = $this->planting->grape_variety_id;
        $this->area_planted = $this->planting->area_planted;
        $this->harvest_limit_kg = $this->planting->harvest_limit_kg;
        $this->planting_year = $this->planting->planting_year;
        $this->planting_date = optional($this->planting->planting_date)->format('Y-m-d');
        $this->vine_count = $this->planting->vine_count;
        $this->density = $this->planting->density;
        $this->row_spacing = $this->planting->row_spacing;
        $this->vine_spacing = $this->planting->vine_spacing;
        $this->rootstock = $this->planting->rootstock;
        $this->training_system_id = $this->planting->training_system_id;
        $this->irrigated = $this->planting->irrigated;
        $this->status = $this->planting->status;
        $this->notes = $this->planting->notes;
    }

    protected function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'grape_variety_id' => 'nullable|exists:grape_varieties,id',
            'area_planted' => 'required|numeric|min:0.001',
            'harvest_limit_kg' => 'nullable|numeric|min:0',
            'planting_year' => 'nullable|integer|min:1900|max:' . now()->year,
            'planting_date' => 'nullable|date',
            'vine_count' => 'nullable|integer|min:0',
            'density' => 'nullable|integer|min:0',
            'row_spacing' => 'nullable|numeric|min:0',
            'vine_spacing' => 'nullable|numeric|min:0',
            'rootstock' => 'nullable|string|max:255',
            'training_system_id' => 'nullable|exists:training_systems,id',
            'irrigated' => 'boolean',
            'status' => 'required|in:active,removed,experimental,replanting',
            'notes' => 'nullable|string',
        ];
    }

    public function update()
    {
        $this->validate();

        $this->planting->update([
            'name' => $this->name ?: null,
            'grape_variety_id' => $this->grape_variety_id ?: null,
            'area_planted' => $this->area_planted,
            'harvest_limit_kg' => $this->harvest_limit_kg ?: null,
            'planting_year' => $this->planting_year ?: null,
            'planting_date' => $this->planting_date ?: null,
            'vine_count' => $this->vine_count ?: null,
            'density' => $this->density ?: null,
            'row_spacing' => $this->row_spacing ?: null,
            'vine_spacing' => $this->vine_spacing ?: null,
            'rootstock' => $this->rootstock ?: null,
            'training_system_id' => $this->training_system_id ?: null,
            'irrigated' => (bool) $this->irrigated,
            'status' => $this->status,
            'notes' => $this->notes ?: null,
        ]);

        $this->toastSuccess('Plantación actualizada correctamente.');

        return $this->redirect(route('plots.plantings.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.plots.plantings.edit', [
            'grapeVarieties' => GrapeVariety::active()->orderBy('name')->get(),
            'trainingSystems' => TrainingSystem::where('active', true)->orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}


