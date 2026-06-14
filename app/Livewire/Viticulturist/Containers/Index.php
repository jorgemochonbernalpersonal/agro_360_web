<?php

namespace App\Livewire\Viticulturist\Containers;

use App\Livewire\Concerns\WithListing;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Container;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    use WithListing, WithToastNotifications;

    public $filterStatus = ''; // '', 'empty', 'available', 'full'

    protected $queryString = [
        'filterStatus' => ['except' => ''],
    ];

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    // switchTab propio: además de paginar, limpia el filtro de ocupación.
    public function switchTab($tab)
    {
        $this->currentTab = $tab;
        $this->filterStatus = '';
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->filterStatus = '';
        $this->resetPage();
    }

    public function archive($containerId)
    {
        $container = Container::where('user_id', Auth::id())->findOrFail($containerId);

        if (! $container->isEmpty()) {
            $this->toastError(__('No puedes archivar un contenedor que tiene contenido.'));

            return;
        }

        $container->update(['archived' => true]);
        $this->toastSuccess(__('Contenedor archivado correctamente.'));

        if ($this->currentTab === 'active') {
            $this->currentTab = 'archived';
        }
    }

    public function unarchive($containerId)
    {
        $container = Container::where('user_id', Auth::id())->findOrFail($containerId);
        $container->update(['archived' => false]);
        $this->toastSuccess(__('Contenedor restaurado correctamente.'));

        if ($this->currentTab === 'archived') {
            $this->currentTab = 'active';
        }
    }

    public function delete($containerId)
    {
        $container = Container::where('user_id', Auth::id())->findOrFail($containerId);

        if (! $container->isEmpty()) {
            $this->toastError(__('No puedes eliminar un contenedor que tiene contenido.'));

            return;
        }

        if ($container->harvests()->count() > 0) {
            $this->toastError(__('No puedes eliminar un contenedor con historial de vendimias. Archívalo en su lugar.'));

            return;
        }

        $container->delete();
        $this->toastSuccess(__('Contenedor eliminado correctamente.'));
    }

    public function render()
    {
        $query = Container::where('user_id', Auth::id());

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('serial_number', 'like', '%'.$this->search.'%');
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
            'active' => (clone $baseQuery)->where('archived', false)->count(),
            'archived' => (clone $baseQuery)->where('archived', true)->count(),
        ];

        return view('livewire.viticulturist.containers.index', compact('containers', 'stats'))
            ->layout('layouts.app');
    }
}
