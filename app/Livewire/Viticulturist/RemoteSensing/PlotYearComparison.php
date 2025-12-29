<?php

namespace App\Livewire\Viticulturist\RemoteSensing;

use App\Models\Plot;
use App\Services\RemoteSensing\YearComparisonService;
use Livewire\Component;

class PlotYearComparison extends Component
{
    public Plot $plot;
    public int $year1;
    public int $year2;
    public array $availableYears = [];
    public array $comparisonData = [];

    public function mount(Plot $plot)
    {
        $this->plot = $plot;
        
        $service = new YearComparisonService();
        $this->availableYears = $service->getAvailableYears($plot);
        
        if (count($this->availableYears) >= 2) {
            $this->year2 = $this->availableYears[0];
            $this->year1 = $this->availableYears[1];
            $this->loadComparison();
        } elseif (count($this->availableYears) === 1) {
            $this->year1 = $this->availableYears[0];
            $this->year2 = $this->availableYears[0];
        } else {
            $this->year1 = now()->year - 1;
            $this->year2 = now()->year;
        }
    }

    public function updatedYear1()
    {
        $this->loadComparison();
    }

    public function updatedYear2()
    {
        $this->loadComparison();
    }

    public function loadComparison()
    {
        $service = new YearComparisonService();
        $this->comparisonData = $service->compareYears($this->plot, $this->year1, $this->year2);
    }

    public function render()
    {
        return view('livewire.viticulturist.remote-sensing.plot-year-comparison');
    }
}
