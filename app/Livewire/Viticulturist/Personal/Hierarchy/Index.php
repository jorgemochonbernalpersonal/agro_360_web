<?php

namespace App\Livewire\Viticulturist\Personal\Hierarchy;

use App\Models\ViticulturistHierarchy;
use App\Models\WineryViticulturist;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Index extends Component
{
    use WithPagination;

    public $wineryFilter = '';
    public $newSubordinateId = '';
    public $selectedWineryId = '';

    protected $queryString = ['wineryFilter'];

    public function mount()
    {
        $user = Auth::user();
        
        // Si solo tiene una bodega, auto-seleccionarla
        $wineries = $user->wineries;

        if ($wineries->count() === 1) {
            $this->wineryFilter = $wineries->first()->id;
            $this->selectedWineryId = $wineries->first()->id;
        }
    }

    public function render()
    {
        $user = Auth::user();

        $query = ViticulturistHierarchy::where('parent_viticulturist_id', $user->id)
            ->with(['childViticulturist', 'winery'])
            ->when($this->wineryFilter, fn($q) => $q->where('winery_id', $this->wineryFilter))
            ->orderBy('created_at', 'desc');

        $subordinates = $query->paginate(10);

        // Obtener bodegas del viticultor usando relación
        $wineries = $user->wineries;

        // Obtener viticultores disponibles para agregar como subordinados
        // Usar scope visibleTo() para filtrar según relaciones (supervisor/winery)
        // Si no hay winery seleccionada, mostrar solo los viticultores creados por el usuario
        $availableViticulturists = collect();
        if ($this->selectedWineryId) {
            // El scope visibleTo() ya incluye eager loading de viticulturist, winery y parentViticulturist
            $availableViticulturists = WineryViticulturist::visibleTo($user, $this->selectedWineryId)
                ->get()
                ->map(function ($relation) use ($user) {
                    $viticulturist = $relation->viticulturist;
                    // Agregar flag si puede editar (solo los que creó)
                    $viticulturist->can_edit = $user->canEditViticulturist($viticulturist->id);
                    return $viticulturist;
                })
                ->filter(function ($viticulturist) use ($user) {
                    // Excluir los que ya son subordinados
                    return !ViticulturistHierarchy::where('parent_viticulturist_id', $user->id)
                        ->where('child_viticulturist_id', $viticulturist->id)
                        ->where('winery_id', $this->selectedWineryId)
                        ->exists();
                })
                ->sortBy('name')
                ->values();
        } else {
            // Si no hay winery seleccionada, mostrar solo los viticultores creados por el usuario
            // El scope visibleTo() ya incluye eager loading
            $availableViticulturists = WineryViticulturist::visibleTo($user)
                ->get()
                ->map(function ($relation) use ($user) {
                    $viticulturist = $relation->viticulturist;
                    $viticulturist->can_edit = $user->canEditViticulturist($viticulturist->id);
                    return $viticulturist;
                })
                ->filter(function ($viticulturist) use ($user) {
                    // Excluir los que ya son subordinados (sin winery)
                    return !ViticulturistHierarchy::where('parent_viticulturist_id', $user->id)
                        ->where('child_viticulturist_id', $viticulturist->id)
                        ->whereNull('winery_id')
                        ->exists();
                })
                ->sortBy('name')
                ->values();
        }

        return view('livewire.viticulturist.personal.hierarchy.index', [
            'subordinates' => $subordinates,
            'wineries' => $wineries,
            'availableViticulturists' => $availableViticulturists,
        ])->layout('layouts.app');
    }

    public function addSubordinate()
    {
        if (empty($this->newSubordinateId)) {
            session()->flash('error', 'Debes seleccionar un viticultor.');
            return;
        }

        $user = Auth::user();

        // Validar visibilidad usando el método isVisibleTo
        // Si hay winery seleccionada, validar que el viticultor esté en esa winery
        if ($this->selectedWineryId) {
            $wineryRelation = WineryViticulturist::where('viticulturist_id', $this->newSubordinateId)
                ->where('winery_id', $this->selectedWineryId)
                ->first();

            if (!$wineryRelation || !$wineryRelation->isVisibleTo($user)) {
                session()->flash('error', 'No tienes permiso para gestionar este viticultor.');
                return;
            }
        } else {
            // Si no hay winery, solo validar que sea un viticultor creado por el usuario
            $wineryRelation = WineryViticulturist::where('viticulturist_id', $this->newSubordinateId)
                ->where('parent_viticulturist_id', $user->id)
                ->where('source', WineryViticulturist::SOURCE_VITICULTURIST)
                ->first();

            if (!$wineryRelation) {
                session()->flash('error', 'Solo puedes gestionar viticultores que hayas creado.');
                return;
            }
        }

        // Validar que no sea el mismo usuario
        if ($this->newSubordinateId == $user->id) {
            session()->flash('error', 'No puedes asignarte a ti mismo como subordinado.');
            return;
        }

        // Validar que no exista ya la relación
        $exists = ViticulturistHierarchy::where('parent_viticulturist_id', $user->id)
            ->where('child_viticulturist_id', $this->newSubordinateId)
            ->when($this->selectedWineryId, fn($q) => $q->where('winery_id', $this->selectedWineryId))
            ->when(!$this->selectedWineryId, fn($q) => $q->whereNull('winery_id'))
            ->exists();

        if ($exists) {
            $message = $this->selectedWineryId 
                ? 'Este viticultor ya es tu subordinado en esta bodega.'
                : 'Este viticultor ya es tu subordinado.';
            session()->flash('error', $message);
            return;
        }

        try {
            DB::transaction(function () use ($user) {
                ViticulturistHierarchy::create([
                    'parent_viticulturist_id' => $user->id,
                    'child_viticulturist_id' => $this->newSubordinateId,
                    'winery_id' => $this->selectedWineryId ?: null,
                    'assigned_by' => $user->id,
                ]);
            });

            $this->newSubordinateId = '';
            session()->flash('message', 'Subordinado agregado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al agregar subordinado', [
                'error' => $e->getMessage(),
                'parent_id' => $user->id,
                'child_id' => $this->newSubordinateId,
                'winery_id' => $this->selectedWineryId,
                'trace' => $e->getTraceAsString(),
            ]);
            session()->flash('error', 'Error al agregar el subordinado. Por favor, intenta de nuevo.');
        }
    }

    public function removeSubordinate(ViticulturistHierarchy $hierarchy)
    {
        if ($hierarchy->parent_viticulturist_id !== Auth::id()) {
            session()->flash('error', 'No tienes permiso para remover este subordinado.');
            return;
        }

        try {
            DB::transaction(function () use ($hierarchy) {
                $hierarchy->delete();
            });

            session()->flash('message', 'Subordinado removido correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al remover subordinado', [
                'error' => $e->getMessage(),
                'hierarchy_id' => $hierarchy->id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);
            session()->flash('error', 'Error al remover el subordinado. Por favor, intenta de nuevo.');
        }
    }

    public function updatedSelectedWineryId()
    {
        $this->newSubordinateId = '';
    }

    public function clearFilters()
    {
        $this->wineryFilter = '';
        $this->resetPage();
    }
}

