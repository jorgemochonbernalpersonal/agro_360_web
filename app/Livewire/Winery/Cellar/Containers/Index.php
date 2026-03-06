<?php

namespace App\Livewire\Winery\Cellar\Containers;

use App\Livewire\Winery\AbstractIndex;
use App\Models\Container;
use App\Models\ContainerType;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
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

    protected function filterDefaults(): array
    {
        return ['search' => '', 'typeFilter' => '', 'statusFilter' => 'active'];
    }

    public function archive(int $containerId): void
    {
        $container = Container::where('user_id', $this->wineryId())->findOrFail($containerId);
        $container->update(['archived' => true]);
        $this->toastSuccess("Contenedor «{$container->name}» archivado.");
    }

    public function unarchive(int $containerId): void
    {
        $container = Container::where('user_id', $this->wineryId())->findOrFail($containerId);
        $container->update(['archived' => false]);
        $this->toastSuccess("Contenedor «{$container->name}» reactivado.");
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
        return Container::where('user_id', $this->wineryId())->withCount('harvests');
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

        match ($this->statusFilter) {
            'active'   => $query->where('archived', false),
            'archived' => $query->where('archived', true),
            default    => null,
        };
    }

    protected function applyOrderBy(Builder $query): void
    {
        $query->orderBy('name');
    }

    protected function defaultOrderBy(): array { return ['name', 'asc']; }

    protected function perPage(): int { return 15; }

    protected function viewData(mixed $entries): array
    {
        $types     = ContainerType::orderBy('name')->get();
        $typesById = $types->keyBy('id');

        return [
            'containers' => $entries,
            'types'      => $types,
            'typesById'  => $typesById,
        ];
    }
}
