<?php

namespace App\Livewire\Winery\Cellar\Containers;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Container;
use App\Models\ContainerType;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public string $search       = '';
    public string $typeFilter   = '';
    public string $statusFilter = 'active';

    protected $queryString = [
        'search'       => ['except' => ''],
        'typeFilter'   => ['except' => ''],
        'statusFilter' => ['except' => 'active'],
    ];

    public function updatingSearch(): void       { $this->resetPage(); }
    public function updatingTypeFilter(): void   { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->search       = '';
        $this->typeFilter   = '';
        $this->statusFilter = 'active';
        $this->resetPage();
    }

    public function archive(int $containerId): void
    {
        $container = Container::where('user_id', Auth::id())->findOrFail($containerId);
        $container->update(['archived' => true]);
        $this->toastSuccess("Contenedor «{$container->name}» archivado.");
    }

    public function unarchive(int $containerId): void
    {
        $container = Container::where('user_id', Auth::id())->findOrFail($containerId);
        $container->update(['archived' => false]);
        $this->toastSuccess("Contenedor «{$container->name}» reactivado.");
    }

    public function delete(int $containerId): void
    {
        $container = Container::where('user_id', Auth::id())
            ->withCount('harvests')
            ->findOrFail($containerId);

        if ($container->harvests_count > 0) {
            $this->toastError('No se puede eliminar un contenedor con recepciones asignadas.');
            return;
        }

        $container->delete();
        $this->toastSuccess('Contenedor eliminado.');
    }

    public function render()
    {
        $wineryId = Auth::id();

        $query = Container::where('user_id', $wineryId)
            ->withCount('harvests')
            ->orderBy('name');

        if ($this->search) {
            $term = '%' . mb_strtolower($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(name) LIKE ?', [$term])
                  ->orWhereRaw('LOWER(IFNULL(description, \'\')) LIKE ?', [$term]);
            });
        }

        if ($this->typeFilter) {
            $query->where('type_id', $this->typeFilter);
        }

        match ($this->statusFilter) {
            'active'   => $query->where('archived', false),
            'archived' => $query->where('archived', true),
            default    => null,
        };

        $containers = $query->paginate(15);
        $types      = ContainerType::orderBy('name')->get();
        $typesById  = $types->keyBy('id');

        $statsBase = Container::where('user_id', $wineryId)->where('archived', false);
        $totalCapacity = (float) $statsBase->sum('capacity');
        $totalUsed     = (float) $statsBase->sum('used_capacity');

        $stats = [
            'total'           => $statsBase->count(),
            'total_capacity'  => $totalCapacity,
            'total_used'      => $totalUsed,
            'total_available' => max(0, $totalCapacity - $totalUsed),
            'fill_percent'    => $totalCapacity > 0 ? round($totalUsed / $totalCapacity * 100, 1) : 0,
        ];

        return view('livewire.winery.cellar.containers.index', [
            'containers' => $containers,
            'types'      => $types,
            'typesById'  => $typesById,
            'stats'      => $stats,
        ])->layout('layouts.app');
    }
}
