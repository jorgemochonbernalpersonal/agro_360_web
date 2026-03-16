<?php

namespace App\Livewire\Supervisor\Oversight\Wineries;

use App\Models\SupervisorWinery;
use App\Models\User;
use App\Models\WineryViticulturist;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $vintageFilter = '';

    protected $queryString = [
        'search'        => ['except' => ''],
        'vintageFilter' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingVintageFilter(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search        = '';
        $this->vintageFilter = '';
        $this->resetPage();
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $doId = Auth::id();

        $wineryIds = SupervisorWinery::where('supervisor_id', $doId)->pluck('winery_id');

        // Available vintages for filter
        $availableVintages = DB::table('harvests')
            ->whereIn('winery_id', $wineryIds)
            ->select('vintage')
            ->distinct()
            ->orderByDesc('vintage')
            ->pluck('vintage');

        $vintage = $this->vintageFilter ?: ($availableVintages->first() ?? now()->year);

        // Harvest totals per winery for selected vintage
        $harvestStats = DB::table('harvests')
            ->whereIn('winery_id', $wineryIds)
            ->where('vintage', $vintage)
            ->select('winery_id', DB::raw('SUM(total_weight) as total_kg'), DB::raw('COUNT(*) as reception_count'))
            ->groupBy('winery_id')
            ->get()
            ->keyBy('winery_id');

        // Viticulturist count per winery via this DO
        $vitCountByWinery = WineryViticulturist::where('supervisor_id', $doId)
            ->where('source', WineryViticulturist::SOURCE_SUPERVISOR)
            ->select('winery_id', DB::raw('count(distinct viticulturist_id) as vit_count'))
            ->groupBy('winery_id')
            ->pluck('vit_count', 'winery_id');

        $query = User::whereIn('id', $wineryIds);

        if ($this->search) {
            $search = '%' . strtolower($this->search) . '%';
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(email) LIKE ?', [$search]);
            });
        }

        $wineries = $query->orderBy('name')->paginate(15);

        return view('livewire.supervisor.oversight.wineries.index', [
            'wineries'          => $wineries,
            'harvestStats'      => $harvestStats,
            'vitCountByWinery'  => $vitCountByWinery,
            'availableVintages' => $availableVintages,
            'vintage'           => $vintage,
            'totalWineries'     => $wineryIds->count(),
        ]);
    }
}
