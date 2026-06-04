<?php

namespace App\Livewire\Viticulturist\Phenology;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Campaign;
use App\Models\PhenologyObservation;
use App\Models\PlotPlanting;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    use WithToastNotifications;

    public $filter_campaign_id = '';

    public $filter_planting_id = '';

    protected $queryString = [
        'filter_campaign_id' => ['except' => '', 'as' => 'campaign'],
        'filter_planting_id' => ['except' => '', 'as' => 'filter_planting_id'],
    ];

    public function mount()
    {
        if (! $this->filter_planting_id) {
            $this->redirect(route('plots.plantings.index'), navigate: true);

            return;
        }

        $user = Auth::user();
        $campaign = Campaign::where('viticulturist_id', $user->id)->where('active', true)->first();
        if ($campaign && ! $this->filter_campaign_id) {
            $this->filter_campaign_id = $campaign->id;
        }
    }

    public function delete(int $id)
    {
        PhenologyObservation::whereHas('plotPlanting.plot', function ($q) {
            $q->where('viticulturist_id', Auth::id());
        })->findOrFail($id)->delete();

        $this->toastSuccess(__('Observación eliminada.'));
    }

    public function render()
    {
        $user = Auth::user();

        $campaigns = Campaign::where('viticulturist_id', $user->id)
            ->orderBy('year', 'desc')->get();

        $query = PhenologyObservation::whereHas('plotPlanting.plot', function ($q) use ($user) {
            $q->where('viticulturist_id', $user->id);
        })
            ->with(['plotPlanting.plot', 'plotPlanting.grapeVariety', 'campaign'])
            ->active();

        if ($this->filter_campaign_id) {
            $query->where('campaign_id', $this->filter_campaign_id);
        }

        if ($this->filter_planting_id) {
            $query->where('plot_planting_id', $this->filter_planting_id);
        }

        $observations = $query->chronological()->get();

        $filteredPlanting = $this->filter_planting_id
            ? PlotPlanting::with(['plot', 'grapeVariety'])->find($this->filter_planting_id)
            : null;

        return view('livewire.viticulturist.phenology.index', [
            'observations' => $observations,
            'campaigns' => $campaigns,
            'filteredPlanting' => $filteredPlanting,
            'events' => PhenologyObservation::eventOptions(),
        ])->layout('layouts.app');
    }
}
