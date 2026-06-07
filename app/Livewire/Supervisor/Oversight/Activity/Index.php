<?php

namespace App\Livewire\Supervisor\Oversight\Activity;

use App\Models\AgriculturalActivity;
use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $filterVit = '';

    public string $filterType = '';

    public string $filterFrom = '';

    public string $filterTo = '';

    public bool $onlyAlerts = false;

    protected $queryString = [
        'filterVit' => ['except' => ''],
        'filterType' => ['except' => ''],
        'filterFrom' => ['except' => ''],
        'filterTo' => ['except' => ''],
        'onlyAlerts' => ['except' => false],
    ];

    public function updatingFilterVit(): void
    {
        $this->resetPage();
    }

    public function updatingFilterType(): void
    {
        $this->resetPage();
    }

    public function updatingOnlyAlerts(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->filterVit = '';
        $this->filterType = '';
        $this->filterFrom = '';
        $this->filterTo = '';
        $this->onlyAlerts = false;
        $this->resetPage();
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $supervisorId = Auth::id();

        // Viticultores con cuaderno accesible
        $accessibleVitIds = WineryViticulturist::where('source', WineryViticulturist::SOURCE_SUPERVISOR)
            ->where('supervisor_id', $supervisorId)
            ->where('notebook_access', true)
            ->distinct()
            ->pluck('viticulturist_id');

        // Todos los viticultores del DO (para filtro)
        $allVitIds = WineryViticulturist::where('source', WineryViticulturist::SOURCE_SUPERVISOR)
            ->where('supervisor_id', $supervisorId)
            ->distinct()
            ->pluck('viticulturist_id');

        $viticulturists = User::whereIn('id', $accessibleVitIds)
            ->orderBy('name')
            ->get(['id', 'name']);

        // Alertas activas (threshold excedido o follow_up vencido)
        $activeAlerts = DB::table('agricultural_activities as aa')
            ->join('observations as obs', 'obs.activity_id', '=', 'aa.id')
            ->whereIn('aa.viticulturist_id', $accessibleVitIds)
            ->where(fn ($q) => $q
                ->where('obs.threshold_exceeded', true)
                ->orWhere(fn ($q2) => $q2
                    ->whereNotNull('obs.follow_up_date')
                    ->whereDate('obs.follow_up_date', '<', now())
                )
            )
            ->count();

        // Stream de actividades
        $query = AgriculturalActivity::whereIn('viticulturist_id', $accessibleVitIds)
            ->with(['viticulturist:id,name', 'plot:id,name', 'observation'])
            ->orderByDesc('activity_date');

        if ($this->filterVit) {
            $query->where('viticulturist_id', $this->filterVit);
        }

        if ($this->filterType) {
            $query->where('activity_type', $this->filterType);
        }

        if ($this->filterFrom) {
            $query->whereDate('activity_date', '>=', $this->filterFrom);
        }

        if ($this->filterTo) {
            $query->whereDate('activity_date', '<=', $this->filterTo);
        }

        if ($this->onlyAlerts) {
            $query->where('activity_type', 'observation')
                ->whereHas('observation', fn ($q) => $q
                    ->where('threshold_exceeded', true)
                    ->orWhere(fn ($q2) => $q2
                        ->whereNotNull('follow_up_date')
                        ->whereDate('follow_up_date', '<', now())
                    )
                );
        }

        $activities = $query->paginate(25);

        $activityTypes = AgriculturalActivity::activityTypes();
        $totalWithAccess = $accessibleVitIds->count();
        $totalDo = $allVitIds->count();

        return view('livewire.supervisor.oversight.activity.index', [
            'activities' => $activities,
            'viticulturists' => $viticulturists,
            'activityTypes' => $activityTypes,
            'activeAlerts' => $activeAlerts,
            'totalWithAccess' => $totalWithAccess,
            'totalDo' => $totalDo,
        ]);
    }
}
