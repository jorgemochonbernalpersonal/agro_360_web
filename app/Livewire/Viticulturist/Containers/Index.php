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

    public $currentTab    = 'active'; // 'active', 'archived'
    public $search        = '';
    public $filterStatus  = ''; // '', 'empty', 'available', 'full'

    protected $queryString = [
        'currentTab'   => ['as' => 'tab', 'except' => 'active'],
        'search'       => ['except' => ''],
        'filterStatus' => ['except' => ''],
    ];

    public function updatingSearch()       { $this->resetPage(); }
    public function updatingFilterStatus() { $this->resetPage(); }

    public function switchTab($tab)
    {
        $this->currentTab  = $tab;
        $this->filterStatus = '';
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search        = '';
        $this->filterStatus  = '';
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

        if ($this->currentTab === 'active') {
            $this->currentTab = 'archived';
        }
    }

    public function unarchive($containerId)
    {
        $container = Container::where('user_id', Auth::id())->findOrFail($containerId);
        $container->update(['archived' => false]);
        $this->toastSuccess('Contenedor restaurado correctamente.');

        if ($this->currentTab === 'archived') {
            $this->currentTab = 'active';
        }
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

    public function render()
    {
        $query = Container::where('user_id', Auth::id());

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('serial_number', 'like', '%' . $this->search . '%');
            });
        }

        // Tab activos/archivados
        if ($this->currentTab === 'archived') {
            $query->where('archived', true);
        } else {
            $query->where('archived', false);
        }

        // Filtro de estado de ocupación
        if ($this->filterStatus === 'empty') {
            $query->where('used_capacity', '<=', 0);
        } elseif ($this->filterStatus === 'available') {
            $query->whereColumn('used_capacity', '<', 'capacity')->where('used_capacity', '>', 0);
        } elseif ($this->filterStatus === 'full') {
            $query->whereColumn('used_capacity', '>=', 'capacity');
        }

        $containers = $query->orderBy('name')->paginate(12);

        $baseQuery = Container::where('user_id', Auth::id());
        $stats = [
            'active'   => (clone $baseQuery)->where('archived', false)->count(),
            'archived' => (clone $baseQuery)->where('archived', true)->count(),
        ];

        return view('livewire.viticulturist.containers.index', compact('containers', 'stats'))
            ->layout('layouts.app');
    }
}
