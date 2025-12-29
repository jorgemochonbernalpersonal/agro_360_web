<?php

namespace App\Livewire\Viticulturist\RemoteSensing;

use App\Services\RemoteSensing\AlertService;
use Livewire\Component;

class AlertsWidget extends Component
{
    public array $alerts = [];
    public int $totalCount = 0;

    public function mount()
    {
        $this->loadAlerts();
    }

    public function loadAlerts()
    {
        $service = new AlertService();
        $user = auth()->user();
        
        $alertsByPlot = $service->checkAlertsForUser($user);
        $this->alerts = $alertsByPlot;
        $this->totalCount = $service->getAlertCountForUser($user);
    }

    public function refresh()
    {
        $this->loadAlerts();
    }

    public function render()
    {
        return view('livewire.viticulturist.remote-sensing.alerts-widget');
    }
}
