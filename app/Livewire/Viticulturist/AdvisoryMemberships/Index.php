<?php

namespace App\Livewire\Viticulturist\AdvisoryMemberships;

use App\Livewire\Viticulturist\AbstractIndex;
use App\Models\AdvisoryMembership;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public string $filterSpecialty = '';

    public function updatingFilterSpecialty(): void
    {
        $this->resetPage();
    }

    public function deactivate(int $id): void
    {
        $this->findOwned(AdvisoryMembership::class, $id)->update(['active' => false]);
        $this->toastSuccess(__('Asesor desactivado.'));
    }

    protected function filterDefaults(): array
    {
        return ['filterSpecialty' => ''];
    }

    protected function baseQuery(): Builder
    {
        return AdvisoryMembership::where('viticulturist_id', $this->viticulturistId())
            ->with('campaign:id,name')
            ->active();
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->filterSpecialty) {
            $query->where('specialty', $this->filterSpecialty);
        }
    }

    protected function defaultOrderBy(): array
    {
        return ['advisor_name', 'asc'];
    }

    protected function viewData(mixed $entries): array
    {
        $base = AdvisoryMembership::where('viticulturist_id', $this->viticulturistId())->active();

        $stats = [
            'total' => (clone $base)->count(),
            'permanent' => (clone $base)->whereNull('campaign_id')->count(),
            'this_year' => (clone $base)->whereHas('campaign', fn ($q) => $q->where('year', now()->year))->count(),
        ];

        return [
            'entries' => $entries,
            'specialties' => AdvisoryMembership::SPECIALTIES,
            'stats' => $stats,
        ];
    }
}
