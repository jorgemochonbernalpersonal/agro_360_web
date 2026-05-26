<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

use App\Models\AgriculturalActivity;
use App\Models\Observation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EditObservation extends AbstractActivityForm
{
    // ─── Model instances ──────────────────────────────────────────────────────

    public AgriculturalActivity $activity;
    public Observation          $observation;

    // ─── Type-specific properties ─────────────────────────────────────────────

    public $observation_type         = '';
    public $description              = '';
    public $severity                 = '';
    public $action_taken             = '';
    public $affected_area_percentage = '';
    public $threshold_exceeded       = false;
    public $follow_up_date           = '';

    // ─── Mount ────────────────────────────────────────────────────────────────

    public function mount(AgriculturalActivity $activity): void
    {
        $this->activity = $activity->load(['observation', 'plot', 'plotPlanting', 'crew', 'crewMember']);

        if (!$this->mountEditGuards($activity, 'observation', 'digital-notebook.observation.index')) {
            return;
        }

        $this->observation = $this->activity->observation;
        $this->loadActivityFields($this->activity);
        $this->loadAvailablePlantings();

        $this->observation_type          = $this->observation->observation_type;
        $this->description               = $this->observation->description;
        $this->severity                  = $this->observation->severity ?? '';
        $this->affected_area_percentage  = $this->observation->affected_area_percentage ?? '';
        $this->threshold_exceeded        = (bool) $this->observation->threshold_exceeded;
        $this->follow_up_date            = $this->observation->follow_up_date
            ? Carbon::parse($this->observation->follow_up_date)->format('Y-m-d') : '';
        $this->action_taken              = $this->observation->action_taken ?? '';
    }

    // ─── Validation ───────────────────────────────────────────────────────────

    protected function rules(): array
    {
        return array_merge($this->commonRules(), [
            'observation_type'         => 'required|in:plaga,enfermedad,fenología,climatología,suelo,otro',
            'description'              => 'required|string',
            'severity'                 => 'nullable|string|in:leve,moderada,grave',
            'affected_area_percentage' => 'nullable|numeric|min:0|max:100',
            'threshold_exceeded'       => 'boolean',
            'follow_up_date'           => 'nullable|date|after_or_equal:activity_date',
            'action_taken'             => 'nullable|string',
        ]);
    }

    // ─── Update ───────────────────────────────────────────────────────────────

    public function update(): mixed
    {
        $this->validate();
        $this->authorizeCreateActivityForPlot($this->plot_id);

        try {
            DB::transaction(function () {
                $this->activity->update($this->activityData('observation'));

                $this->observation->update([
                    'observation_type'         => $this->observation_type,
                    'description'              => $this->description,
                    'severity'                 => $this->severity ?: null,
                    'affected_area_percentage' => $this->affected_area_percentage ?: null,
                    'threshold_exceeded'       => (bool) $this->threshold_exceeded,
                    'follow_up_date'           => $this->follow_up_date ?: null,
                    'action_taken'             => $this->action_taken,
                ]);
            });

            $this->toastSuccess(__('Observación actualizada correctamente.'));
            return $this->viticulturistRoleRedirect('digital-notebook.observation.index');
        } catch (\Exception $e) {
            \Log::error('Error al actualizar observación', ['error' => $e->getMessage(), 'user_id' => Auth::id(), 'activity_id' => $this->activity->id]);
            $this->toastError(__('Error al actualizar la observación. Por favor, intenta de nuevo.'));
        }
    }

    // ─── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.viticulturist.digital-notebook.edit-observation', $this->renderData())
            ->layout('layouts.app', ['title' => __('Editar Observación - Agro365')]);
    }
}
