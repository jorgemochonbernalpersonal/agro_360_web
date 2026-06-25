<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

use App\Livewire\Concerns\WithHarvestControlPanel;
use App\Livewire\Concerns\WithHarvestFormFields;
use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Livewire\Concerns\WithUserFilters;
use App\Livewire\Concerns\WithViticulturistValidation;
use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\Container;
use App\Models\Harvest;
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
    use WithHarvestControlPanel, WithHarvestFormFields, WithRoleAwareRedirect, WithToastNotifications, WithUserFilters, WithViticulturistValidation;

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

    protected function onMultiplePlantingsInUpdatedPlot(): void
    {
        $this->plot_planting_id = '';
    }

    public function save()
    {
        $this->validate();

        $user = Auth::user();

        if (! $this->validateWorkType()) {
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
                $crewMemberId = $this->resolveCrewMemberId($user);

                $activityData = array_merge($this->buildActivityData($crewMemberId), [
                    'viticulturist_id' => $user->id,
                    'activity_type' => 'harvest',
                    'notes' => $this->buildWithdrawalNotes(),
                ]);

                $activity = AgriculturalActivity::create($activityData);

                Harvest::create(array_merge($this->buildHarvestData(), [
                    'activity_id' => $activity->id,
                    'status' => 'active',
                    'notes' => $this->notes,
                ]));

                $this->syncPhenologyObservation($activity->campaign_id, $user);
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
        return view('livewire.viticulturist.digital-notebook.create-harvest',
            $this->harvestFormRenderData(Auth::user())
        );
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
        return array_merge($this->harvestBaseRules(), [
            'destination_type' => 'required|in:winery,direct_sale,cooperative,self_consumption,other',
            'transport_document_number' => 'required_unless:destination_type,self_consumption|nullable|string|max:50',
            'destination_rega_code' => 'required_unless:destination_type,self_consumption|nullable|string|max:20',
            'workType' => 'required|in:crew,individual',
            'crew_id' => 'required_if:workType,crew|nullable|exists:crews,id',
            'crew_member_id' => 'required_if:workType,individual|nullable|exists:users,id',
        ]);
    }
}
