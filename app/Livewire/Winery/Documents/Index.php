<?php

namespace App\Livewire\Winery\Documents;

use App\Livewire\Winery\AbstractIndex;
use App\Models\WineryDocument;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public string $search     = '';
    public string $typeFilter = '';

    protected $queryString = [
        'search'     => ['except' => ''],
        'typeFilter' => ['except' => ''],
    ];

    public function updatingSearch(): void    { $this->resetPage(); }
    public function updatingTypeFilter(): void { $this->resetPage(); }

    protected function filterDefaults(): array
    {
        return ['search' => '', 'typeFilter' => ''];
    }

    public function delete(int $id): void
    {
        $document = WineryDocument::where('user_id', $this->wineryId())->findOrFail($id);
        $document->delete();
        $this->toastSuccess(__('Documento eliminado.'));
    }

    protected function baseQuery(): Builder
    {
        return WineryDocument::where('user_id', $this->wineryId());
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $term = '%' . mb_strtolower($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(title) LIKE ?', [$term])
                  ->orWhereRaw('LOWER(IFNULL(reference_number, \'\')) LIKE ?', [$term])
                  ->orWhereRaw('LOWER(IFNULL(issuing_authority, \'\')) LIKE ?', [$term]);
            });
        }
        if ($this->typeFilter) {
            $query->where('document_type', $this->typeFilter);
        }
    }

    protected function applyOrderBy(Builder $query): void
    {
        $query->orderBy('title');
    }

    protected function defaultOrderBy(): array { return ['title', 'asc']; }
    protected function perPage(): int          { return 20; }

    protected function viewData(mixed $entries): array
    {
        return [
            'documents' => $entries,
            'types'     => WineryDocument::documentTypeOptions(),
        ];
    }
}
