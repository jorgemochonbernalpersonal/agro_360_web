<?php

namespace App\Livewire\Viticulturist\RemoteSensing;

use App\Models\Plot;
use App\Services\RemoteSensing\ImageHistoryService;
use Livewire\Component;

class PlotImageHistory extends Component
{
    public Plot $plot;
    public $history = [];
    public int $months = 12;
    public ?int $selectedIndex = null;

    public function mount(Plot $plot)
    {
        $this->plot = $plot;
        $this->loadHistory();
    }

    public function loadHistory()
    {
        $service = new ImageHistoryService();
        $this->history = $service->getHistory($this->plot, $this->months)->toArray();
        
        if (!empty($this->history)) {
            $this->selectedIndex = 0;
        }
    }

    public function selectRecord(int $index)
    {
        $this->selectedIndex = $index;
    }

    public function updatedMonths()
    {
        $this->loadHistory();
    }

    public function render()
    {
        return view('livewire.viticulturist.remote-sensing.plot-image-history');
    }
}
