<?php

namespace App\Livewire\Concerns;

use App\Models\SupervisorViticulturist;
use App\Models\User;
use App\Models\WineryViticulturist;
use App\Notifications\ViticulturistAssignedToWineryNotification;
use Illuminate\Support\Facades\Auth;

trait WithViticulturistAssignment
{
    public bool $showAssignModal = false;

    public string $poolSearch = '';

    public function updatingPoolSearch(): void {}

    public function openAssignModal(): void
    {
        $this->poolSearch = '';
        $this->showAssignModal = true;
    }

    public function closeAssignModal(): void
    {
        $this->showAssignModal = false;
        $this->poolSearch = '';
    }

    public function assignViticulturist(int $viticulturistId): void
    {
        SupervisorViticulturist::where('supervisor_id', Auth::id())
            ->where('viticulturist_id', $viticulturistId)
            ->firstOrFail();

        WineryViticulturist::firstOrCreate(
            [
                'winery_id' => $this->winery->id,
                'viticulturist_id' => $viticulturistId,
            ],
            [
                'source' => WineryViticulturist::SOURCE_SUPERVISOR,
                'supervisor_id' => Auth::id(),
                'assigned_by' => Auth::id(),
            ]
        );

        $viticulturist = User::find($viticulturistId);
        if ($viticulturist?->can_login) {
            $viticulturist->notify(new ViticulturistAssignedToWineryNotification($this->winery, Auth::user()));
        }

        $this->dispatch('toast', message: __('Viticultor asignado a la bodega.'), type: 'success');
    }

    public function unassignViticulturist(int $viticulturistId): void
    {
        WineryViticulturist::where('supervisor_id', Auth::id())
            ->where('winery_id', $this->winery->id)
            ->where('viticulturist_id', $viticulturistId)
            ->where('source', WineryViticulturist::SOURCE_SUPERVISOR)
            ->firstOrFail()
            ->delete();

        $this->dispatch('toast', message: __('Viticultor retirado de la bodega.'), type: 'warning');
    }
}
