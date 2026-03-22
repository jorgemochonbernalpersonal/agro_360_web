<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\AgriculturalActivity;
use App\Models\CulturalWork;
use App\Models\Campaign;
use App\Models\Crew;
use App\Models\Machinery;
use App\Models\CrewMember;
use App\Livewire\Concerns\WithViticulturistValidation;
use App\Livewire\Concerns\WithToastNotifications;
use App\Livewire\Concerns\WithUserFilters;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app', ['title' => 'Registrar Poda - Agro365'])]
class CreatePruning extends Component
{
    use WithViticulturistValidation, WithToastNotifications, WithUserFilters;

    public $plot_id = '';
    public $plot_planting_id = '';
    public $availablePlantings = [];
    public $activity_date = '';
    public $pruning_type = '';
    public $productive_buds_per_hectare = '';
    public $residue_management = '';
    public $hours_worked = '';
    public $workers_count = '';
    public $description = '';
    public $phenological_stage = 'Reposo invernal';
    public $workType = '';
    public $crew_id = '';
    public $crew_member_id = '';
    public $machinery_id = '';
    public $weather_conditions = '';
    public $temperature = '';
    public $notes = '';
    public $campaign_id = '';

    public function mount()
    {
        $this->authorizeCreateActivity();
        $this->activity_date = now()->format('Y-m-d');

        $campaign = Campaign::getOrCreateActiveForYear(Auth::id());
        if (!$campaign) {
            $this->toastError('No se pudo obtener la campaña activa. Por favor, crea una campaña primero.');
            return $this->redirect(route('viticulturist.campaign.create'), navigate: true);
        }
        $this->campaign_id = $campaign->id;
    }

    public function updatedPlotId($value)
    {
        $this->plot_planting_id = '';
        $this->availablePlantings = $value
            ? PlotPlanting::where('plot_id', $value)->where('status', 'active')->with('grapeVariety')->orderBy('name')->get()
            : [];
    }

    protected function rules(): array
    {
        return [
            'plot_id' => $this->plotOwnershipRule(),
            'plot_planting_id' => [
                'nullable',
                'exists:plot_plantings,id',
                function ($attribute, $value, $fail) {
                    if ($this->plot_id) {
                        $plot = Plot::find($this->plot_id);
                        if ($plot && $plot->plantings()->where('status', 'active')->exists()) {
                            if (!$value) {
                                $fail('Debes seleccionar una plantación para esta parcela.');
                            } elseif (!PlotPlanting::where('id', $value)->where('plot_id', $this->plot_id)->exists()) {
                                $fail('La plantación seleccionada no pertenece a esta parcela.');
                            }
                        }
                    }
                },
            ],
            'campaign_id'                 => 'required|exists:campaigns,id',
            'activity_date'               => 'required|date',
            'pruning_type'                => 'required|string|max:50',
            'productive_buds_per_hectare' => 'nullable|integer|min:0',
            'residue_management'          => 'nullable|string|in:triturado_incorporado,triturado_superficie,retirado,quemado,otro',
            'hours_worked'                => 'nullable|numeric|min:0',
            'workers_count'               => 'nullable|integer|min:1',
            'description'                 => 'required|string|min:10',
            'phenological_stage'          => 'required|string|max:50',
            'workType'                    => 'required|in:crew,individual',
            'crew_id'                     => 'required_if:workType,crew|nullable|exists:crews,id',
            'crew_member_id'              => 'required_if:workType,individual|nullable|exists:users,id',
            'machinery_id'                => 'nullable|exists:machinery,id',
            'weather_conditions'          => 'nullable|string|max:255',
            'temperature'                 => 'nullable|numeric',
            'notes'                       => 'nullable|string',
        ];
    }

    public function save()
    {
        $this->validate();
        $user = Auth::user();

        if ($this->workType === 'crew' && !$this->crew_id) {
            $this->addError('crew_id', 'Debes seleccionar un equipo.');
            return;
        }
        if ($this->workType === 'individual' && !$this->crew_member_id) {
            $this->addError('crew_member_id', 'Debes seleccionar un viticultor.');
            return;
        }

        $this->authorizeCreateActivityForPlot($this->plot_id);

        try {
            DB::transaction(function () use ($user) {
                $crewMemberId = null;
                if ($this->workType === 'individual' && $this->crew_member_id) {
                    $crewMember = CrewMember::firstOrCreate(
                        ['viticulturist_id' => $this->crew_member_id, 'assigned_by' => $user->id],
                        ['crew_id' => null]
                    );
                    $crewMemberId = $crewMember->id;
                }

                $activity = AgriculturalActivity::create([
                    'plot_id'            => $this->plot_id,
                    'plot_planting_id'   => $this->plot_planting_id ?: null,
                    'viticulturist_id'   => $user->id,
                    'campaign_id'        => $this->campaign_id,
                    'activity_type'      => 'pruning',
                    'phenological_stage' => $this->phenological_stage,
                    'activity_date'      => $this->activity_date,
                    'crew_id'            => $this->workType === 'crew' ? $this->crew_id : null,
                    'crew_member_id'     => $crewMemberId,
                    'machinery_id'       => $this->machinery_id ?: null,
                    'weather_conditions' => $this->weather_conditions,
                    'temperature'        => $this->temperature ?: null,
                    'notes'              => $this->notes,
                ]);

                CulturalWork::create([
                    'activity_id'                 => $activity->id,
                    'work_type'                   => 'poda',
                    'pruning_type'                => $this->pruning_type,
                    'productive_buds_per_hectare' => $this->productive_buds_per_hectare ?: null,
                    'residue_management'          => $this->residue_management ?: null,
                    'hours_worked'                => $this->hours_worked ?: null,
                    'workers_count'               => $this->workers_count ?: null,
                    'description'                 => $this->description,
                ]);
            });

            $this->toastSuccess('Poda registrada correctamente.');
            return $this->redirect(route('viticulturist.digital-notebook.pruning.index'), navigate: true);
        } catch (\Exception $e) {
            \Log::error('Error al registrar poda', ['error' => $e->getMessage(), 'user_id' => Auth::id()]);
            $this->toastError('Error al registrar la poda. Por favor, intenta de nuevo.');
        }
    }

    public function render()
    {
        $user = Auth::user();
        return view('livewire.viticulturist.digital-notebook.create-pruning', [
            'plots'             => Plot::forUser($user)->where('active', true)->orderBy('name')->get(),
            'crews'             => Crew::where('viticulturist_id', $user->id)->orderBy('name')->get(),
            'machinery'         => Machinery::forViticulturist($user->id)->active()->orderBy('name')->get(),
            'campaign'          => Campaign::find($this->campaign_id),
            'allViticulturists' => $this->viticulturists,
        ]);
    }
}
