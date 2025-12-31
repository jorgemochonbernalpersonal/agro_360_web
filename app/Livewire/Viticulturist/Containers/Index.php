<?php

namespace App\Livewire\Viticulturist\Containers;

use App\Models\Container;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    // Filtros
    public $search = '';
    public $filterType = '';
    public $filterMaterial = '';
    public $filterStatus = '';
    public $filterRoom = '';
    
    // Ordenamiento
    public $sortBy = 'name';
    public $sortDirection = 'asc';
    
    // Vista
    public $viewMode = 'cards'; // cards, table
    
    protected $queryString = [
        'search' => ['except' => ''],
        'filterType' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'viewMode' => ['except' => 'cards'],
    ];

    public function showActive()
    {
        $this->filterStatus = '';
        $this->viewMode = 'cards';
        $this->resetPage();
    }

    public function showInactive()
    {
        $this->filterStatus = 'archived';
        $this->viewMode = 'cards';
        $this->resetPage();
    }

    public function showStats()
    {
        $this->viewMode = 'stats';
    }

    public function switchTab($tab)
    {
        switch ($tab) {
            case 'active':
                $this->showActive();
                break;
            case 'inactive':
                $this->showInactive();
                break;
            case 'statistics':
                $this->showStats();
                break;
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterType()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function toggleViewMode()
    {
        $this->viewMode = $this->viewMode === 'cards' ? 'table' : 'cards';
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->filterType = '';
        $this->filterMaterial = '';
        $this->filterStatus = '';
        $this->filterRoom = '';
        $this->resetPage();
    }

    public function archive($containerId)
    {
        $container = Container::where('user_id', Auth::id())->findOrFail($containerId);
        
        if (!$container->isEmpty()) {
            $this->toastError('No puedes archivar un contenedor que tiene contenido.');
            return;
        }
        
        $container->update(['archived' => true]);
        $this->toastSuccess('Contenedor archivado correctamente.');
    }

    public function unarchive($containerId)
    {
        $container = Container::where('user_id', Auth::id())->findOrFail($containerId);
        $container->update(['archived' => false]);
        $this->toastSuccess('Contenedor restaurado correctamente.');
    }

    public function delete($containerId)
    {
        $container = Container::where('user_id', Auth::id())->findOrFail($containerId);
        
        if (!$container->isEmpty()) {
            $this->toastError('No puedes eliminar un contenedor que tiene contenido.');
            return;
        }
        
        if ($container->harvests()->count() > 0) {
            $this->toastError('No puedes eliminar un contenedor con historial de vendimias. Archívalo en su lugar.');
            return;
        }
        
        $container->delete();
        $this->toastSuccess('Contenedor eliminado correctamente.');
    }

    public function getContainersProperty()
    {
        $query = Container::where('user_id', Auth::id());

        // Aplicar filtros
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('serial_number', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterType) {
            $query->where('type_id', $this->filterType);
        }

        if ($this->filterMaterial) {
            $query->where('material_id', $this->filterMaterial);
        }

        if ($this->filterStatus) {
            switch ($this->filterStatus) {
                case 'empty':
                    $query->where('used_capacity', '<=', 0);
                    break;
                case 'available':
                    $query->whereColumn('used_capacity', '<', 'capacity')
                          ->where('used_capacity', '>', 0);
                    break;
                case 'full':
                    $query->whereColumn('used_capacity', '>=', 'capacity');
                    break;
                case 'archived':
                    $query->where('archived', true);
                    break;
                default:
                    $query->where('archived', false);
            }
        } else {
            $query->where('archived', false);
        }

        if ($this->filterRoom) {
            $query->where('container_room_id', $this->filterRoom);
        }

        // Aplicar ordenamiento
        $query->orderBy($this->sortBy, $this->sortDirection);

        return $query->paginate(12);
    }

    public function getStatsProperty()
    {
        $containers = Container::where('user_id', Auth::id())
            ->where('archived', false)
            ->get();

        return [
            'total_capacity' => $containers->sum('capacity'),
            'used_capacity' => $containers->sum('used_capacity'),
            'available_capacity' => $containers->sum(function($c) {
                return $c->getAvailableCapacity();
            }),
            'occupancy_percentage' => $containers->sum('capacity') > 0 
                ? round(($containers->sum('used_capacity') / $containers->sum('capacity')) * 100, 1)
                : 0,
            'total_containers' => $containers->count(),
            'empty_containers' => $containers->filter(fn($c) => $c->isEmpty())->count(),
            'available_containers' => $containers->filter(fn($c) => !$c->isEmpty() && !$c->isFull())->count(),
            'full_containers' => $containers->filter(fn($c) => $c->isFull())->count(),
        ];
    }

    public function render()
    {
        return view('livewire.viticulturist.containers.index', [
            'containers' => $this->containers,
            'stats' => $this->stats,
        ])->layout('layouts.app');
    }
}
