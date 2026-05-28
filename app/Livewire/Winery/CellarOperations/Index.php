<?php

namespace App\Livewire\Winery\CellarOperations;

use App\Livewire\Winery\AbstractIndex;
use App\Models\CellarOperation;
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

    public function updatingSearch(): void       { $this->resetPage(); }
    public function updatingTypeFilter(): void   { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }

    protected function filterDefaults(): array
    {
        return ['search' => '', 'typeFilter' => '', 'statusFilter' => ''];
    }

    public function delete(int $id): void
    {
        $operation = CellarOperation::where('user_id', $this->wineryId())->findOrFail($id);
        $operation->delete();
        $this->toastSuccess(__('Operación eliminada.'));
    }

    protected function baseQuery(): Builder
    {
        return CellarOperation::where('user_id', $this->wineryId())
            ->with(['sourceContainer', 'targetContainer']);
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $term = '%' . mb_strtolower($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(IFNULL(responsible_person, \'\')) LIKE ?', [$term])
                  ->orWhereRaw('LOWER(IFNULL(notes, \'\')) LIKE ?', [$term]);
            });
        }
        if ($this->typeFilter) {
            $query->where('operation_type', $this->typeFilter);
        }
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }
    }

    protected function applyOrderBy(Builder $query): void
    {
        $query->orderByDesc('operation_date')->orderByDesc('id');
    }

    protected function defaultOrderBy(): array { return ['operation_date', 'desc']; }
    protected function perPage(): int { return 20; }

    protected function viewData(mixed $entries): array
    {
        return [
            'operations' => $entries,
            'types'      => CellarOperation::operationTypeOptions(),
            'statuses'   => CellarOperation::statusOptions(),
        ];
    }
}
