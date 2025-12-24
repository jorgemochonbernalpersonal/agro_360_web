<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

use App\Models\Harvest;
use App\Models\Container;
use App\Models\EstimatedYield;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ShowHarvest extends Component
{
    public $harvest;
    public $harvest_id;
    public $container; // Un solo contenedor
    public $estimatedYield;
    public $harvestLimitInfo = null;
    public $totalHarvestedInCampaign = 0;

    public function mount($harvest)
    {
        $this->harvest_id = $harvest;
        $this->loadHarvest();
    }

    public function loadHarvest()
    {
        $user = Auth::user();

        $this->harvest = Harvest::whereHas('activity', function($q) use ($user) {
            $q->where('viticulturist_id', $user->id);
        })
        ->with([
            'activity.plot',
            'activity.campaign',
            'activity.crew',
            'activity.crewMember.viticulturist',
            'activity.machinery',
            'plotPlanting.grapeVariety',
            'container',
            'editor'
        ])
        ->findOrFail($this->harvest_id);

        // Cargar rendimiento estimado si existe
        if ($this->harvest->plot_planting_id && $this->harvest->activity->campaign_id) {
            $this->estimatedYield = EstimatedYield::where('plot_planting_id', $this->harvest->plot_planting_id)
                ->where('campaign_id', $this->harvest->activity->campaign_id)
                ->with('estimator')
                ->first();

            // Cargar información del límite de cosecha
            $planting = $this->harvest->plotPlanting;
            if ($planting && $planting->hasHarvestLimit()) {
                $this->totalHarvestedInCampaign = $planting->getTotalActualYieldForCampaign($this->harvest->activity->campaign_id);
                $this->harvestLimitInfo = [
                    'limit' => $planting->harvest_limit_kg,
                    'harvested' => $this->totalHarvestedInCampaign,
                    'remaining' => $planting->getRemainingHarvestLimitForCampaign($this->harvest->activity->campaign_id),
                    'percentage' => $planting->getHarvestLimitUsagePercentageForCampaign($this->harvest->activity->campaign_id),
                    'exceeds' => $this->totalHarvestedInCampaign > $planting->harvest_limit_kg,
                ];
            }
        }
    }

    public function getContainerUsedCapacity()
    {
        return $this->harvest->container ? $this->harvest->container->used_capacity : 0;
    }

    public function getContainersCount()
    {
        // Si hay un contenedor asignado, retornar 1
        return $this->harvest->container ? 1 : 0;
    }

    public function getContainersTotalWeight()
    {
        // Retornar la capacidad usada del contenedor (no la capacidad total)
        return $this->harvest->container ? $this->harvest->container->used_capacity : 0;
    }

    public function render()
    {
        return view('livewire.viticulturist.digital-notebook.show-harvest')
            ->layout('layouts.app');
    }
}

