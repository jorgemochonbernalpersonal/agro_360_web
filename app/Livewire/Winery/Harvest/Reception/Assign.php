<?php

namespace App\Livewire\Winery\Harvest\Reception;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Campaign;
use App\Models\Container;
use App\Models\ContainerType;
use App\Models\Harvest;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Assign extends Component
{
    use WithToastNotifications;

    public Harvest $harvest;
    public string  $container_id = '';

    public function mount(Harvest $harvest): void
    {
        $wineryId = Auth::id();

        // Guard: verify this harvest belongs to this winery
        $viticulturistIds  = WineryViticulturist::where('winery_id', $wineryId)->pluck('viticulturist_id');
        $wineryCampaignIds = Campaign::forViticulturist($wineryId)->pluck('id');

        $exists = Harvest::where('id', $harvest->id)
            ->whereHas('activity', function ($q) use ($viticulturistIds, $wineryCampaignIds) {
                $q->whereIn('viticulturist_id', $viticulturistIds)
                  ->whereIn('campaign_id', $wineryCampaignIds);
            })
            ->exists();

        abort_unless($exists, 403);

        $this->harvest = $harvest->load([
            'plotPlanting.grapeVariety',
            'plotPlanting.plot',
            'activity.viticulturist',
            'activity.campaign',
            'container',
        ]);

        if ($harvest->container_id) {
            $this->container_id = (string) $harvest->container_id;
        }
    }

    public function save(): void
    {
        $this->validate([
            'container_id' => ['required', 'exists:containers,id'],
        ], [
            'container_id.required' => 'Selecciona un contenedor.',
        ]);

        $wineryId  = Auth::id();
        $container = Container::where('user_id', $wineryId)->findOrFail((int) $this->container_id);

        if ($this->harvest->container_id === $container->id) {
            $this->toastError('Este contenedor ya está asignado a esta recepción.');
            return;
        }

        // If no previous container, validate available capacity
        if (!$this->harvest->container_id) {
            $weight = (float) $this->harvest->total_weight;
            if (!$container->hasAvailableCapacity($weight)) {
                $available = number_format($container->getAvailableCapacity(), 0);
                $required  = number_format($weight, 0);
                $this->addError('container_id', "Capacidad insuficiente. Disponible: {$available} kg, Necesario: {$required} kg.");
                return;
            }
        }

        // HarvestObserver::updating() handles ContainerStockService::transferContainer()
        $this->harvest->update(['container_id' => $container->id]);

        $this->toastSuccess("Recepción asignada al contenedor «{$container->name}».");
        redirect()->route('winery.grape-reception.index');
    }

    public function render()
    {
        $wineryId = Auth::id();

        $availableContainers = Container::where('user_id', $wineryId)
            ->where('archived', false)
            ->orderBy('name')
            ->get();

        $typesById = ContainerType::all()->keyBy('id');

        return view('livewire.winery.harvest.reception.assign', [
            'availableContainers' => $availableContainers,
            'typesById'           => $typesById,
        ])->layout('layouts.app');
    }
}
