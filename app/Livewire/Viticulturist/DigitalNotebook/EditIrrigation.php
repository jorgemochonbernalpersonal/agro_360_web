<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\AgriculturalActivity;
use App\Models\Irrigation;
use App\Models\Campaign;
use App\Models\Crew;
use App\Models\Machinery;
use App\Models\CrewMember;
use App\Livewire\Concerns\WithViticulturistValidation;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EditIrrigation extends Component
{
    use WithViticulturistValidation, WithToastNotifications;
    
    public AgriculturalActivity $activity;
    public Irrigation $irrigation;
    
    public $plot_id = '';
    public $plot_planting_id = '';
    public $availablePlantings = [];
    public $activity_date = '';
    public $phenological_stage = '';
    public $water_volume = '';
    public $irrigation_method = '';
    public $duration_minutes = '';
    public $soil_moisture_before = '';
    public $soil_moisture_after = '';
    public $water_source = '';
    public $water_concession = '';
    public $flow_rate = '';
    public $workType = '';
    public $crew_id = '';
    public $crew_member_id = '';
    public $machinery_id = '';
    public $weather_conditions = '';
    public $temperature = '';
    public $notes = '';
    public $campaign_id = '';

    public function mount(AgriculturalActivity $activity)
    {
        $this->activity = $activity->load(['irrigation', 'plot', 'plotPlanting', 'crew', 'crewMember']);
        
        if ($this->activity->activity_type !== 'irrigation') {
            $this->toastError('Esta actividad no es un riego.');
            return redirect()->route('viticulturist.digital-notebook');
        }
        
        if (!Auth::user()->can('update', $this->activity)) {
            abort(403, 'No tienes permiso para editar esta actividad.');
        }
        
        if ($this->activity->isLocked()) {
            $this->toastError(' No se puede editar una actividad bloqueada. Las actividades se bloquean automáticamente después de ' . config('activities.lock_days', 7) . ' días para cumplimiento PAC.');
            return redirect()->route('viticulturist.digital-notebook');
        }
        
        $this->irrigation = $this->activity->irrigation;
        
        $this->plot_id = $this->activity->plot_id;
        $this->plot_planting_id = $this->activity->plot_planting_id;
        $this->activity_date = \Carbon\Carbon::parse($this->activity->activity_date)->format('Y-m-d');
        $this->phenological_stage = $this->activity->phenological_stage;
        $this->campaign_id = $this->activity->campaign_id;
        $this->weather_conditions = $this->activity->weather_conditions;
        $this->temperature = $this->activity->temperature;
        $this->notes = $this->activity->notes;
        
        if ($this->activity->crew_id) {
            $this->workType = 'crew';
            $this->crew_id = $this->activity->crew_id;
        } elseif ($this->activity->crew_member_id) {
            $this->workType = 'individual';
            $crewMember = $this->activity->crewMember;
            $this->crew_member_id = $crewMember ? $crewMember->viticulturist_id : '';
        }
        
        $this->machinery_id = $this->activity->machinery_id;
        
        $this->water_volume = $this->irrigation->water_volume;
        $this->irrigation_method = $this->irrigation->irrigation_method;
        $this->duration_minutes = $this->irrigation->duration_minutes;
        $this->soil_moisture_before = $this->irrigation->soil_moisture_before;
        $this->soil_moisture_after = $this->irrigation->soil_moisture_after;
        $this->water_source = $this->irrigation->water_source;
        $this->water_concession = $this->irrigation->water_concession;
        $this->flow_rate = $this->irrigation->flow_rate;
        
        if ($this->plot_id) {
            $this->availablePlantings = PlotPlanting::where('plot_id', $this->plot_id)
                ->where('status', 'active')
                ->with('grapeVariety')
                ->orderBy('name')
                ->get();
        }
    }

    public function updatedPlotId($value)
    {
        $this->plot_planting_id = '';
        if ($value) {
            $this->availablePlantings = PlotPlanting::where('plot_id', $value)
                ->where('status', 'active')
                ->with('grapeVariety')
                ->orderBy('name')
                ->get();
        } else {
            $this->availablePlantings = [];
        }
    }

    protected function rules(): array
    {
        return [
            'plot_id' => 'required|exists:plots,id',
            'plot_planting_id' => [
                'nullable',
                'exists:plot_plantings,id',
                function ($attribute, $value, $fail) {
                    if ($this->plot_id) {
                        $plot = Plot::find($this->plot_id);
                        if ($plot && $plot->plantings()->where('status', 'active')->exists()) {
                            if (!$value) {
                                $fail('Debes seleccionar una plantación para esta parcela.');
                            } elseif (!PlotPlanting::where('id', $value)
                                ->where('plot_id', $this->plot_id)
                                ->exists()) {
                                $fail('La plantación seleccionada no pertenece a esta parcela.');
                            }
                        }
                    }
                },
            ],
            'campaign_id' => 'required|exists:campaigns,id',
            'activity_date' => 'required|date',
            'water_volume' => 'required|numeric|min:0.01|max:1000000',
            'irrigation_method' => 'nullable|string|max:50',
            'duration_minutes' => 'nullable|integer|min:0',
            'soil_moisture_before' => 'nullable|numeric|min:0|max:100',
            'soil_moisture_after' => 'nullable|numeric|min:0|max:100',
            'phenological_stage' => 'required|string|max:50',
            'water_source' => 'required|string|max:100',
            'water_concession' => 'required|string|max:100',
            'flow_rate' => 'required|numeric|min:0|max:100000',
            'crew_id' => 'nullable|exists:crews,id',
            'crew_member_id' => 'nullable|exists:crew_members,id',
            'machinery_id' => 'nullable|exists:machinery,id',
            'weather_conditions' => 'nullable|string|max:255',
            'temperature' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ];
    }

    public function update()
    {
        $this->validate();

        $user = Auth::user();

        if (!$this->workType) {
            $this->addError('workType', 'Debes seleccionar si el trabajo lo realizó un equipo completo o un viticultor individual.');
            return;
        }

        if ($this->workType === 'crew' && !$this->crew_id) {
            $this->addError('crew_id', 'Debes seleccionar un equipo.');
            return;
        }

        if ($this->workType === 'individual' && !$this->crew_member_id) {
            $this->addError('crew_member_id', 'Debes seleccionar un viticultor.');
            return;
        }

        $plot = $this->authorizeCreateActivityForPlot($this->plot_id);

        try {
            DB::transaction(function () use ($user) {
                $crewMemberId = null;
                
                if ($this->workType === 'individual' && $this->crew_member_id) {
                    $viticulturistId = $this->crew_member_id;
                    
                    $crewMember = CrewMember::firstOrCreate(
                        [
                            'viticulturist_id' => $viticulturistId,
                            'assigned_by' => $user->id,
                        ],
                        [
                            'crew_id' => null,
                        ]
                    );
                    
                    $crewMemberId = $crewMember->id;
                }
                
                $this->activity->update([
                    'plot_id' => $this->plot_id,
                    'plot_planting_id' => $this->plot_planting_id ?: null,
                    'campaign_id' => $this->campaign_id,
                    'phenological_stage' => $this->phenological_stage,
                    'activity_date' => $this->activity_date,
                    'crew_id' => $this->workType === 'crew' ? $this->crew_id : null,
                    'crew_member_id' => $crewMemberId,
                    'machinery_id' => $this->machinery_id ?: null,
                    'weather_conditions' => $this->weather_conditions,
                    'temperature' => $this->temperature ?: null,
                    'notes' => $this->notes,
                ]);

                $this->irrigation->update([
                    'water_volume' => $this->water_volume ?: null,
                    'irrigation_method' => $this->irrigation_method,
                    'duration_minutes' => $this->duration_minutes ?: null,
                    'soil_moisture_before' => $this->soil_moisture_before ?: null,
                    'soil_moisture_after' => $this->soil_moisture_after ?: null,
                    'water_source' => $this->water_source ?: null,
                    'water_concession' => $this->water_concession ?: null,
                    'flow_rate' => $this->flow_rate ?: null,
                ]);
            });

            $this->toastSuccess('Riego actualizado correctamente.');
            return redirect()->route('viticulturist.digital-notebook');
        } catch (\Exception $e) {
            \Log::error('Error al actualizar riego', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'activity_id' => $this->activity->id,
                'trace' => $e->getTraceAsString(),
            ]);

            $this->toastError('Error al actualizar el riego. Por favor, intenta de nuevo.');
            return;
        }
    }

    public function render()
    {
        $user = Auth::user();
        
        $plots = Plot::forUser($user)
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $crews = Crew::where('viticulturist_id', $user->id)
            ->orderBy('name')
            ->get();

        $machinery = Machinery::forViticulturist($user->id)
            ->active()
            ->orderBy('name')
            ->get();

        $individualWorkers = CrewMember::whereNull('crew_id')
            ->where('assigned_by', $user->id)
            ->with('viticulturist')
            ->get()
            ->sortBy(fn ($worker) => $worker->viticulturist->name)
            ->values();
        
        $allViticulturists = \App\Models\WineryViticulturist::editableBy($user)
            ->with('viticulturist')
            ->get()
            ->pluck('viticulturist')
            ->unique('id')
            ->sortBy('name')
            ->values();

        $campaign = Campaign::find($this->campaign_id);

        return view('livewire.viticulturist.digital-notebook.edit-irrigation', [
            'plots' => $plots,
            'crews' => $crews,
            'machinery' => $machinery,
            'campaign' => $campaign,
            'individualWorkers' => $individualWorkers,
            'allViticulturists' => $allViticulturists,
        ])->layout('layouts.app', [
            'title' => 'Editar Riego - Agro365',
        ]);
    }
}
