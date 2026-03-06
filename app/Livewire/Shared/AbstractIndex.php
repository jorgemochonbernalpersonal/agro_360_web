<?php

namespace App\Livewire\Shared;

use App\Livewire\Concerns\WithToastNotifications;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

abstract class AbstractIndex extends Component
{
    use WithToastNotifications, WithPagination;

    /**
     * Base query scoped to the authenticated user.
     * Do NOT add ordering here — use defaultOrderBy() or applyOrderBy() instead.
     */
    abstract protected function baseQuery(): Builder;

    /**
     * Default sort: ['column', 'asc'|'desc'].
     */
    abstract protected function defaultOrderBy(): array;

    /**
     * Variables passed to the view alongside the paginated/collected entries.
     */
    abstract protected function viewData(mixed $entries): array;

    /**
     * Items per page. Return 0 to skip pagination and use ->get().
     */
    protected function perPage(): int
    {
        return 20;
    }

    /**
     * Filter property names mapped to their reset values.
     */
    protected function filterDefaults(): array
    {
        return [];
    }

    /**
     * Apply additional filter conditions to the query.
     */
    protected function applyFilters(Builder $query): void {}

    /**
     * Apply ordering to the query.
     */
    protected function applyOrderBy(Builder $query): void
    {
        [$col, $dir] = $this->defaultOrderBy();
        $dir === 'desc' ? $query->orderByDesc($col) : $query->orderBy($col);
    }

    public function clearFilters(): void
    {
        foreach ($this->filterDefaults() as $property => $default) {
            $this->$property = $default;
        }
        $this->resetPage();
    }

    public function render(): View
    {
        $query = $this->baseQuery();
        $this->applyFilters($query);
        $this->applyOrderBy($query);

        $perPage = $this->perPage();
        $entries = $perPage > 0 ? $query->paginate($perPage) : $query->get();

        return view($this->resolveViewName(), $this->viewData($entries))
            ->layout('layouts.app');
    }

    /**
     * Derives the Blade view name from the component class namespace.
     * App\Livewire\Winery\Viticulturists\Index
     * → livewire.winery.viticulturists.index
     */
    protected function resolveViewName(): string
    {
        $relative = str_replace('App\\Livewire\\', '', static::class);

        return 'livewire.' . implode('.', array_map(
            fn(string $part) => Str::kebab($part),
            explode('\\', $relative),
        ));
    }
}
