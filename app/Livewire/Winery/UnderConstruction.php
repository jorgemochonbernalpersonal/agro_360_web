<?php

namespace App\Livewire\Winery;

use Livewire\Component;

class UnderConstruction extends Component
{
    public string $module = '';

    public string $icon = 'wrench-screwdriver';

    public string $eta = '';

    public string $backRoute = 'winery.dashboard';

    public function mount(
        string $module = '',
        string $icon = 'wrench-screwdriver',
        string $eta = '',
        string $backRoute = 'winery.dashboard'
    ): void {
        $this->module = $module;
        $this->icon = $icon;
        $this->eta = $eta;
        $this->backRoute = $backRoute;
    }

    public function render()
    {
        return view('livewire.winery.under-construction')
            ->layout('layouts.app');
    }
}
