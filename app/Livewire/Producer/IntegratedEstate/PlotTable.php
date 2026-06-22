<?php

namespace App\Livewire\Producer\IntegratedEstate;

use App\Models\PhenologyObservation;
use App\Models\Plot;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class PlotTable extends Component
{
    use WithPagination;

    public int $campaignId = 0;

    public int $plotFilter = 0;

    public string $search = '';

    public function mount(int $campaignId = 0, int $plotFilter = 0): void
    {
        $this->campaignId = $campaignId;
        $this->plotFilter = $plotFilter;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $userId = Auth::id();

        $query = Plot::where('viticulturist_id', $userId)
            ->with([
                'plantings' => fn ($q) => $q
                    ->with('grapeVariety:id,name')
                    ->whereNull('uprooting_date')
                    ->orderBy('name'),
                'latestRemoteSensing',
                'municipality:id,name',
            ])
            ->orderBy('name');

        if ($this->plotFilter > 0) {
            $query->where('id', $this->plotFilter);
        }

        if ($this->search !== '') {
            $query->where('name', 'like', '%'.$this->search.'%');
        }

        $paginatedPlots = $query->paginate(15);

        // ── Fenología: carga en batch para todas las plantaciones de esta página ─
        $plantingIds = $paginatedPlots->getCollection()
            ->flatMap->plantings
            ->pluck('id');

        $latestPhenology = $this->campaignId > 0 && $plantingIds->isNotEmpty()
            ? PhenologyObservation::whereIn('plot_planting_id', $plantingIds)
                ->where('campaign_id', $this->campaignId)
                ->select('plot_planting_id', 'event', 'obs_date', 'bbch_code')
                ->orderByDesc('obs_date')
                ->get()
                ->groupBy('plot_planting_id')
                ->map->first()
            : collect();

        return view('livewire.producer.integrated-estate.plot-table', [
            'paginatedPlots' => $paginatedPlots,
            'latestPhenology' => $latestPhenology,
        ]);
    }
}
