<?php

namespace App\Livewire\Plots\Plantings;

use App\Livewire\Concerns\WithRoleBasedFields;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\GrapeVariety;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\TrainingSystem;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    use WithRoleBasedFields, WithToastNotifications;

    public Plot $plot;

    public $grape_variety_id = '';
    public $area_planted = '';
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

    public function mount(Plot $plot): void
    {
        if (!Auth::user()->can('update', $plot)) {
            abort(403);
        }

        $this->plot = $plot;
    }

    protected function rules(): array
    {
        return [
            'grape_variety_id' => 'nullable|exists:grape_varieties,id',
            'area_planted' => 'required|numeric|min:0.001',
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

    public function save()
    {
        $this->validate();

        PlotPlanting::create([
            'plot_id' => $this->plot->id,
            'grape_variety_id' => $this->grape_variety_id ?: null,
            'area_planted' => $this->area_planted,
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

        $this->toastSuccess('Plantación creada correctamente.');

        return $this->redirect(route('plots.plantings.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.plots.plantings.create', [
            'grapeVarieties' => GrapeVariety::active()->orderBy('name')->get(),
            'trainingSystems' => TrainingSystem::where('active', true)->orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}


