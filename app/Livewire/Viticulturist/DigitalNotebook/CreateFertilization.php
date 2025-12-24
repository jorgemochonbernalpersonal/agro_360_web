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
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateFertilization extends Component
{
    use WithViticulturistValidation, WithToastNotifications;
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
    public $workType = ''; // 'crew' o 'individual'
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

    public function mount()
    {
        // Validar autorización
        $this->authorizeCreateActivity();
        
        $this->activity_date = now()->format('Y-m-d');
        
        // Obtener o crear campaña activa del año actual
        $user = Auth::user();
        $campaign = Campaign::getOrCreateActiveForYear($user->id);
        
        if (!$campaign) {
            // Si no se pudo obtener/crear campaña, redirigir
            $this->toastError('No se pudo obtener la campaña activa. Por favor, crea una campaña primero.');
            return redirect()->route('viticulturist.campaign.create');
        }
        
        $this->campaign_id = $campaign->id;
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
            'fertilizer_type' => 'nullable|string|max:100',
            'fertilizer_name' => 'nullable|string|max:255',
            'quantity' => 'nullable|numeric|min:0',
            'npk_ratio' => 'nullable|string|max:50',
            'application_method' => 'nullable|string|max:50',
            'area_applied' => 'nullable|numeric|min:0',
            'crew_id' => 'nullable|exists:crews,id',
            'crew_member_id' => 'nullable|exists:crew_members,id',
            'machinery_id' => 'nullable|exists:machinery,id',
            'weather_conditions' => 'nullable|string|max:255',
            'temperature' => 'nullable|numeric',
            'notes' => 'nullable|string',
            // PAC Nutrición
            'nitrogen_uf' => 'nullable|numeric|min:0',
            'phosphorus_uf' => 'nullable|numeric|min:0',
            'potassium_uf' => 'nullable|numeric|min:0',
            'manure_type' => 'nullable|string|max:100',
            'burial_date' => 'nullable|date',
            'emission_reduction_method' => 'nullable|string|max:100',
        ];
    }

    public function save()
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

        // Validar que la parcela pertenece al viticultor usando el trait
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
                
                // Crear la actividad base
                $activity = AgriculturalActivity::create([
                    'plot_id' => $this->plot_id,
                    'plot_planting_id' => $this->plot_planting_id ?: null,
                    'viticulturist_id' => $user->id,
                    'campaign_id' => $this->campaign_id,
                    'activity_type' => 'fertilization',
                    'activity_date' => $this->activity_date,
                    'crew_id' => $this->workType === 'crew' ? $this->crew_id : null,
                    'crew_member_id' => $crewMemberId,
                    'machinery_id' => $this->machinery_id ?: null,
                    'weather_conditions' => $this->weather_conditions,
                    'temperature' => $this->temperature ?: null,
                    'notes' => $this->notes,
                ]);

                // Crear la fertilización
                Fertilization::create([
                    'activity_id' => $activity->id,
                    'fertilizer_type' => $this->fertilizer_type,
                    'fertilizer_name' => $this->fertilizer_name,
                    'quantity' => $this->quantity ?: null,
                    'npk_ratio' => $this->npk_ratio,
                    'application_method' => $this->application_method,
                    'area_applied' => $this->area_applied ?: null,
                    // PAC
                    'nitrogen_uf' => $this->nitrogen_uf ?: null,
                    'phosphorus_uf' => $this->phosphorus_uf ?: null,
                    'potassium_uf' => $this->potassium_uf ?: null,
                    'manure_type' => $this->manure_type,
                    'burial_date' => $this->burial_date ?: null,
                    'emission_reduction_method' => $this->emission_reduction_method,
                ]);
            });

            $this->toastSuccess('Fertilización registrada correctamente.');
            return redirect()->route('viticulturist.digital-notebook');
        } catch (\Exception $e) {
            \Log::error('Error al registrar fertilización', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'plot_id' => $this->plot_id,
                'trace' => $e->getTraceAsString(),
            ]);

            $this->toastError('Error al registrar la fertilización. Por favor, intenta de nuevo.');
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
        
        // También obtener todos los viticultores visibles para selección individual
        $allViticulturists = \App\Models\WineryViticulturist::editableBy($user)
            ->with('viticulturist')
            ->get()
            ->pluck('viticulturist')
            ->unique('id')
            ->sortBy('name')
            ->values();

        $campaign = Campaign::find($this->campaign_id);

        return view('livewire.viticulturist.digital-notebook.create-fertilization', [
            'plots' => $plots,
            'crews' => $crews,
            'machinery' => $machinery,
            'campaign' => $campaign,
            'individualWorkers' => $individualWorkers,
            'allViticulturists' => $allViticulturists,
        ])->layout('layouts.app');
    }
}

