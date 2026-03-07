<?php

namespace App\Livewire\Winery\Harvest\Reception;

use App\Models\Harvest;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    public Harvest $harvest;

    public function mount(Harvest $harvest): void
    {
        $wineryId = Auth::id();

        abort_unless($harvest->winery_id === $wineryId, 403);

        $this->harvest = $harvest->load([
            'plotPlanting.grapeVariety',
            'plotPlanting.plot',
            'batch.viticulturist',
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
