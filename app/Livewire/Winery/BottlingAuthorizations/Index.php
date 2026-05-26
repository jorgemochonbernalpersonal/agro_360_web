<?php

namespace App\Livewire\Winery\BottlingAuthorizations;

use App\Livewire\Winery\AbstractIndex;
use App\Models\BottlingAuthorization;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public string $search       = '';
    public string $typeFilter   = '';
    public string $statusFilter = '';

    protected $queryString = [
        'search'       => ['except' => ''],
        'typeFilter'   => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function updatingSearch(): void        { $this->resetPage(); }
    public function updatingTypeFilter(): void    { $this->resetPage(); }
    public function updatingStatusFilter(): void  { $this->resetPage(); }

    protected function filterDefaults(): array
    {
        return ['search' => '', 'typeFilter' => '', 'statusFilter' => ''];
    }

    public function delete(int $id): void
    {
        $authorization = BottlingAuthorization::where('user_id', $this->wineryId())->findOrFail($id);
        $authorization->delete();
        $this->toastSuccess(__('Autorización de embotellado eliminada.'));
    }

    protected function baseQuery(): Builder
    {
        return BottlingAuthorization::with('wine')->where('user_id', $this->wineryId());
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $term = '%' . mb_strtolower($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(authorization_number) LIKE ?', [$term])
                  ->orWhereRaw('LOWER(IFNULL(issuing_authority, \'\')) LIKE ?', [$term]);
            });
        }
        if ($this->typeFilter) {
            $query->where('authorization_type', $this->typeFilter);
        }
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }
    }

    protected function applyOrderBy(Builder $query): void
    {
        $query->orderByRaw('valid_until IS NULL ASC')->orderBy('valid_until');
    }

    protected function defaultOrderBy(): array { return ['valid_until', 'asc']; }
    protected function perPage(): int          { return 20; }

    protected function viewData(mixed $entries): array
    {
        $base = BottlingAuthorization::where('user_id', $this->wineryId());

        $stats = [
            'total'    => (clone $base)->count(),
            'active'   => (clone $base)->where('status', 'active')->count(),
            'expiring' => (clone $base)->where('status', 'active')->whereNotNull('valid_until')
                ->whereBetween('valid_until', [today(), today()->addDays(90)])->count(),
            'expired'  => (clone $base)->where('status', 'expired')->count(),
        ];

        return [
            'authorizations' => $entries,
            'types'          => BottlingAuthorization::AUTHORIZATION_TYPES,
            'statuses'       => BottlingAuthorization::STATUSES,
            'stats'          => $stats,
        ];
    }
}
