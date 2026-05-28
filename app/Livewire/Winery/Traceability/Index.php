<?php

namespace App\Livewire\Winery\Traceability;

use App\Models\Wine;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $vintageFilter = '';

    #[Url]
    public string $statusFilter = '';

    public function updatingSearch(): void        { $this->resetPage(); }
    public function updatingVintageFilter(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void  { $this->resetPage(); }

    public function resetFilters(): void
    {
        $this->search        = '';
        $this->vintageFilter = '';
        $this->statusFilter  = '';
        $this->resetPage();
    }

    public function render()
    {
        $wines = Wine::forUser(Auth::id())
            ->with(['oenologist', 'wineHarvests', 'processDetails', 'bottlings', 'labelings'])
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('internal_code', 'like', "%{$this->search}%"))
            ->when($this->vintageFilter, fn($q) => $q->where('vintage', $this->vintageFilter))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->orderBy('vintage', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $vintages = Wine::forUser(Auth::id())
            ->whereNotNull('vintage')
            ->distinct()
            ->orderBy('vintage', 'desc')
            ->pluck('vintage');

        return view('livewire.winery.traceability.index', [
            'wines'    => $wines,
            'vintages' => $vintages,
            'statuses' => Wine::statusOptions(),
        ]);
    }
}
