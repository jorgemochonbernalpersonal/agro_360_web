<?php

namespace App\Livewire\Winery\Cellar\WineLots;

use App\Livewire\Winery\AbstractIndex;
use App\Models\WineLot;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public string $search       = '';
    public string $typeFilter   = '';
    public string $statusFilter = 'active';

    protected $queryString = [
        'search'       => ['except' => ''],
        'typeFilter'   => ['except' => ''],
        'statusFilter' => ['except' => 'active'],
    ];

    public function updatingSearch(): void       { $this->resetPage(); }
    public function updatingTypeFilter(): void   { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }

    protected function filterDefaults(): array
    {
        return ['search' => '', 'typeFilter' => '', 'statusFilter' => 'active'];
    }

    public function archive(int $id): void
    {
        $lot = WineLot::where('user_id', $this->wineryId())->findOrFail($id);
        $lot->update(['archived' => true]);
        $this->toastSuccess("Lote «{$lot->name}» archivado.");
    }

    public function unarchive(int $id): void
    {
        $lot = WineLot::where('user_id', $this->wineryId())->findOrFail($id);
        $lot->update(['archived' => false]);
        $this->toastSuccess("Lote «{$lot->name}» reactivado.");
    }

    public function delete(int $id): void
    {
        $lot = WineLot::where('user_id', $this->wineryId())
            ->withCount('invoiceItems')
            ->findOrFail($id);

        if ($lot->invoice_items_count > 0) {
            $this->toastError('No se puede eliminar un lote que ya tiene líneas de factura.');
            return;
        }

        $lot->delete();
        $this->toastSuccess('Lote de vino eliminado.');
    }

    protected function baseQuery(): Builder
    {
        return WineLot::where('user_id', $this->wineryId());
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $term = '%' . mb_strtolower($this->search) . '%';
            $query->whereRaw('LOWER(name) LIKE ?', [$term]);
        }

        if ($this->typeFilter) {
            $query->where('wine_type', $this->typeFilter);
        }

        match ($this->statusFilter) {
            'active'   => $query->where('archived', false),
            'archived' => $query->where('archived', true),
            default    => null,
        };
    }

    protected function applyOrderBy(Builder $query): void
    {
        $query->orderByDesc('vintage')->orderBy('name');
    }

    protected function defaultOrderBy(): array { return ['vintage', 'desc']; }

    protected function perPage(): int { return 15; }

    protected function viewData(mixed $entries): array
    {
        return ['lots' => $entries];
    }
}
