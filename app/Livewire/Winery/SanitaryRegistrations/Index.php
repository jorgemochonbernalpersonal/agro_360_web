<?php

namespace App\Livewire\Winery\SanitaryRegistrations;

use App\Livewire\Winery\AbstractIndex;
use App\Models\SanitaryRegistration;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public string $search = '';

    public string $typeFilter = '';

    public string $statusFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'typeFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $registration = SanitaryRegistration::where('user_id', $this->wineryId())->findOrFail($id);
        $registration->delete();
        $this->toastSuccess(__('Registro sanitario eliminado.'));
    }

    protected function filterDefaults(): array
    {
        return ['search' => '', 'typeFilter' => '', 'statusFilter' => ''];
    }

    protected function baseQuery(): Builder
    {
        return SanitaryRegistration::where('user_id', $this->wineryId());
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $term = '%'.mb_strtolower($this->search).'%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(registration_number) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(IFNULL(activity_description, \'\')) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(IFNULL(issuing_authority, \'\')) LIKE ?', [$term]);
            });
        }
        if ($this->typeFilter) {
            $query->where('registration_type', $this->typeFilter);
        }
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }
    }

    protected function applyOrderBy(Builder $query): void
    {
        $query->orderBy('registration_number');
    }

    protected function defaultOrderBy(): array
    {
        return ['registration_number', 'asc'];
    }

    protected function perPage(): int
    {
        return 20;
    }

    protected function viewData(mixed $entries): array
    {
        $base = SanitaryRegistration::where('user_id', $this->wineryId());

        $stats = [
            'total' => (clone $base)->count(),
            'active' => (clone $base)->where('status', 'active')->count(),
            'expiring' => (clone $base)->where('status', 'active')->whereNotNull('renewal_date')
                ->whereBetween('renewal_date', [today(), today()->addDays(90)])->count(),
            'expired' => (clone $base)->where('status', 'expired')->count(),
        ];

        return [
            'registrations' => $entries,
            'types' => SanitaryRegistration::registrationTypeOptions(),
            'statuses' => SanitaryRegistration::statusOptions(),
            'stats' => $stats,
        ];
    }
}
