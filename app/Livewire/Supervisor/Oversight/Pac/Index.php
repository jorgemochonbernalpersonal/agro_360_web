<?php

namespace App\Livewire\Supervisor\Oversight\Pac;

use App\Models\Plot;
use App\Models\SupervisorViticulturist;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $filterVit = '';

    public string $filterStatus = ''; // 'ok', 'missing_pac', 'locked'

    protected $queryString = [
        'filterVit' => ['except' => ''],
        'filterStatus' => ['except' => ''],
    ];

    public function updatingFilterVit(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->filterVit = '';
        $this->filterStatus = '';
        $this->resetPage();
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $supervisorId = Auth::id();

        $viticulturistIds = SupervisorViticulturist::where('supervisor_id', $supervisorId)
            ->pluck('viticulturist_id');

        // Stats globales PAC
        $stats = DB::table('plots')
            ->whereIn('viticulturist_id', $viticulturistIds)
            ->where('active', true)
            ->selectRaw('
                COUNT(*) as total_plots,
                COALESCE(SUM(area), 0) as total_area,
                COALESCE(SUM(pac_eligible_area), 0) as total_eligible,
                SUM(CASE WHEN pac_eligible_area IS NOT NULL THEN 1 ELSE 0 END) as with_pac,
                SUM(CASE WHEN pac_eligible_area IS NULL THEN 1 ELSE 0 END) as without_pac,
                SUM(CASE WHEN is_locked = 1 THEN 1 ELSE 0 END) as locked_count,
                SUM(CASE WHEN eligibility_coefficient IS NOT NULL THEN 1 ELSE 0 END) as with_coeff
            ')
            ->first();

        // PAC compliance por viticultor
        $pacByVit = DB::table('plots')
            ->whereIn('viticulturist_id', $viticulturistIds)
            ->where('active', true)
            ->select(
                'viticulturist_id',
                DB::raw('COUNT(*) as total_plots'),
                DB::raw('COALESCE(SUM(area), 0) as total_area'),
                DB::raw('COALESCE(SUM(pac_eligible_area), 0) as eligible_area'),
                DB::raw('SUM(CASE WHEN pac_eligible_area IS NULL THEN 1 ELSE 0 END) as missing_pac'),
                DB::raw('SUM(CASE WHEN is_locked = 1 THEN 1 ELSE 0 END) as locked_plots')
            )
            ->groupBy('viticulturist_id')
            ->get()
            ->keyBy('viticulturist_id');

        $viticulturists = User::whereIn('id', $viticulturistIds)
            ->orderBy('name')
            ->get(['id', 'name']);

        // Tabla de parcelas con problemas PAC
        $query = Plot::whereIn('viticulturist_id', $viticulturistIds)
            ->where('active', true)
            ->with(['viticulturist:id,name', 'municipality:id,name']);

        if ($this->filterVit) {
            $query->where('viticulturist_id', $this->filterVit);
        }

        if ($this->filterStatus === 'missing_pac') {
            $query->whereNull('pac_eligible_area');
        } elseif ($this->filterStatus === 'locked') {
            $query->where('is_locked', true);
        } elseif ($this->filterStatus === 'ok') {
            $query->whereNotNull('pac_eligible_area')->where('is_locked', false);
        }

        $plots = $query->orderBy('plots.name')->paginate(20);

        return view('livewire.supervisor.oversight.pac.index', [
            'stats' => $stats,
            'pacByVit' => $pacByVit,
            'viticulturists' => $viticulturists,
            'plots' => $plots,
        ]);
    }
}
