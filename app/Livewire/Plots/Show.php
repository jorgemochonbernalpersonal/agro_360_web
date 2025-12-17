<?php

namespace App\Livewire\Plots;

use App\Models\Plot;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Show extends Component
{
    public Plot $plot;

    public function mount(Plot $plot)
    {
        if (!Auth::user()->can('view', $plot)) {
            abort(403);
        }

        $this->plot = $plot->load([
            'winery',
            'viticulturist',
            'autonomousCommunity',
            'province',
            'municipality',
            'sigpacUses',
            'sigpacCodes',
            'multipartCoordinates.sigpacCode'
        ]);
    }

    public function render()
    {
        return view('livewire.plots.show')->layout('layouts.app');
    }
}
