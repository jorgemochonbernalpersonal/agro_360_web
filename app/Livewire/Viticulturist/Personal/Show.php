<?php

namespace App\Livewire\Viticulturist\Personal;

use App\Models\Crew;
use App\Models\CrewMember;
use App\Models\WineryViticulturist;
use App\Models\ViticulturistHierarchy;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Show extends Component
{
    public Crew $crew;
    public $newMemberId = '';
    public $filterMode = 'all'; // 'all' | 'hierarchy'
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
            'subordinates_count' => $this->getSubordinatesCount(),
        ];
    }

    private function getSubordinatesCount(): int
    {
        return ViticulturistHierarchy::where('parent_viticulturist_id', $this->crew->viticulturist_id)
            ->when($this->crew->winery_id, fn($q) => $q->where('winery_id', $this->crew->winery_id))
            ->when(!$this->crew->winery_id, fn($q) => $q->whereNull('winery_id'))
            ->count();
    }

    public function addMember()
    {
        if (empty($this->newMemberId)) {
            session()->flash('error', 'Debes seleccionar un viticultor.');
            return;
        }

        $user = Auth::user();

        // Validar visibilidad usando el método isVisibleTo
        if ($this->crew->winery_id) {
            $wineryRelation = WineryViticulturist::where('viticulturist_id', $this->newMemberId)
                ->where('winery_id', $this->crew->winery_id)
                ->first();

            if (!$wineryRelation || !$wineryRelation->isVisibleTo($user)) {
                session()->flash('error', 'No tienes permiso para gestionar este viticultor.');
                return;
            }
        } else {
            // Si no hay winery, solo validar que sea un viticultor creado por el usuario
            $wineryRelation = WineryViticulturist::where('viticulturist_id', $this->newMemberId)
                ->where('parent_viticulturist_id', $user->id)
                ->where('source', WineryViticulturist::SOURCE_VITICULTURIST)
                ->first();

            if (!$wineryRelation) {
                session()->flash('error', 'Solo puedes gestionar viticultores que hayas creado.');
                return;
            }
        }

        // Validar que no sea el líder
        if ($this->newMemberId == $this->crew->viticulturist_id) {
            session()->flash('error', 'El líder de la cuadrilla no puede ser miembro.');
            return;
        }

        // Validar que no esté ya en la cuadrilla
        if ($this->crew->members()->where('viticulturist_id', $this->newMemberId)->exists()) {
            session()->flash('error', 'Este viticultor ya es miembro de la cuadrilla.');
            return;
        }

        try {
            DB::transaction(function () {
                // Verificar si ya existe como trabajador individual
                $existingWorker = CrewMember::where('viticulturist_id', $this->newMemberId)
                    ->whereNull('crew_id')
                    ->first();

                if ($existingWorker) {
                    // Actualizar trabajador individual a miembro de cuadrilla
                    $existingWorker->update([
                        'crew_id' => $this->crew->id,
                        'assigned_by' => Auth::id(),
                    ]);
                } else {
                    // Crear nuevo miembro
                    CrewMember::create([
                        'crew_id' => $this->crew->id,
                        'viticulturist_id' => $this->newMemberId,
                        'assigned_by' => Auth::id(),
                    ]);
                }
            });

            $this->crew->refresh();
            $this->loadStats();
            $this->newMemberId = '';
            session()->flash('message', 'Miembro agregado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al agregar miembro a cuadrilla', [
                'error' => $e->getMessage(),
                'crew_id' => $this->crew->id,
                'viticulturist_id' => $this->newMemberId,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);
            session()->flash('error', 'Error al agregar el miembro. Por favor, intenta de nuevo.');
        }
    }

    public function removeMember(CrewMember $member)
    {
        if ($member->crew_id !== $this->crew->id) {
            session()->flash('error', 'Miembro no válido.');
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
            session()->flash('message', 'Miembro removido correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al remover miembro de cuadrilla', [
                'error' => $e->getMessage(),
                'member_id' => $member->id,
                'crew_id' => $this->crew->id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);
            session()->flash('error', 'Error al remover el miembro. Por favor, intenta de nuevo.');
        }
    }

    public function getAvailableViticulturistsProperty()
    {
        $user = Auth::user();
        
        // Usar scope visibleTo() para filtrar según relaciones (supervisor/winery)
        // El scope ya incluye eager loading de viticulturist, winery y parentViticulturist
        $query = WineryViticulturist::visibleTo($user, $this->crew->winery_id);

        // Excluir miembros actuales de esta cuadrilla
        $currentMemberIds = $this->crew->members()->pluck('viticulturist_id');
        $query->whereNotIn('viticulturist_id', $currentMemberIds);

        // Si el filtro es por jerarquía, solo mostrar subordinados
        if ($this->filterMode === 'hierarchy') {
            $subordinateIds = ViticulturistHierarchy::where('parent_viticulturist_id', $this->crew->viticulturist_id)
                ->when($this->crew->winery_id, fn($q) => $q->where('winery_id', $this->crew->winery_id))
                ->when(!$this->crew->winery_id, fn($q) => $q->whereNull('winery_id'))
                ->pluck('child_viticulturist_id');
            
            $query->whereIn('viticulturist_id', $subordinateIds);
        }

        // Obtener trabajadores individuales
        if ($this->crew->winery_id) {
            $individualWorkerIds = CrewMember::whereNull('crew_id')
                ->whereIn('viticulturist_id', function ($q) {
                    $q->select('viticulturist_id')
                      ->from('winery_viticulturist')
                      ->where('winery_id', $this->crew->winery_id);
                })
                ->pluck('viticulturist_id');
        } else {
            // Si no hay winery, obtener trabajadores individuales asignados por el usuario
            $individualWorkerIds = CrewMember::whereNull('crew_id')
                ->where('assigned_by', $user->id)
                ->pluck('viticulturist_id');
        }

        return $query->get()
            ->pluck('viticulturist')
            ->map(function ($viticulturist) use ($user, $individualWorkerIds) {
                // Agregar propiedades dinámicas
                $viticulturist->is_individual_worker = $individualWorkerIds->contains($viticulturist->id);
                $viticulturist->can_edit = $user->canEditViticulturist($viticulturist->id);
                return $viticulturist;
            })
            ->sortBy(function ($viticulturist) {
                // Priorizar trabajadores individuales
                return isset($viticulturist->is_individual_worker) && $viticulturist->is_individual_worker ? 0 : 1;
            })
            ->sortBy('name')
            ->values();
    }

    public function render()
    {
        $this->crew->load(['members.viticulturist', 'activities']);
        
        return view('livewire.viticulturist.personal.show', [
            'availableViticulturists' => $this->availableViticulturists,
        ])->layout('layouts.app');
    }
}

