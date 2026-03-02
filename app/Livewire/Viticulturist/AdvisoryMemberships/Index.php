<?php

namespace App\Livewire\Viticulturist\AdvisoryMemberships;

use App\Livewire\Viticulturist\AbstractIndex;
use App\Models\AdvisoryMembership;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public string $filterSpecialty = '';

    public function updatingFilterSpecialty(): void { $this->resetPage(); }

    protected function filterDefaults(): array
    {
        return ['filterSpecialty' => ''];
    }

    public function deactivate(int $id): void
    {
        $this->findOwned(AdvisoryMembership::class, $id)->update(['active' => false]);
        $this->toastSuccess('Asesor desactivado.');
    }

    protected function baseQuery(): Builder
    {
        return AdvisoryMembership::where('viticulturist_id', $this->viticulturistId())->active();
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->filterSpecialty) {
            $query->where('specialty', $this->filterSpecialty);
        }
    }

    protected function defaultOrderBy(): array { return ['advisor_name', 'asc']; }

    protected function viewData(mixed $entries): array
    {
        return [
            'entries'     => $entries,
            'specialties' => AdvisoryMembership::SPECIALTIES,
        ];
    }
}
