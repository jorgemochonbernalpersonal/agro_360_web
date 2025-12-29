<?php

namespace App\Livewire\Viticulturist\RemoteSensing;

use App\Models\Plot;
use App\Services\RemoteSensing\IrrigationRecommendationService;
use Livewire\Component;

class PlotIrrigationCard extends Component
{
    public Plot $plot;
    public array $recommendation = [];

    public function mount(Plot $plot)
    {
        $this->plot = $plot;
        $this->loadRecommendation();
    }

    public function loadRecommendation()
    {
        $service = new IrrigationRecommendationService();
        $this->recommendation = $service->getRecommendation($this->plot);
    }

    public function refresh()
    {
        $this->loadRecommendation();
    }

    public function render()
    {
        return view('livewire.viticulturist.remote-sensing.plot-irrigation-card');
    }
}
