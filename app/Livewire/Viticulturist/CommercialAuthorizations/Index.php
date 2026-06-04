<?php

namespace App\Livewire\Viticulturist\CommercialAuthorizations;

use App\Livewire\Viticulturist\AbstractIndex;
use App\Models\CommercialAuthorization;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public string $filterType = '';

    public function updatingFilterType(): void
    {
        $this->resetPage();
    }

    public function deactivate(int $id): void
    {
        $this->findOwned(CommercialAuthorization::class, $id)->update(['active' => false]);
        $this->toastSuccess(__('Autorización archivada.'));
    }

    protected function filterDefaults(): array
    {
        return ['filterType' => ''];
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

    protected function defaultOrderBy(): array
    {
        return ['expiry_date', 'asc'];
    }

    protected function viewData(mixed $entries): array
    {
        $vitId = $this->viticulturistId();
        $base = CommercialAuthorization::where('viticulturist_id', $vitId);

        $expiring = (clone $base)->active()->expiringSoon(60)->count();

        $stats = [
            'total' => (clone $base)->active()->count(),
            'expiring' => $expiring,
            'expired' => (clone $base)->active()->whereNotNull('expiry_date')->where('expiry_date', '<', now())->count(),
            'types' => (clone $base)->active()->distinct()->count('authorization_type'),
        ];

        return [
            'entries' => $entries,
            'authTypes' => CommercialAuthorization::authorizationTypeOptions(),
            'expiring' => $expiring,
            'stats' => $stats,
        ];
    }
}
