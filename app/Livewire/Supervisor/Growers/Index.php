<?php

namespace App\Livewire\Supervisor\Growers;

use App\Models\SupervisorWinery;
use App\Models\WineryViticulturist;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $doId = Auth::id();

        $viticulturistIds = WineryViticulturist::where('supervisor_id', $doId)
            ->where('source', WineryViticulturist::SOURCE_SUPERVISOR)
            ->pluck('viticulturist_id')
            ->unique();

        $plotStatsByVit = DB::table('plots')
            ->whereIn('viticulturist_id', $viticulturistIds)
            ->where('active', true)
            ->select(
                'viticulturist_id',
                DB::raw('COUNT(*) as plot_count'),
                DB::raw('COALESCE(SUM(area), 0) as total_area')
            )
            ->groupBy('viticulturist_id')
            ->get()
            ->keyBy('viticulturist_id');

        $activePlantingsByVit = DB::table('plot_plantings')
            ->join('plots', 'plots.id', '=', 'plot_plantings.plot_id')
            ->whereIn('plots.viticulturist_id', $viticulturistIds)
            ->where('plot_plantings.status', 'active')
            ->select(
                'plots.viticulturist_id',
                DB::raw('COUNT(*) as planting_count'),
                DB::raw('COALESCE(SUM(plot_plantings.area_planted), 0) as planted_area')
            )
            ->groupBy('plots.viticulturist_id')
            ->get()
            ->keyBy('viticulturist_id');

        $wineryNamesByVit = WineryViticulturist::where('supervisor_id', $doId)
            ->where('source', WineryViticulturist::SOURCE_SUPERVISOR)
            ->whereIn('viticulturist_id', $viticulturistIds)
            ->with(['winery:id,name'])
            ->get()
            ->groupBy('viticulturist_id')
            ->map(fn($rows) => $rows->pluck('winery.name')->filter()->unique()->implode(', '));

        $query = User::whereIn('id', $viticulturistIds);

        if ($this->search) {
            $term = '%' . mb_strtolower($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(name) LIKE ?', [$term])
                  ->orWhereRaw('LOWER(email) LIKE ?', [$term]);
            });
        }

        $growers          = $query->orderBy('name')->paginate(15);
        $totalGrowerCount = $viticulturistIds->count();

        return view('livewire.supervisor.growers.index', [
            'growers'              => $growers,
            'plotStatsByVit'       => $plotStatsByVit,
            'activePlantingsByVit' => $activePlantingsByVit,
            'wineryNamesByVit'     => $wineryNamesByVit,
            'totalGrowerCount'     => $totalGrowerCount,
        ]);
    }
}
