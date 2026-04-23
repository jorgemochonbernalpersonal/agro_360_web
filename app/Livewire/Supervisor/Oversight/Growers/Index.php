<?php

namespace App\Livewire\Supervisor\Oversight\Growers;

use App\Models\SupervisorViticulturist;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        $viticulturistIds = SupervisorViticulturist::where('supervisor_id', $doId)
            ->pluck('viticulturist_id');

        // Last activity date per viticulturist
        $lastActivityByVit = DB::table('agricultural_activities')
            ->whereIn('viticulturist_id', $viticulturistIds)
            ->select('viticulturist_id', DB::raw('MAX(activity_date) as last_activity'))
            ->groupBy('viticulturist_id')
            ->pluck('last_activity', 'viticulturist_id');

        // Active plantings count per viticulturist
        $plantingCountByVit = DB::table('plot_plantings')
            ->join('plots', 'plots.id', '=', 'plot_plantings.plot_id')
            ->whereIn('plots.viticulturist_id', $viticulturistIds)
            ->where('plot_plantings.status', 'active')
            ->select('plots.viticulturist_id', DB::raw('count(*) as planting_count'))
            ->groupBy('plots.viticulturist_id')
            ->pluck('planting_count', 'viticulturist_id');

        $query = User::whereIn('id', $viticulturistIds)
            ->withCount('plots')
            ->withSum('plots', 'area');

        if ($this->search) {
            $search = '%' . strtolower($this->search) . '%';
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(email) LIKE ?', [$search]);
            });
        }

        $growers        = $query->orderBy('name')->paginate(15);
        $totalGrowers   = $viticulturistIds->count();

        return view('livewire.supervisor.oversight.growers.index', [
            'growers'            => $growers,
            'lastActivityByVit'  => $lastActivityByVit,
            'plantingCountByVit' => $plantingCountByVit,
            'totalGrowers'       => $totalGrowers,
        ]);
    }
}
