<?php

namespace App\Livewire\Winery\EcoCertifications;

use App\Livewire\Winery\AbstractIndex;
use App\Models\EcoCertification;
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
        $certification = EcoCertification::where('user_id', $this->wineryId())->findOrFail($id);
        $certification->delete();
        $this->toastSuccess('Certificación eliminada.');
    }

    protected function baseQuery(): Builder
    {
        return EcoCertification::where('user_id', $this->wineryId());
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $term = '%' . mb_strtolower($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(name) LIKE ?', [$term])
                  ->orWhereRaw('LOWER(IFNULL(certifying_body, \'\')) LIKE ?', [$term])
                  ->orWhereRaw('LOWER(IFNULL(certificate_number, \'\')) LIKE ?', [$term]);
            });
        }
        if ($this->typeFilter) {
            $query->where('certification_type', $this->typeFilter);
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
        return [
            'certifications' => $entries,
            'types'          => EcoCertification::CERTIFICATION_TYPES,
            'statuses'       => EcoCertification::STATUSES,
        ];
    }
}
