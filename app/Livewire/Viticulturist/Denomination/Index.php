<?php

namespace App\Livewire\Viticulturist\Denomination;

use App\Models\SupervisorViticulturist;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Index extends Component
{
    #[Layout('layouts.app')]
    public function render()
    {
        $viticulturistId = Auth::id();

        // Relación con supervisor (pool DO)
        $supervisorRelation = SupervisorViticulturist::where('viticulturist_id', $viticulturistId)
            ->with('supervisor')
            ->first();

        $supervisor = $supervisorRelation?->supervisor;

        // Bodegas asignadas por el supervisor a este viticultor
        $supervisorWineries = collect();

        if ($supervisor) {
            $supervisorWineries = WineryViticulturist::where('viticulturist_id', $viticulturistId)
                ->where('source', WineryViticulturist::SOURCE_SUPERVISOR)
                ->where('supervisor_id', $supervisor->id)
                ->with('winery')
                ->get()
                ->map(fn ($rel) => $rel->winery)
                ->filter();
        }

        // Acceso al cuaderno concedido al supervisor
        $notebookGranted = $supervisorRelation?->hasNotebookAccess() ?? false;

        return view('livewire.viticulturist.denomination.index', [
            'supervisor'         => $supervisor,
            'supervisorJoined'   => $supervisorRelation?->created_at,
            'supervisorWineries' => $supervisorWineries,
            'notebookGranted'    => $notebookGranted,
        ]);
    }
}
