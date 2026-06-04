<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

use App\Models\AgriculturalActivity;
use App\Models\CulturalWork;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EditPruning extends AbstractActivityForm
{
    // ─── Model instances ──────────────────────────────────────────────────────

    public AgriculturalActivity $activity;

    public CulturalWork $culturalWork;

    // ─── Type-specific properties ─────────────────────────────────────────────

    public $pruning_type = '';

    public $productive_buds_per_hectare = '';

    public $residue_management = '';

    public $hours_worked = '';

    public $workers_count = '';

    public $description = '';

    // ─── Mount ────────────────────────────────────────────────────────────────

    public function mount(AgriculturalActivity $activity): void
    {
        $this->activity = $activity->load(['culturalWork', 'plot', 'plotPlanting', 'crew', 'crewMember']);

        if (! $this->mountEditGuards($activity, 'pruning', 'digital-notebook.pruning.index')) {
            return;
        }

        $this->culturalWork = $this->activity->culturalWork;
        $this->loadActivityFields($this->activity);
        $this->loadAvailablePlantings();

        $this->pruning_type = $this->culturalWork->pruning_type ?? '';
        $this->productive_buds_per_hectare = $this->culturalWork->productive_buds_per_hectare ?? '';
        $this->residue_management = $this->culturalWork->residue_management ?? '';
        $this->hours_worked = $this->culturalWork->hours_worked ?? '';
        $this->workers_count = $this->culturalWork->workers_count ?? '';
        $this->description = $this->culturalWork->description;
    }

    // ─── Update ───────────────────────────────────────────────────────────────

    public function update(): mixed
    {
        $this->validate();
        $this->authorizeCreateActivityForPlot($this->plot_id);

        try {
            DB::transaction(function () {
                $this->activity->update($this->activityData('pruning'));

                $this->culturalWork->update([
                    'pruning_type' => $this->pruning_type,
                    'productive_buds_per_hectare' => $this->productive_buds_per_hectare ?: null,
                    'residue_management' => $this->residue_management ?: null,
                    'hours_worked' => $this->hours_worked ?: null,
                    'workers_count' => $this->workers_count ?: null,
                    'description' => $this->description,
                ]);
            });

            $this->toastSuccess(__('Poda actualizada correctamente.'));

            return $this->viticulturistRoleRedirect('digital-notebook.pruning.index');
        } catch (\Exception $e) {
            \Log::error('Error al actualizar poda', ['error' => $e->getMessage(), 'user_id' => Auth::id(), 'activity_id' => $this->activity->id]);
            $this->toastError(__('Error al actualizar la poda. Por favor, intenta de nuevo.'));
        }

        return null;
    }

    // ─── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.viticulturist.digital-notebook.edit-pruning', $this->renderData())
            ->layout('layouts.app', ['title' => __('Editar Poda - Agro365')]);
    }

    // ─── Validation ───────────────────────────────────────────────────────────

    protected function rules(): array
    {
        return array_merge($this->commonRules(), [
            'pruning_type' => 'required|in:guyot,doble_guyot,vaso,cordon,otro',
            'productive_buds_per_hectare' => 'nullable|integer|min:0',
            'residue_management' => 'nullable|string|in:triturado_incorporado,triturado_superficie,retirado,quemado,otro',
            'hours_worked' => 'nullable|numeric|min:0',
            'workers_count' => 'nullable|integer|min:1',
            'description' => 'required|string|min:10',
        ]);
    }
}
