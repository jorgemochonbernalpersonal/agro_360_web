<?php

namespace App\Livewire\Viticulturist\Personal;

use App\Models\Crew;
use App\Models\CrewMember;
use App\Models\WineryViticulturist;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Show extends Component
{
    use WithToastNotifications;
    public Crew $crew;
    public $stats = [];

    public function mount(Crew $crew)
    {
        if (!Auth::user()->can('view', $crew)) {
            abort(403, 'No tienes permiso para ver esta cuadrilla.');
        }

        $this->crew = $crew->load(['winery', 'viticulturist', 'members.viticulturist', 'activities']);
        $this->loadStats();
    }

    private function loadStats()
    {
        $this->stats = [
            'members_count' => $this->crew->members()->count(),
            'activities_count' => $this->crew->activities()->count(),
        ];
    }

    public function removeMember(CrewMember $member)
    {
        if ($member->crew_id !== $this->crew->id) {
            $this->toastError('Miembro no válido.');
            return;
        }

        try {
            DB::transaction(function () use ($member) {
                // Opción: Convertir a trabajador individual en lugar de eliminar
                // O simplemente eliminar (comentado para futura implementación)
                // $member->update(['crew_id' => null]);
                
                $member->delete();
            });

            $this->crew->refresh();
            $this->loadStats();
            $this->toastSuccess('Miembro removido correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al remover miembro de cuadrilla', [
                'error' => $e->getMessage(),
                'member_id' => $member->id,
                'crew_id' => $this->crew->id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->toastError('Error al remover el miembro. Por favor, intenta de nuevo.');
        }
    }

    public function render()
    {
        $this->crew->load(['members.viticulturist', 'activities']);
        
        return view('livewire.viticulturist.personal.show')->layout('layouts.app');
    }
}

