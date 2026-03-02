<?php

namespace App\Livewire\Viticulturist\CommercialAuthorizations;

use App\Livewire\Viticulturist\AbstractIndex;
use App\Models\CommercialAuthorization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Index extends AbstractIndex
{
    public string $filterType = '';

    public function updatingFilterType(): void { $this->resetPage(); }

    protected function filterDefaults(): array
    {
        return ['filterType' => ''];
    }

    public function deactivate(int $id): void
    {
        $this->findOwned(CommercialAuthorization::class, $id)->update(['active' => false]);
        $this->toastSuccess('Autorización archivada.');
    }

    protected function baseQuery(): Builder
    {
        return CommercialAuthorization::where('viticulturist_id', $this->viticulturistId())->active();
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->filterType) {
            $query->where('authorization_type', $this->filterType);
        }
    }

    protected function defaultOrderBy(): array { return ['expiry_date', 'asc']; }

    protected function viewData(mixed $entries): array
    {
        $expiring = CommercialAuthorization::where('viticulturist_id', $this->viticulturistId())
            ->active()
            ->expiringSoon(60)
            ->count();

        return [
            'entries'   => $entries,
            'authTypes' => CommercialAuthorization::AUTHORIZATION_TYPES,
            'expiring'  => $expiring,
        ];
    }
}
