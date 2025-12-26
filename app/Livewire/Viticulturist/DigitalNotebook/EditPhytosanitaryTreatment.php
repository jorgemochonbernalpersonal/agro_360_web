<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\AgriculturalActivity;
use App\Models\PhytosanitaryProduct;
use App\Models\PhytosanitaryTreatment;
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

class EditPhytosanitaryTreatment extends Component
{
    use WithViticulturistValidation, WithToastNotifications, WithUserFilters;
    
    public AgriculturalActivity $activity;
    public PhytosanitaryTreatment $treatment;
    
    public $plot_id = '';
    public $plot_planting_id = '';
    public $availablePlantings = [];
    public $activity_date = '';
    public $phenological_stage = '';
    public $product_id = '';
    public $dose_per_hectare = '';
    public $total_dose = '';
    public $area_treated = '';
    public $application_method = '';
    public $pest_id = '';
    public $workType = ''; // 'crew' o 'individual'
    public $crew_id = '';
    public $crew_member_id = '';
    public $machinery_id = '';
    public $weather_conditions = '';
    public $temperature = '';
    public $wind_speed = '';
    public $humidity = '';
    public $notes = '';
    public $campaign_id = '';
    
    // Campos PAC obligatorios
    public $treatment_justification = '';
    public $applicator_ropo_number = '';
    public $reentry_period_days = '';
    public $spray_volume = '';

    public function mount(AgriculturalActivity $activity)
    {
        // Cargar la actividad con sus relaciones
        $this->activity = $activity->load(['phytosanitaryTreatment', 'plot', 'plotPlanting', 'crew', 'crewMember']);
        
        // Validar que sea un tratamiento fitosanitario
        if ($this->activity->activity_type !== 'phytosanitary') {
            $this->toastError('Esta actividad no es un tratamiento fitosanitario.');
            return redirect()->route('viticulturist.digital-notebook');
        }
        
        // Validar autorización
        if (!Auth::user()->can('update', $this->activity)) {
            abort(403, 'No tienes permiso para editar esta actividad.');
        }
        
        // Verificar si está bloqueada
        if ($this->activity->isLocked()) {
            $this->toastError('🔒 No se puede editar una actividad bloqueada. Las actividades se bloquean automáticamente después de ' . config('activities.lock_days', 7) . ' días para cumplimiento PAC.');
            return redirect()->route('viticulturist.digital-notebook');
        }
        
        $this->treatment = $this->activity->phytosanitaryTreatment;
        
        // Cargar datos de la actividad
        $this->plot_id = $this->activity->plot_id;
        $this->plot_planting_id = $this->activity->plot_planting_id;
        $this->activity_date = \Carbon\Carbon::parse($this->activity->activity_date)->format('Y-m-d');
        $this->phenological_stage = $this->activity->phenological_stage;
        $this->campaign_id = $this->activity->campaign_id;
        $this->weather_conditions = $this->activity->weather_conditions;
        $this->temperature = $this->activity->temperature;
        $this->notes = $this->activity->notes;
        
        // Determinar tipo de trabajo
        if ($this->activity->crew_id) {
            $this->workType = 'crew';
            $this->crew_id = $this->activity->crew_id;
        } elseif ($this->activity->crew_member_id) {
            $this->workType = 'individual';
            $crewMember = $this->activity->crewMember;
            $this->crew_member_id = $crewMember ? $crewMember->viticulturist_id : '';
        }
        
        $this->machinery_id = $this->activity->machinery_id;
        
        // Cargar datos del tratamiento
        $this->product_id = $this->treatment->product_id;
        $this->dose_per_hectare = $this->treatment->dose_per_hectare;
        $this->total_dose = $this->treatment->total_dose;
        $this->area_treated = $this->treatment->area_treated;
        $this->application_method = $this->treatment->application_method;
        $this->pest_id = $this->treatment->pest_id;
        $this->wind_speed = $this->treatment->wind_speed;
        $this->humidity = $this->treatment->humidity;
        $this->treatment_justification = $this->treatment->treatment_justification;
        $this->applicator_ropo_number = $this->treatment->applicator_ropo_number;
        $this->reentry_period_days = $this->treatment->reentry_period_days;
        $this->spray_volume = $this->treatment->spray_volume;
        
        // Cargar plantaciones disponibles
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
            'phenological_stage' => 'required|string|max:50',
            'product_id' => 'required|exists:phytosanitary_products,id',
            'dose_per_hectare' => 'required|numeric|min:0.01|max:100',
            'total_dose' => 'nullable|numeric|min:0',
            'area_treated' => 'required|numeric|min:0.01',
            'application_method' => 'nullable|string|max:50',
            'pest_id' => 'nullable|exists:pests,id',
            'crew_id' => 'nullable|exists:crews,id',
            'crew_member_id' => 'nullable|exists:crew_members,id',
            'machinery_id' => 'nullable|exists:machinery,id',
            'weather_conditions' => 'nullable|string|max:255',
            'temperature' => 'nullable|numeric',
            'wind_speed' => 'nullable|numeric|min:0',
            'humidity' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
            // Campos PAC obligatorios (RD 1311/2012)
            'treatment_justification' => 'required|string|min:10|max:500',
            'applicator_ropo_number' => 'required|string|max:50',
            'reentry_period_days' => 'required|integer|min:0|max:365',
            'spray_volume' => 'required|numeric|min:0.01|max:10000',
        ];
    }

    public function updatedAreaTreated()
    {
        if ($this->area_treated && $this->dose_per_hectare) {
            $this->total_dose = $this->area_treated * $this->dose_per_hectare;
        }
    }

    public function updatedDosePerHectare()
    {
        if ($this->area_treated && $this->dose_per_hectare) {
            $this->total_dose = $this->area_treated * $this->dose_per_hectare;
        }
    }

    public function getSelectedProductProperty()
    {
        if (!$this->product_id) {
            return null;
        }
        return PhytosanitaryProduct::find($this->product_id);
    }

    public function update()
    {
        $this->validate();

        $user = Auth::user();

        // Validar que se haya seleccionado un tipo de trabajo
        if (!$this->workType) {
            $this->addError('workType', 'Debes seleccionar si el trabajo lo realizó un equipo completo o un viticultor individual.');
            return;
        }

        // Validar según el tipo seleccionado
        if ($this->workType === 'crew' && !$this->crew_id) {
            $this->addError('crew_id', 'Debes seleccionar un equipo.');
            return;
        }

        if ($this->workType === 'individual' && !$this->crew_member_id) {
            $this->addError('crew_member_id', 'Debes seleccionar un viticultor.');
            return;
        }

        // Validar que la parcela pertenece al viticultor
        $plot = $this->authorizeCreateActivityForPlot($this->plot_id);

        try {
            DB::transaction(function () use ($user) {
                $crewMemberId = null;
                
                // Si es trabajo individual, buscar o crear CrewMember
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
                
                // Actualizar la actividad base
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

                // Actualizar el tratamiento fitosanitario
                $this->treatment->update([
                    'product_id' => $this->product_id,
                    'dose_per_hectare' => $this->dose_per_hectare ?: null,
                    'total_dose' => $this->total_dose ?: null,
                    'area_treated' => $this->area_treated ?: null,
                    'application_method' => $this->application_method,
                    'pest_id' => $this->pest_id ?: null,
                    'wind_speed' => $this->wind_speed ?: null,
                    'humidity' => $this->humidity ?: null,
                    'treatment_justification' => $this->treatment_justification,
                    'applicator_ropo_number' => $this->applicator_ropo_number ?: null,
                    'reentry_period_days' => $this->reentry_period_days,
                    'spray_volume' => $this->spray_volume,
                ]);
            });

            $this->toastSuccess('Tratamiento fitosanitario actualizado correctamente.');
            return redirect()->route('viticulturist.digital-notebook');
        } catch (\Exception $e) {
            \Log::error('Error al actualizar tratamiento fitosanitario', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'activity_id' => $this->activity->id,
                'trace' => $e->getTraceAsString(),
            ]);

            $this->toastError('Error al actualizar el tratamiento fitosanitario. Por favor, intenta de nuevo.');
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

        $products = PhytosanitaryProduct::orderBy('name')->get();

        $pests = \App\Models\Pest::active()->orderBy('name')->get();

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

        return view('livewire.viticulturist.digital-notebook.edit-phytosanitary-treatment', [
            'plots' => $plots,
            'products' => $products,
            'pests' => $pests,
            'crews' => $crews,
            'machinery' => $machinery,
            'campaign' => $campaign,
            'individualWorkers' => $individualWorkers,
            'allViticulturists' => $allViticulturists,
        ])->layout('layouts.app', [
            'title' => 'Editar Tratamiento Fitosanitario - Agro365',
        ]);
    }
}
