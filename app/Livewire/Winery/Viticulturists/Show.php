<?php

namespace App\Livewire\Winery\Viticulturists;

use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    public User $viticulturist;

    public WineryViticulturist $relation;

    public function mount(User $viticulturist): void
    {
        $wineryId = Auth::id();

        $this->relation = WineryViticulturist::where('winery_id', $wineryId)
            ->where('viticulturist_id', $viticulturist->id)
            ->firstOrFail();

        $this->viticulturist = $viticulturist->load([
            'plots.municipality',
            'plots.plantings.grapeVariety',
            'profile',
        ]);
    }

    public function render()
    {
        $plots = $this->viticulturist->plots;

        $totalHa        = $plots->sum('area');
        $totalPlantings = $plots->sum(fn($p) => $p->plantings->count());
        $totalKgLimit   = $plots->sum(fn($p) => $p->plantings->sum('harvest_limit_kg'));

        $isOwn = $this->relation->source === WineryViticulturist::SOURCE_OWN;

        return view('livewire.winery.viticulturists.show', [
            'plots'          => $plots,
            'totalHa'        => $totalHa,
            'totalPlantings' => $totalPlantings,
            'totalKgLimit'   => $totalKgLimit,
            'relation'       => $this->relation,
            'isOwn'          => $isOwn,
        ])->layout('layouts.app');
    }
}
