<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

use App\Models\AgriculturalActivity;
use App\Models\Observation;
use App\Models\Pest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateObservation extends AbstractActivityForm
{
    // ─── Type-specific properties ─────────────────────────────────────────────

    public $observation_type        = '';
    public $description             = '';
    public $severity                = '';
    public $action_taken            = '';
    public $affected_area_percentage = '';
    public $threshold_exceeded      = false;
    public $follow_up_date          = '';
    public $pest_id                 = '';
    public $selectedPest            = null;

    // ─── Mount ────────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $this->mountCreate();

        // Pre-select pest from URL query param (e.g. from pest-management detail page)
        $this->pest_id = request()->query('pest_id', '');
        if ($this->pest_id) {
            $this->selectedPest = Pest::find($this->pest_id);
            if ($this->selectedPest) {
                $this->observation_type = in_array($this->selectedPest->type, ['disease', 'enfermedad'])
                    ? 'enfermedad'
                    : 'plaga';
            }
        }
    }

    // ─── Reactive ─────────────────────────────────────────────────────────────

    public function updatedPestId($value): void
    {
        $this->selectedPest = $value ? Pest::find($value) : null;
    }

    // ─── Validation ───────────────────────────────────────────────────────────

    protected function rules(): array
    {
        return array_merge($this->commonRules(), [
            'observation_type'        => 'required|in:plaga,enfermedad,fenología,climatología,suelo,otro',
            'description'             => 'required|string',
            'severity'                => 'nullable|string|in:leve,moderada,grave',
            'affected_area_percentage'=> 'nullable|numeric|min:0|max:100',
            'threshold_exceeded'      => 'boolean',
            'follow_up_date'          => 'nullable|date|after_or_equal:activity_date',
            'action_taken'            => 'nullable|string',
            'pest_id'                 => 'nullable|exists:pests,id',
        ]);
    }

    // ─── Save ─────────────────────────────────────────────────────────────────

    public function save(): mixed
    {
        $this->validate();
        $this->authorizeCreateActivityForPlot($this->plot_id);

        try {
            DB::transaction(function () {
                $activity = AgriculturalActivity::create($this->activityData('observation'));

                Observation::create([
                    'activity_id'              => $activity->id,
                    'pest_id'                  => $this->pest_id ?: null,
                    'observation_type'         => $this->observation_type,
                    'description'              => $this->description,
                    'severity'                 => $this->severity ?: null,
                    'affected_area_percentage' => $this->affected_area_percentage ?: null,
                    'threshold_exceeded'       => (bool) $this->threshold_exceeded,
                    'follow_up_date'           => $this->follow_up_date ?: null,
                    'action_taken'             => $this->action_taken,
                    'photos'                   => null,
                ]);
            });

            $this->toastSuccess('Observación registrada correctamente.');
            return $this->viticulturistRoleRedirect('digital-notebook.observation.index');
        } catch (\Exception $e) {
            \Log::error('Error al registrar observación', ['error' => $e->getMessage(), 'user_id' => Auth::id()]);
            $this->toastError('Error al registrar la observación. Por favor, intenta de nuevo.');
        }
    }

    // ─── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.viticulturist.digital-notebook.create-observation', $this->renderData())
            ->layout('layouts.app');
    }
}
