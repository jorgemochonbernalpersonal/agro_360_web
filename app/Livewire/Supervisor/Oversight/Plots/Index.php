<?php

namespace App\Livewire\Supervisor\Oversight\Plots;

use App\Models\Plot;
use App\Models\SupervisorViticulturist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search         = '';
    public string $filterVit      = '';
    public string $filterMunicip  = '';
    public string $filterOrganic  = '';
    public string $filterLocked   = '';

    protected $queryString = [
        'search'        => ['except' => ''],
        'filterVit'     => ['except' => ''],
        'filterMunicip' => ['except' => ''],
        'filterOrganic' => ['except' => ''],
        'filterLocked'  => ['except' => ''],
    ];

    public function updatingSearch(): void   { $this->resetPage(); }
    public function updatingFilterVit(): void { $this->resetPage(); }
    public function updatingFilterMunicip(): void { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->search        = '';
        $this->filterVit     = '';
        $this->filterMunicip = '';
        $this->filterOrganic = '';
        $this->filterLocked  = '';
        $this->resetPage();
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $supervisorId = Auth::id();

        $viticulturistIds = SupervisorViticulturist::where('supervisor_id', $supervisorId)
            ->pluck('viticulturist_id');

        $query = Plot::whereIn('viticulturist_id', $viticulturistIds)
            ->where('active', true)
            ->with([
                'viticulturist:id,name',
                'municipality:id,name',
                'plantings' => fn($q) => $q->where('status', 'active')->with('grapeVariety:id,name'),
                'lastAgriculturalActivity',
            ]);

        if ($this->search) {
            $s = '%' . strtolower($this->search) . '%';
            $query->where(fn($q) => $q
                ->whereRaw('LOWER(plots.name) LIKE ?', [$s])
                ->orWhereRaw('LOWER(plots.code_parcel) LIKE ?', [$s])
            );
        }

        if ($this->filterVit) {
            $query->where('viticulturist_id', $this->filterVit);
        }

        if ($this->filterMunicip) {
            $query->where('municipality_id', $this->filterMunicip);
        }

        if ($this->filterOrganic !== '') {
            $query->where('is_organic', (bool) $this->filterOrganic);
        }

        if ($this->filterLocked !== '') {
            $query->where('is_locked', (bool) $this->filterLocked);
        }

        $plots = $query->orderBy('plots.name')->paginate(20);

        // Stats globales (sin filtros)
        $globalStats = DB::table('plots')
            ->whereIn('viticulturist_id', $viticulturistIds)
            ->where('active', true)
            ->selectRaw('
                COUNT(*) as total_plots,
                COALESCE(SUM(area), 0) as total_area,
                COALESCE(SUM(pac_eligible_area), 0) as total_eligible,
                SUM(CASE WHEN is_locked = 1 THEN 1 ELSE 0 END) as locked_count,
                SUM(CASE WHEN pac_eligible_area IS NULL THEN 1 ELSE 0 END) as without_pac,
                SUM(CASE WHEN is_organic = 1 THEN 1 ELSE 0 END) as organic_count
            ')
            ->first();

        // Lista de viticultores para filtro
        $viticulturists = \App\Models\User::whereIn('id', $viticulturistIds)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('livewire.supervisor.oversight.plots.index', [
            'plots'           => $plots,
            'globalStats'     => $globalStats,
            'viticulturists'  => $viticulturists,
        ]);
    }
}
