<?php

namespace App\Livewire\Winery\Cellar\Containers;

use App\Livewire\Winery\AbstractIndex;
use App\Models\Container;
use App\Models\ContainerType;
use App\Services\WineContainerStockService;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public string $currentTab = 'active';
    public string $search     = '';
    public string $typeFilter = '';

    protected $queryString = [
        'currentTab' => ['as' => 'tab', 'except' => 'active'],
        'search'     => ['except' => ''],
        'typeFilter' => ['except' => ''],
    ];

    public function updatingSearch(): void     { $this->resetPage(); }
    public function updatingTypeFilter(): void { $this->resetPage(); }

    public function switchTab(string $tab): void
    {
        $this->currentTab = $tab;
        $this->resetPage();
    }

    protected function filterDefaults(): array
    {
        return ['search' => '', 'typeFilter' => ''];
    }

    public function archive(int $containerId): void
    {
        $container = Container::where('user_id', $this->wineryId())->findOrFail($containerId);
        $container->update(['archived' => true]);
        $this->toastSuccess("Contenedor «{$container->name}» desactivado.");
        $this->currentTab = 'archived';
    }

    public function unarchive(int $containerId): void
    {
        $container = Container::where('user_id', $this->wineryId())->findOrFail($containerId);
        $container->update(['archived' => false]);
        $this->toastSuccess("Contenedor «{$container->name}» activado.");
        $this->currentTab = 'active';
    }

    public function emptyWine(int $containerId): void
    {
        $container = Container::where('user_id', $this->wineryId())->findOrFail($containerId);
        app(WineContainerStockService::class)->emptyWineContent($container);
        $this->toastSuccess("Contenedor «{$container->name}» vaciado de vino.");
    }

    public function delete(int $containerId): void
    {
        $container = Container::where('user_id', $this->wineryId())
            ->withCount('harvests')
            ->findOrFail($containerId);

        if ($container->harvests_count > 0) {
            $this->toastError('No se puede eliminar un contenedor con recepciones asignadas.');
            return;
        }

        $container->delete();
        $this->toastSuccess('Contenedor eliminado.');
    }

    protected function baseQuery(): Builder
    {
        return Container::where('user_id', $this->wineryId())
            ->withCount('harvests')
            ->with('containerType');
    }

    protected function applyFilters(Builder $query): void
    {
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

        if ($this->currentTab === 'archived') {
            $query->where('archived', true);
        } else {
            $query->where('archived', false);
        }
    }

    protected function applyOrderBy(Builder $query): void
    {
        $query->orderBy('name');
    }

    protected function defaultOrderBy(): array { return ['name', 'asc']; }

    protected function perPage(): int { return 15; }

    protected function viewData(mixed $entries): array
    {
        $wineryId  = $this->wineryId();
        $types     = ContainerType::orderBy('name')->get();
        $typesById = $types->keyBy('id');

        $base = Container::where('user_id', $wineryId);
        $stats = [
            'active'   => (clone $base)->where('archived', false)->count(),
            'archived' => (clone $base)->where('archived', true)->count(),
        ];

        return [
            'containers' => $entries,
            'types'      => $types,
            'typesById'  => $typesById,
            'stats'      => $stats,
        ];
    }
}
