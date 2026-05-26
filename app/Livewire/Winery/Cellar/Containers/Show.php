<?php

namespace App\Livewire\Winery\Cellar\Containers;

use App\Livewire\Concerns\WithOwnershipRules;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Container;
use App\Models\ContainerHistory;
use App\Models\Wine;
use App\Models\WineAnalysis;
use App\Models\WineFermentationControl;
use App\Models\WineTransfer;
use App\Services\WineContainerStockService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    use WithOwnershipRules, WithToastNotifications;

    public Container $container;

    public bool   $fromVisual        = false;

    // Ajuste manual de litros
    public bool   $showAdjustModal  = false;
    public string $adjustWineId     = '';
    public string $adjustLiters     = '';

    public function mount(Container $container): void
    {
        abort_if($container->user_id !== Auth::id(), 403);
        $this->container  = $container;
        $this->fromVisual = request()->query('from') === 'visual';
    }

    public function emptyWine(): void
    {
        $container = Container::where('user_id', Auth::id())->findOrFail($this->container->id);
        app(WineContainerStockService::class)->emptyWineContent($container);
        $this->container->refresh();
        $this->toastSuccess(__('Contenedor vaciado de vino elaborado.'));
    }

    public function openAdjustModal(): void
    {
        $this->adjustLiters  = (string) $this->container->wine_volume_liters;
        $this->adjustWineId  = (string) ($this->container->currentState?->wine_id ?? '');
        $this->showAdjustModal = true;
    }

    public function saveAdjust(): void
    {
        $this->validate([
            'adjustLiters' => ['required', 'numeric', 'min:0'],
            'adjustWineId' => $this->ownedWineRule(false),
        ]);

        $container = Container::where('user_id', Auth::id())->findOrFail($this->container->id);
        app(WineContainerStockService::class)->manualAdjust(
            $container,
            $this->adjustWineId ? (int) $this->adjustWineId : null,
            (float) $this->adjustLiters
        );

        $this->container->refresh();
        $this->showAdjustModal = false;
        $this->toastSuccess(__('Stock de vino ajustado correctamente.'));
    }

    public function render()
    {
        $container = Container::where('id', $this->container->id)
            ->with([
                'containerType',
                'containerMaterial',
                'containerRoom',
                'currentState.harvest.batch.grapeVariety',
                'currentState.wine',
            ])
            ->firstOrFail();

        $history = ContainerHistory::where('container_id', $container->id)
            ->with('wine')
            ->orderByDesc('start_date')
            ->take(15)
            ->get();

        $recentTransfers = WineTransfer::with(['wine', 'fromContainer', 'toContainer'])
            ->where(fn($q) => $q
                ->where('from_container_id', $container->id)
                ->orWhere('to_container_id', $container->id)
            )
            ->orderByDesc('transfer_date')
            ->take(5)
            ->get();

        $maintenanceCount = $container->maintenances()->count();
        $additiveCount    = $container->additiveSupplies()->count();

        $recentAnalyses = WineAnalysis::with('wine')
            ->where('container_id', $container->id)
            ->orderByDesc('analysis_date')
            ->take(5)
            ->get();

        $recentFermentations = WineFermentationControl::with('wine')
            ->where('container_id', $container->id)
            ->orderByDesc('control_date')
            ->take(5)
            ->get();

        // Ocupación cosecha (kg)
        $harvestPct = $container->capacity > 0
            ? min(($container->used_capacity / $container->capacity) * 100, 100)
            : 0;

        // Ocupación vino (litros)
        $winePct = $container->capacity > 0
            ? min(($container->wine_volume_liters / $container->capacity) * 100, 100)
            : 0;

        $wines = Wine::where('user_id', Auth::id())->orderBy('name')->get(['id', 'name']);

        return view('livewire.winery.cellar.containers.show', [
            'wines'               => $wines,
            'container'           => $container,
            'history'             => $history,
            'recentTransfers'     => $recentTransfers,
            'maintenanceCount'    => $maintenanceCount,
            'additiveCount'       => $additiveCount,
            'harvestPct'          => round($harvestPct, 1),
            'winePct'             => round($winePct, 1),
            'recentAnalyses'      => $recentAnalyses,
            'recentFermentations' => $recentFermentations,
        ])->layout('layouts.app');
    }
}
