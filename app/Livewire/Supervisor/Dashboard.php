<?php

namespace App\Livewire\Supervisor;

use App\Models\SupervisorWinery;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $doId = Auth::id();

        $wineryCount = SupervisorWinery::where('supervisor_id', $doId)->count();

        $viticulturistCount = WineryViticulturist::where('supervisor_id', $doId)
            ->where('source', WineryViticulturist::SOURCE_SUPERVISOR)
            ->distinct('viticulturist_id')
            ->count('viticulturist_id');

        return view('livewire.supervisor.dashboard', [
            'wineryCount'        => $wineryCount,
            'viticulturistCount' => $viticulturistCount,
        ])->layout('layouts.app');
    }
}
