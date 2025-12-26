<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\AgriculturalActivity;
use App\Models\Fertilization;
use App\Models\Campaign;
use App\Models\Crew;
use App\Models\Machinery;
use App\Models\CrewMember;
use App\Livewire\Concerns\WithViticulturistValidation;
use App\Livewire\Concerns\WithToastNotifications;
use App\Livewire\Concerns\WithUserFilters;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EditFertilization extends Component
{
    use WithViticulturistValidation, WithToastNotifications, WithUserFilters;
    
    public AgriculturalActivity $activity;
    public Fertilization $fertilization;
    
    public $plot_id = '';
    public $plot_planting_id = '';
    public $availablePlantings = [];
    public $activity_date = '';
    public $fertilizer_type = '';
    public $fertilizer_name = '';
    public $quantity = '';
    public $npk_ratio = '';
    public $application_method = '';
    public $area_applied = '';
    public $phenological_stage = '';
    public $workType = '';
    public $crew_id = '';
    public $crew_member_id = '';
    public $machinery_id = '';
    public $weather_conditions = '';
    public $temperature = '';
    public $notes = '';
    public $campaign_id = '';
    
    // PAC Nutrición
    public $nitrogen_uf = '';
    public $phosphorus_uf = '';
    public $potassium_uf = '';
    public $manure_type = '';
    public $burial_date = '';
    public $emission_reduction_method = '';

    public function mount(AgriculturalActivity $activity)
    {
        $this->activity = $activity->load(['fertilization', 'plot', 'plotPlanting', 'crew', 'crewMember']);
        
        if ($this->activity->activity_type !== 'fertilization') {
            $this->toastError('Esta actividad no es una fertilización.');
            return redirect()->route('viticulturist.digital-notebook');
        }
        
        if (!Auth::user()->can('update', $this->activity)) {
            abort(403, 'No tienes permiso para editar esta actividad.');
        }
        
        if ($this->activity->isLocked()) {
            $this->toastError('🔒 No se puede editar una actividad bloqueada. Las actividades se bloquean automáticamente después de ' . config('activities.lock_days', 7) . ' días para cumplimiento PAC.');
            return redirect()->route('viticulturist.digital-notebook');
        }
        
        $this->fertilization = $this->activity->fertilization;
        
        // Cargar datos de la actividad
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
        
        // Cargar datos de la fertilización
        $this->fertilizer_type = $this->fertilization->fertilizer_type;
        $this->fertilizer_name = $this->fertilization->fertilizer_name;
        $this->quantity = $this->fertilization->quantity;
        $this->npk_ratio = $this->fertilization->npk_ratio;
        $this->application_method = $this->fertilization->application_method;
        $this->area_applied = $this->fertilization->area_applied;
        $this->nitrogen_uf = $this->fertilization->nitrogen_uf;
        $this->phosphorus_uf = $this->fertilization->phosphorus_uf;
        $this->potassium_uf = $this->fertilization->potassium_uf;
        $this->manure_type = $this->fertilization->manure_type;
        $this->burial_date = $this->fertilization->burial_date ? \Carbon\Carbon::parse($this->fertilization->burial_date)->format('Y-m-d') : '';
        $this->emission_reduction_method = $this->fertilization->emission_reduction_method;
        
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
            'fertilizer_type' => 'required|string|max:100',
            'fertilizer_name' => 'nullable|string|max:255',
            'quantity' => 'required|numeric|min:0.01',
            'npk_ratio' => 'nullable|string|max:50',
            'application_method' => 'nullable|string|max:50',
            'area_applied' => 'required|numeric|min:0.01',
            'phenological_stage' => 'required|string|max:50',
            'crew_id' => 'nullable|exists:crews,id',
            'crew_member_id' => 'nullable|exists:crew_members,id',
            'machinery_id' => 'nullable|exists:machinery,id',
            'weather_conditions' => 'nullable|string|max:255',
            'temperature' => 'nullable|numeric',
            'notes' => 'nullable|string',
            'nitrogen_uf' => 'required_without_all:phosphorus_uf,potassium_uf|nullable|numeric|min:0|max:1000',
            'phosphorus_uf' => 'required_without_all:nitrogen_uf,potassium_uf|nullable|numeric|min:0|max:1000',
            'potassium_uf' => 'required_without_all:nitrogen_uf,phosphorus_uf|nullable|numeric|min:0|max:1000',
            'manure_type' => 'nullable|string|max:100',
            'burial_date' => 'nullable|date|before_or_equal:today',
            'emission_reduction_method' => 'nullable|string|max:100',
        ];
    }

    public function update()
    {
        $this->validate();
        
        if ($this->fertilizer_type && 
            (str_contains(strtolower($this->fertilizer_type), 'orgánico') || 
             str_contains(strtolower($this->fertilizer_type), 'organico') ||
             str_contains(strtolower($this->fertilizer_type), 'estiércol') ||
             str_contains(strtolower($this->fertilizer_type), 'estiercol'))) {
            
            $this->validate([
                'manure_type' => 'required|string|max:100',
                'burial_date' => 'required|date|before_or_equal:today',
            ], [
                'manure_type.required' => 'El tipo de estiércol es obligatorio para fertilizantes orgánicos (BCAM 6).',
                'burial_date.required' => 'La fecha de enterrado es obligatoria para fertilizantes orgánicos (BCAM 6).',
            ]);
        }

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

                $this->fertilization->update([
                    'fertilizer_type' => $this->fertilizer_type,
                    'fertilizer_name' => $this->fertilizer_name,
                    'quantity' => $this->quantity ?: null,
                    'npk_ratio' => $this->npk_ratio,
                    'application_method' => $this->application_method,
                    'area_applied' => $this->area_applied ?: null,
                    'nitrogen_uf' => $this->nitrogen_uf ?: null,
                    'phosphorus_uf' => $this->phosphorus_uf ?: null,
                    'potassium_uf' => $this->potassium_uf ?: null,
                    'manure_type' => $this->manure_type,
                    'burial_date' => $this->burial_date ?: null,
                    'emission_reduction_method' => $this->emission_reduction_method,
                ]);
            });

            $this->toastSuccess('Fertilización actualizada correctamente.');
            return redirect()->route('viticulturist.digital-notebook');
        } catch (\Exception $e) {
            \Log::error('Error al actualizar fertilización', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'activity_id' => $this->activity->id,
                'trace' => $e->getTraceAsString(),
            ]);

            $this->toastError('Error al actualizar la fertilización. Por favor, intenta de nuevo.');
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
        
        // SIEMPRE incluir al usuario mismo al principio
        $allViticulturists = $this->viticulturists;

        $campaign = Campaign::find($this->campaign_id);

        return view('livewire.viticulturist.digital-notebook.edit-fertilization', [
            'plots' => $plots,
            'crews' => $crews,
            'machinery' => $machinery,
            'campaign' => $campaign,
            'individualWorkers' => $individualWorkers,
            'allViticulturists' => $allViticulturists,
        ])->layout('layouts.app', [
            'title' => 'Editar Fertilización - Agro365',
        ]);
    }
}
