<?php

namespace App\Livewire\Winery\Harvest\Reception;

use App\Models\Campaign;
use App\Models\Harvest;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    public Harvest $harvest;

    public function mount(Harvest $harvest): void
    {
        $wineryId         = Auth::id();
        $viticulturistIds = WineryViticulturist::where('winery_id', $wineryId)->pluck('viticulturist_id');
        $campaignIds      = Campaign::forViticulturist($wineryId)->pluck('id');

        $exists = Harvest::where('id', $harvest->id)
            ->whereHas('activity', fn($q) =>
                $q->whereIn('viticulturist_id', $viticulturistIds)
                  ->whereIn('campaign_id', $campaignIds)
            )->exists();

        abort_unless($exists, 403);

        $this->harvest = $harvest->load([
            'plotPlanting.grapeVariety',
            'plotPlanting.plot',
            'activity.viticulturist',
            'activity.campaign',
            'container',
            'editor',
        ]);
    }

    public function render()
    {
        return view('livewire.winery.harvest.reception.show')
            ->layout('layouts.app');
    }
}
