<?php

namespace App\Livewire\Winery\ProductionCosts;

use App\Models\Wine;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $vintageFilter = '';

    #[Url(except: '')]
    public string $statusFilter = '';

    public function updatingVintageFilter(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }

    #[Layout('layouts.app')]
    public function render()
    {
        $userId = Auth::id();

        $query = Wine::where('user_id', $userId)
            ->whereNotIn('status', ['cancelled'])
            ->with([
                'costs:id,wine_id,category,amount',
                'productLots:id,wine_id,price_per_unit',
            ])
            ->when($this->vintageFilter, fn($q) => $q->where('vintage', $this->vintageFilter))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->orderByDesc('vintage')
            ->orderBy('name');

        $wines = $query->paginate(25);

        $availableVintages = Wine::where('user_id', $userId)
            ->whereNotIn('status', ['cancelled'])
            ->distinct()
            ->orderByDesc('vintage')
            ->pluck('vintage');

        // Totals for the header
        $totalWines      = $query->toBase()->getCountForPagination();
        $totalGrapeCost  = null; // expensive to compute across all wines; skipped in summary
        $totalManualCost = \App\Models\WineCost::where('user_id', $userId)->sum('amount');

        return view('livewire.winery.production-costs.index', [
            'wines'             => $wines,
            'availableVintages' => $availableVintages,
            'totalManualCost'   => (float) $totalManualCost,
            'statuses'          => Wine::STATUSES,
        ]);
    }
}
