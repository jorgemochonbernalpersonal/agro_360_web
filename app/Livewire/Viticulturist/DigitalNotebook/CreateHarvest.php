<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

use App\Livewire\Concerns\WithHarvestControlPanel;
use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Livewire\Concerns\WithUserFilters;
use App\Livewire\Concerns\WithViticulturistValidation;
use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\Container;
use App\Models\Crew;
use App\Models\CrewMember;
use App\Models\Harvest;
use App\Models\Machinery;
use App\Models\PhenologyObservation;
use App\Models\Plot;
use App\Models\PlotPlanting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * @property-read mixed $viticulturists
 */
#[Layout('layouts.app')]
class CreateHarvest extends Component
{
    use WithHarvestControlPanel, WithRoleAwareRedirect, WithToastNotifications, WithUserFilters, WithViticulturistValidation;

    public $plot_id = '';

    public $plot_planting_id = '';

    public $container_id = '';

    public $activity_date = '';

    public $harvest_start_date = '';

    public $harvest_end_date = '';

    public $total_weight = '';

    public $yield_per_hectare = '';

    public $baume_degree = '';

    public $brix_degree = '';

    public $acidity_level = '';

    public $ph_level = '';

    public $potential_alcohol = '';

    public $color_rating = '';

    public $aroma_rating = '';

    public $health_status = '';

    public $destination_type = '';

    public $destination = '';

    public $transport_document_number = '';

    public $destination_rega_code = '';

    public $vehicle_plate = '';

    public $buyer_name = '';

    public $harvest_ticket_number = '';

    public $sanitary_state_grapes = '';

    public $sanitary_state_agraces = '';

    public $sanitary_state_botrytis = '';

    public $sanitary_state_oidium = '';

    public $sanitary_state_mildew = '';

    public $price_per_kg = '';

    public $total_value = '';

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
        $this->harvest_start_date = now()->format('Y-m-d');

        $user = Auth::user();
        $campaign = Campaign::getOrCreateActiveForYear($user->id);

        if (! $campaign) {
            $this->toastError(__('No se pudo obtener la campaña activa.'));

            return $this->viticulturistRoleRedirect('campaign.create');
        }

        $this->campaign_id = $campaign->id;
        $this->loadAvailableContainers();
    }

    public function updatedContainerId($value): void
    {
        if ($value) {
            $container = Container::where('user_id', auth()->id())->find($value);
            if ($container && $container->hasAvailableCapacity(0)) {
                $this->total_weight = $container->getAvailableCapacity();
                $this->calculateYield();
                $this->calculateTotalValue();
                $this->updateControlPanelData();
            }
        }
    }

    public function updatedPlotId($value): void
    {
        if (! $value) {
            $this->availablePlantings = [];
            $this->plot_planting_id = '';
            $this->resetWithdrawalWarning();

            return;
        }

        $this->availablePlantings = PlotPlanting::where('plot_id', $value)
            ->where('status', 'active')
            ->with('grapeVariety')
            ->get();

        if ($this->availablePlantings->count() === 1) {
            $this->plot_planting_id = $this->availablePlantings->first()->id;
            $this->loadControlPanelData();
        } else {
            $this->plot_planting_id = '';
            $this->selectedPlanting = null;
            $this->harvestLimitInfo = null;
            $this->yieldVarianceInfo = null;
        }

        $this->checkWithdrawalPeriods();
        $this->calculateYield();
    }

    public function save()
    {
        $this->validate();

        $user = Auth::user();

        if (! $this->workType) {
            $this->addError('workType', __('Debes seleccionar quién realizó el trabajo.'));

            return;
        }

        if ($this->workType === 'crew' && ! $this->crew_id) {
            $this->addError('crew_id', __('Debes seleccionar un equipo.'));

            return;
        }

        if ($this->workType === 'individual' && ! $this->crew_member_id) {
            $this->addError('crew_member_id', __('Debes seleccionar un viticultor.'));

            return;
        }

        $this->authorizeCreateActivityForPlot($this->plot_id);

        if ($this->container_id) {
            $container = Container::where('user_id', auth()->id())->find($this->container_id);
            if (! $container) {
                $this->addError('container_id', __('El contenedor seleccionado no existe.'));

                return;
            }
            if (! $container->hasAvailableCapacity($this->total_weight)) {
                $this->addError('container_id', __('Capacidad insuficiente. Disponible: :available kg, Necesario: :required kg.', [
                    'available' => number_format($container->getAvailableCapacity(), 2),
                    'required' => number_format($this->total_weight, 2),
                ]));

                return;
            }
        }

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

                // Preparar notas con advertencia de plazo de seguridad si aplica
                $notes = $this->notes;
                if ($this->hasActiveWithdrawal && $this->withdrawalAcknowledged) {
                    $warningNote = "\n\n⚠️ COSECHA CON PLAZO DE SEGURIDAD ACTIVO\n";
                    $warningNote .= 'Motivo: '.$this->withdrawalReason."\n";
                    $warningNote .= "Tratamientos activos:\n";
                    foreach ($this->activeWithdrawalTreatments as $treatment) {
                        $warningNote .= "- {$treatment['product_name']} (aplicado el {$treatment['application_date']}, seguro desde {$treatment['safe_date']})\n";
                    }
                    $notes = $warningNote.($notes ? "\n".$notes : '');
                }

                $activity = AgriculturalActivity::create([
                    'plot_id' => $this->plot_id,
                    'viticulturist_id' => $user->id,
                    'campaign_id' => $this->campaign_id,
                    'activity_type' => 'harvest',
                    'activity_date' => $this->activity_date,
                    'crew_id' => $this->workType === 'crew' ? $this->crew_id : null,
                    'crew_member_id' => $crewMemberId,
                    'machinery_id' => $this->machinery_id ?: null,
                    'weather_conditions' => $this->weather_conditions,
                    'temperature' => $this->temperature ?: null,
                    'notes' => $notes,
                ]);

                Harvest::create([
                    'activity_id' => $activity->id,
                    'plot_planting_id' => $this->plot_planting_id,
                    'container_id' => $this->container_id ?: null,
                    'harvest_start_date' => $this->harvest_start_date,
                    'harvest_end_date' => $this->harvest_end_date ?: null,
                    'total_weight' => $this->total_weight,
                    'yield_per_hectare' => $this->yield_per_hectare,
                    'baume_degree' => $this->baume_degree ?: null,
                    'brix_degree' => $this->brix_degree ?: null,
                    'acidity_level' => $this->acidity_level ?: null,
                    'ph_level' => $this->ph_level ?: null,
                    'potential_alcohol' => $this->potential_alcohol ?: null,
                    'color_rating' => $this->color_rating ?: null,
                    'aroma_rating' => $this->aroma_rating ?: null,
                    'health_status' => $this->health_status ?: null,
                    'harvest_ticket_number' => $this->harvest_ticket_number ?: null,
                    'sanitary_state_grapes' => $this->sanitary_state_grapes ?: null,
                    'sanitary_state_agraces' => $this->sanitary_state_agraces ?: null,
                    'sanitary_state_botrytis' => $this->sanitary_state_botrytis ?: null,
                    'sanitary_state_oidium' => $this->sanitary_state_oidium ?: null,
                    'sanitary_state_mildew' => $this->sanitary_state_mildew ?: null,
                    'destination_type' => $this->destination_type ?: null,
                    'destination' => $this->destination,
                    'transport_document_number' => $this->transport_document_number,
                    'destination_rega_code' => $this->destination_rega_code,
                    'vehicle_plate' => $this->vehicle_plate,
                    'buyer_name' => $this->buyer_name,
                    'price_per_kg' => $this->price_per_kg ?: null,
                    'total_value' => $this->total_value ?: null,
                    'status' => 'active',
                    'notes' => $this->notes,
                ]);

                PhenologyObservation::updateOrCreate(
                    [
                        'plot_planting_id' => $this->plot_planting_id,
                        'campaign_id' => $activity->campaign_id,
                        'event' => 'harvest',
                    ],
                    [
                        'viticulturist_id' => $user->id,
                        'obs_date' => $this->harvest_start_date,
                        'bbch_code' => 89,
                        'source' => 'manual',
                        'active' => true,
                    ]
                );
            });

            $this->toastSuccess(__('Cosecha registrada correctamente.'));

            $redirectTarget = Auth::user()->hasWinery() ? 'harvests.index' : 'digital-notebook';

            return $this->viticulturistRoleRedirect($redirectTarget);
        } catch (\Exception $e) {
            \Log::error('Error al registrar cosecha', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'plot_id' => $this->plot_id,
                'trace' => $e->getTraceAsString(),
            ]);

            $this->toastError(__('Error al registrar la cosecha. Por favor, intenta de nuevo.'));
        }
    }

    public function render()
    {
        $user = Auth::user();

        $plots = Plot::forUser($user)
            ->where('active', true)
            ->whereHas('plantings', fn ($q) => $q->where('status', 'active'))
            ->orderBy('name')
            ->get();

        $crews = Crew::where('viticulturist_id', $user->id)->orderBy('name')->get();
        $machinery = Machinery::forViticulturist($user->id)->active()->orderBy('name')->get();
        $campaign = Campaign::find($this->campaign_id);

        return view('livewire.viticulturist.digital-notebook.create-harvest', [
            'plots' => $plots,
            'crews' => $crews,
            'machinery' => $machinery,
            'campaign' => $campaign,
            'allViticulturists' => $this->viticulturists,
        ]);
    }

    protected function loadAvailableContainers(): void
    {
        $this->availableContainers = Container::where('user_id', auth()->id())
            ->where('archived', false)
            ->whereColumn('used_capacity', '<', 'capacity')
            ->orderBy('name')
            ->get();
    }

    protected function rules(): array
    {
        $rules = [
            'plot_id' => $this->plotOwnershipRule(),
            'plot_planting_id' => $this->plotPlantingOwnershipRule(required: true),
            'container_id' => $this->ownedContainerRule(required: false),
            'campaign_id' => $this->campaignOwnershipRule(),
            'activity_date' => 'required|date',
            'harvest_start_date' => 'required|date',
            'harvest_end_date' => 'nullable|date|after_or_equal:harvest_start_date',
            'total_weight' => 'required|numeric|min:0.01',
            'yield_per_hectare' => 'nullable|numeric|min:0',
            'baume_degree' => 'nullable|numeric|min:0|max:20',
            'brix_degree' => 'nullable|numeric|min:0|max:40',
            'acidity_level' => 'nullable|numeric|min:0|max:20',
            'ph_level' => 'nullable|numeric|min:0|max:14',
            'potential_alcohol' => 'nullable|numeric|min:0|max:25',
            'color_rating' => 'nullable|in:excelente,bueno,aceptable,deficiente',
            'aroma_rating' => 'nullable|in:excelente,bueno,aceptable,deficiente',
            'health_status' => 'nullable|in:sano,daño_leve,daño_moderado,daño_grave',
            'harvest_ticket_number' => 'nullable|string|max:50',
            'sanitary_state_grapes' => 'nullable|numeric|min:0|max:100',
            'sanitary_state_agraces' => 'nullable|numeric|min:0|max:100',
            'sanitary_state_botrytis' => 'nullable|numeric|min:0|max:100',
            'sanitary_state_oidium' => 'nullable|numeric|min:0|max:100',
            'sanitary_state_mildew' => 'nullable|numeric|min:0|max:100',
            'destination_type' => 'required|in:winery,direct_sale,cooperative,self_consumption,other',
            'destination' => 'nullable|string|max:255',
            'transport_document_number' => 'required_unless:destination_type,self_consumption|nullable|string|max:50',
            'destination_rega_code' => 'required_unless:destination_type,self_consumption|nullable|string|max:20',
            'vehicle_plate' => 'nullable|string|max:20',
            'buyer_name' => 'nullable|string|max:255',
            'price_per_kg' => 'nullable|numeric|min:0',
            'total_value' => 'nullable|numeric|min:0',
            'workType' => 'required|in:crew,individual',
            'crew_id' => 'required_if:workType,crew|nullable|exists:crews,id',
            'crew_member_id' => 'required_if:workType,individual|nullable|exists:users,id',
            'machinery_id' => $this->machineryOwnershipRule(),
            'weather_conditions' => 'nullable|string|max:255',
            'temperature' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ];

        if ($this->hasActiveWithdrawal) {
            $rules['withdrawalAcknowledged'] = 'required|accepted';
            $rules['withdrawalReason'] = 'required|string|min:20';
        }

        return $rules;
    }
}
