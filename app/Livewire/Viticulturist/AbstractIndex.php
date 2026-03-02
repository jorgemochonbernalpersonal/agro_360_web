<?php

namespace App\Livewire\Viticulturist;

use App\Livewire\Concerns\WithToastNotifications;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

abstract class AbstractIndex extends Component
{
    use WithToastNotifications, WithPagination;

    /**
     * Base query scoped to the authenticated viticulturist.
     * Do NOT add ordering here — use defaultOrderBy() or applyOrderBy() instead.
     */
    abstract protected function baseQuery(): Builder;

    /**
     * Default sort: ['column', 'asc'|'desc'].
     * Override applyOrderBy() for multi-column sorts.
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
     * Used by clearFilters() to reset all filters at once.
     */
    protected function filterDefaults(): array
    {
        return [];
    }

    /**
     * Apply additional filter conditions to the query.
     * Override in subclasses that have filters.
     */
    protected function applyFilters(Builder $query): void {}

    /**
     * Apply ordering to the query.
     * Override for multi-column sorts.
     */
    protected function applyOrderBy(Builder $query): void
    {
        [$col, $dir] = $this->defaultOrderBy();
        $dir === 'desc' ? $query->orderByDesc($col) : $query->orderBy($col);
    }

    /**
     * Returns the authenticated viticulturist's user ID.
     */
    protected function viticulturistId(): int
    {
        return Auth::id();
    }

    /**
     * Find a model owned by the authenticated viticulturist, or abort 404.
     *
     * @template T of Model
     * @param  class-string<T>  $modelClass
     * @return T
     */
    protected function findOwned(string $modelClass, int $id): Model
    {
        return $modelClass::where('viticulturist_id', $this->viticulturistId())->findOrFail($id);
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
     * App\Livewire\Viticulturist\AdvisoryMemberships\Index
     * → livewire.viticulturist.advisory-memberships.index
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
