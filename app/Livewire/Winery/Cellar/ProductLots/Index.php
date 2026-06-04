<?php

namespace App\Livewire\Winery\Cellar\ProductLots;

use App\Livewire\Winery\AbstractIndex;
use App\Models\ProductLot;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class Index extends AbstractIndex
{
    public string $search = '';

    public string $typeFilter = '';

    public string $currentTab = 'active';

    protected $queryString = [
        'search' => ['except' => ''],
        'typeFilter' => ['except' => ''],
        'currentTab' => ['except' => 'active'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingCurrentTab(): void
    {
        $this->resetPage();
    }

    public function switchTab(string $tab): void
    {
        $this->currentTab = $tab;
        $this->resetPage();
    }

    public function duplicate(int $id): void
    {
        $original = ProductLot::where('user_id', $this->wineryId())->findOrFail($id);

        DB::transaction(function () use ($original) {
            $clone = $original->replicate();
            $clone->name = $original->name.' (Copia)';
            $clone->sku = null;
            $clone->quantity = 0;
            $clone->initial_quantity = 0;
            $clone->available_quantity = 0;
            $clone->reserved_quantity = 0;
            $clone->sold_quantity = 0;
            $clone->archived = false;
            $clone->save();

            // Copiar variedades de uva y taxes
            $clone->grapeVarieties()->sync(
                $original->grapeVarieties->pluck('pivot.percentage', 'id')->toArray()
            );
            $clone->taxes()->sync($original->taxes->pluck('id')->toArray());
        });

        $this->toastSuccess("«{$original->name}» duplicado correctamente.");
    }

    public function toggleActive(int $id): void
    {
        $lot = ProductLot::where('user_id', $this->wineryId())->findOrFail($id);
        $wasArchived = (bool) $lot->archived;
        $lot->update(['archived' => ! $wasArchived]);

        if ($wasArchived) {
            $this->toastSuccess("«{$lot->name}» activado.");
            if ($this->currentTab === 'inactive') {
                $this->currentTab = 'active';
            }
        } else {
            $this->toastSuccess("«{$lot->name}» desactivado.");
            if ($this->currentTab === 'active') {
                $this->currentTab = 'inactive';
            }
        }
    }

    protected function filterDefaults(): array
    {
        return ['search' => '', 'typeFilter' => ''];
    }

    protected function baseQuery(): Builder
    {
        return ProductLot::where('user_id', $this->wineryId());
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $term = '%'.mb_strtolower($this->search).'%';
            $query->whereRaw('LOWER(name) LIKE ?', [$term]);
        }

        if ($this->typeFilter) {
            $query->where('wine_type', $this->typeFilter);
        }

        match ($this->currentTab) {
            'active' => $query->where('archived', false),
            'inactive' => $query->where('archived', true),
            default => null,
        };
    }

    protected function applyOrderBy(Builder $query): void
    {
        $query->orderByDesc('vintage')->orderBy('name');
    }

    protected function defaultOrderBy(): array
    {
        return ['vintage', 'desc'];
    }

    protected function perPage(): int
    {
        return 15;
    }

    protected function viewData(mixed $entries): array
    {
        $base = ProductLot::where('user_id', $this->wineryId());
        $stats = [
            'active' => (clone $base)->where('archived', false)->count(),
            'inactive' => (clone $base)->where('archived', true)->count(),
        ];

        return ['lots' => $entries, 'stats' => $stats];
    }
}
