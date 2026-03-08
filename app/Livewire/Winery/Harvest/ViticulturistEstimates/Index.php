<?php

namespace App\Livewire\Winery\Harvest\ViticulturistEstimates;

use App\Models\Campaign;
use App\Models\EstimatedYield;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search              = '';
    public string $viticulturistFilter = '';
    public string $vintageFilter       = '';
    public string $statusFilter        = '';
    public string $roundFilter         = '';

    protected $queryString = [
        'search'              => ['except' => ''],
        'viticulturistFilter' => ['except' => ''],
        'vintageFilter'       => ['except' => ''],
        'statusFilter'        => ['except' => ''],
        'roundFilter'         => ['except' => ''],
    ];

    public function updatingSearch(): void              { $this->resetPage(); }
    public function updatingViticulturistFilter(): void { $this->resetPage(); }
    public function updatingVintageFilter(): void       { $this->resetPage(); }
    public function updatingStatusFilter(): void        { $this->resetPage(); }
    public function updatingRoundFilter(): void         { $this->resetPage(); }

    public function mount(): void
    {
        if (!$this->vintageFilter) {
            $campaign = Campaign::forViticulturist(Auth::id())->where('active', true)->first();
            if ($campaign) {
                $this->vintageFilter = (string) $campaign->year;
            }
        }
    }

    public function render()
    {
        $wineryId = Auth::id();

        $viticulturistIds = WineryViticulturist::where('winery_id', $wineryId)
            ->pluck('viticulturist_id');

        $query = EstimatedYield::with([
                'plotPlanting.grapeVariety',
                'plotPlanting.plot',
                'plotPlanting.plot.viticulturist:id,name',
                'campaign',
            ])
            ->whereHas('plotPlanting.plot', fn($q) =>
                $q->whereIn('viticulturist_id', $viticulturistIds)
            )
            ->when($this->viticulturistFilter, fn($q) =>
                $q->whereHas('plotPlanting.plot', fn($q2) =>
                    $q2->where('viticulturist_id', $this->viticulturistFilter)
                )
            )
            ->when($this->vintageFilter, fn($q) =>
                $q->where(fn($q2) =>
                    $q2->where('vintage', $this->vintageFilter)
                       ->orWhereHas('campaign', fn($q3) => $q3->where('year', $this->vintageFilter))
                )
            )
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->roundFilter,  fn($q) => $q->where('estimation_round', $this->roundFilter))
            ->when($this->search, fn($q) => $q->where(fn($q2) =>
                $q2->whereHas('plotPlanting.grapeVariety', fn($q3) => $q3->where('name', 'like', '%' . $this->search . '%'))
                   ->orWhereHas('plotPlanting.plot', fn($q3) => $q3->where('name', 'like', '%' . $this->search . '%'))
                   ->orWhereHas('plotPlanting.plot.viticulturist', fn($q3) => $q3->where('name', 'like', '%' . $this->search . '%'))
            ));

        $estimates = (clone $query)
            ->orderByDesc('estimation_date')
            ->orderByDesc('estimation_round')
            ->paginate(25);

        $stats = [
            'total'     => (clone $query)->count(),
            'confirmed' => (clone $query)->where('status', 'confirmed')->count(),
            'total_kg'  => (clone $query)->where('status', 'confirmed')->sum('estimated_total_yield'),
        ];

        $linkedViticulturists = WineryViticulturist::where('winery_id', $wineryId)
            ->with('viticulturist:id,name')
            ->get()
            ->pluck('viticulturist')
            ->sortBy('name')
            ->values();

        $vintages = Campaign::whereIn('viticulturist_id', $viticulturistIds)
            ->orderByDesc('year')
            ->pluck('year')
            ->unique()
            ->values();

        return view('livewire.winery.harvest.viticulturist-estimates.index', [
            'estimates'            => $estimates,
            'linkedViticulturists' => $linkedViticulturists,
            'vintages'             => $vintages,
            'stats'                => $stats,
            'rounds'               => EstimatedYield::ROUNDS,
        ])->layout('layouts.app');
    }
}
