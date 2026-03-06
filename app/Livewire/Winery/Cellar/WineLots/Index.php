<?php

namespace App\Livewire\Winery\Cellar\WineLots;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\WineLot;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public string $search        = '';
    public string $typeFilter    = '';
    public string $statusFilter  = 'active';

    protected $queryString = [
        'search'       => ['except' => ''],
        'typeFilter'   => ['except' => ''],
        'statusFilter' => ['except' => 'active'],
    ];

    public function updatingSearch(): void       { $this->resetPage(); }
    public function updatingTypeFilter(): void   { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->search       = '';
        $this->typeFilter   = '';
        $this->statusFilter = 'active';
        $this->resetPage();
    }

    public function archive(int $id): void
    {
        $lot = WineLot::where('user_id', Auth::id())->findOrFail($id);
        $lot->update(['archived' => true]);
        $this->toastSuccess("Lote «{$lot->name}» archivado.");
    }

    public function unarchive(int $id): void
    {
        $lot = WineLot::where('user_id', Auth::id())->findOrFail($id);
        $lot->update(['archived' => false]);
        $this->toastSuccess("Lote «{$lot->name}» reactivado.");
    }

    public function delete(int $id): void
    {
        $lot = WineLot::where('user_id', Auth::id())
            ->withCount('invoiceItems')
            ->findOrFail($id);

        if ($lot->invoice_items_count > 0) {
            $this->toastError('No se puede eliminar un lote que ya tiene líneas de factura.');
            return;
        }

        $lot->delete();
        $this->toastSuccess('Lote de vino eliminado.');
    }

    public function render()
    {
        $wineryId = Auth::id();

        $query = WineLot::where('user_id', $wineryId)->orderByDesc('vintage')->orderBy('name');

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

        $lots = $query->paginate(15);

        $statsBase = WineLot::where('user_id', $wineryId)->where('archived', false);

        $stats = [
            'total'           => (clone $statsBase)->count(),
            'total_quantity'  => (float) (clone $statsBase)->sum('quantity'),
            'total_available' => (float) (clone $statsBase)->sum('available_quantity'),
            'total_reserved'  => (float) (clone $statsBase)->sum('reserved_quantity'),
            'total_sold'      => (float) (clone $statsBase)->sum('sold_quantity'),
        ];

        return view('livewire.winery.cellar.wine-lots.index', [
            'lots'  => $lots,
            'stats' => $stats,
        ])->layout('layouts.app');
    }
}
