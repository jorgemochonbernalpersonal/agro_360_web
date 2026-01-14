<?php

namespace App\Livewire\Viticulturist\Personal\Workers;

use App\Models\CrewMember;
use App\Models\Crew;
use App\Models\WineryViticulturist;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public $wineryFilter = '';
    public $newWorkerId = '';
    public $selectedWineryId = '';
    public $assignToCrewId = '';

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

    #[Layout('layouts.app')]
    public function render()
    {
        $user = Auth::user();

        // Obtener trabajadores individuales asignados por este viticultor
        $query = CrewMember::whereNull('crew_id')
            ->where('assigned_by', $user->id)
            ->with(['viticulturist', 'crew'])
            ->when($this->wineryFilter, function ($q) {
                // Filtrar por bodega a través de WineryViticulturist
                $viticulturistIds = WineryViticulturist::where('winery_id', $this->wineryFilter)
                    ->pluck('viticulturist_id');
                $q->whereIn('viticulturist_id', $viticulturistIds);
            })
            ->orderBy('created_at', 'desc');

        $workers = $query->paginate(10);

        // Obtener bodegas del viticultor usando relación
        $wineries = $user->wineries;

        // Obtener viticultores disponibles para agregar como trabajador individual
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
                ->filter(function ($viticulturist) {
                    // Excluir los que ya son trabajadores individuales
                    return !CrewMember::where('viticulturist_id', $viticulturist->id)
                        ->whereNull('crew_id')
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
                ->filter(function ($viticulturist) {
                    return !CrewMember::where('viticulturist_id', $viticulturist->id)
                        ->whereNull('crew_id')
                        ->exists();
                })
                ->sortBy('name')
                ->values();
        }

        // Obtener cuadrillas del viticultor para asignación
        $crews = Crew::forViticulturist($user->id)
            ->when($this->wineryFilter, fn($q) => $q->forWinery($this->wineryFilter))
            ->orderBy('name')
            ->get();

        return view('livewire.viticulturist.personal.workers.index', [
            'workers' => $workers,
            'wineries' => $wineries,
            'availableViticulturists' => $availableViticulturists,
            'crews' => $crews,
        ]);
    }

    public function addWorker()
    {
        if (empty($this->newWorkerId)) {
            $this->toastError('Debes seleccionar un viticultor.');
            return;
        }

        $user = Auth::user();

        // Validar que el viticultor seleccionado sea realmente visible para el usuario
        $visibleRelationExists = WineryViticulturist::visibleTo($user, $this->selectedWineryId ?: null)
            ->where('viticulturist_id', $this->newWorkerId)
            ->exists();

        if (! $visibleRelationExists) {
            $this->toastError('No tienes permiso para gestionar este viticultor.');
            return;
        }

        // Validar que no sea el mismo usuario
        if ($this->newWorkerId == $user->id) {
            $this->toastError('No puedes agregarte a ti mismo como trabajador individual.');
            return;
        }

        // Validar que no exista ya como trabajador (individual o en cuadrilla)
        $exists = CrewMember::where('viticulturist_id', $this->newWorkerId)->exists();

        if ($exists) {
            $this->toastError('Este viticultor ya está dado de alta como trabajador (individual o en una cuadrilla).');
            return;
        }

        try {
            DB::transaction(function () use ($user) {
                CrewMember::create([
                    'viticulturist_id' => $this->newWorkerId,
                    'crew_id' => null, // Trabajador individual
                    'assigned_by' => $user->id,
                ]);
            });

            $this->newWorkerId = '';
            $this->toastSuccess('Trabajador individual agregado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al agregar trabajador individual', [
                'error' => $e->getMessage(),
                'viticulturist_id' => $this->newWorkerId,
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString(),
            ]);
            $this->toastError('Error al agregar el trabajador. Por favor, intenta de nuevo.');
        }
    }

    public function removeWorker(CrewMember $worker)
    {
        if ($worker->crew_id !== null) {
            $this->toastError('Este trabajador pertenece a una cuadrilla. Remuévelo desde la cuadrilla.');
            return;
        }

        if ($worker->viticulturist_id == Auth::id()) {
            $this->toastError('No puedes removerte a ti mismo.');
            return;
        }

        try {
            DB::transaction(function () use ($worker) {
                $worker->delete();
            });

            $this->toastSuccess('Trabajador individual removido correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al remover trabajador individual', [
                'error' => $e->getMessage(),
                'worker_id' => $worker->id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->toastError('Error al remover el trabajador. Por favor, intenta de nuevo.');
        }
    }

    public function assignToCrew($workerId)
    {
        if (empty($workerId) || empty($this->assignToCrewId)) {
            $this->toastError('Debes seleccionar un trabajador y una cuadrilla.');
            return;
        }

        $worker = CrewMember::find($workerId);
        
        if (!$worker || $worker->crew_id !== null) {
            $this->toastError('Trabajador no válido o ya está en una cuadrilla.');
            return;
        }

        $crew = Crew::find($this->assignToCrewId);
        
        if (!$crew || $crew->viticulturist_id !== Auth::id()) {
            $this->toastError('Cuadrilla no válida.');
            return;
        }

        // Validar que no esté ya en la cuadrilla
        if (CrewMember::where('crew_id', $crew->id)
            ->where('viticulturist_id', $worker->viticulturist_id)
            ->exists()) {
            $this->toastError('Este trabajador ya está en esta cuadrilla.'); // Modified this line
            return;
        }

        try {
            DB::transaction(function () use ($worker, $crew) {
                $worker->update(['crew_id' => $crew->id]);
            });

            $this->assignToCrewId = '';
            $this->toastSuccess('Trabajador asignado a la cuadrilla correctamente.');
            $this->resetPage();
        } catch (\Exception $e) {
            Log::error('Error al asignar trabajador a cuadrilla', [
                'error' => $e->getMessage(),
                'worker_id' => $worker->id,
                'crew_id' => $crew->id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->toastError('Error al asignar el trabajador. Por favor, intenta de nuevo.');
        }
    }

    public function updatedSelectedWineryId()
    {
        $this->newWorkerId = '';
    }

    public function clearFilters()
    {
        $this->wineryFilter = '';
        $this->resetPage();
    }
}

