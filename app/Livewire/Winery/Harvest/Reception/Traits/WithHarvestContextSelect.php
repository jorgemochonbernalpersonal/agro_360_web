<?php

namespace App\Livewire\Winery\Harvest\Reception\Traits;

use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\Auth;

trait WithHarvestContextSelect
{
    public string $viticulturist_id = '';

    public string $plot_id = '';

    public string $plot_planting_id = '';

    /** @var array<int, mixed> */
    public array $availablePlots = [];

    /** @var array<int, mixed> */
    public array $availablePlantings = [];

    public function updatedViticulturistId(): void
    {
        $this->plot_id = '';
        $this->plot_planting_id = '';
        $this->availablePlots = [];
        $this->availablePlantings = [];
        $this->resetPlantingState();

        if (! $this->viticulturist_id) {
            return;
        }

        $wineryId = Auth::id();
        $isSelf = Auth::user()->isProducer() && (int) $this->viticulturist_id === $wineryId;
        $isLinked = $isSelf || WineryViticulturist::where('winery_id', $wineryId)
            ->where('viticulturist_id', $this->viticulturist_id)
            ->exists();

        if (! $isLinked) {
            $this->viticulturist_id = '';

            return;
        }

        $this->availablePlots = Plot::where('viticulturist_id', $this->viticulturist_id)
            ->where('active', true)
            ->whereHas('plantings', fn ($q) => $q->where('status', 'active'))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();
    }

    public function updatedPlotId(): void
    {
        $this->plot_planting_id = '';
        $this->availablePlantings = [];
        $this->resetPlantingState();

        if (! $this->plot_id) {
            return;
        }

        $this->availablePlantings = PlotPlanting::where('plot_id', $this->plot_id)
            ->where('status', 'active')
            ->with('grapeVariety:id,name')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'label' => ($p->grapeVariety->name ?? $p->name ?? 'Sin variedad')
                    .($p->area_planted ? ' — '.number_format((float) $p->area_planted, 2).' ha' : ''),
                'area' => $p->area_planted,
                'limit_kg' => $p->harvest_limit_kg,
                'planting_year' => $p->planting_year,
                'do' => $p->designation_of_origin,
            ])
            ->toArray();

        if (count($this->availablePlantings) === 1) {
            $this->plot_planting_id = (string) $this->availablePlantings[0]['id'];
            $this->loadPlantingState();
        }
    }
}
