<?php

namespace App\Livewire\Supervisor\Oversight\Notebook;

use App\Models\AgriculturalActivity;
use App\Models\Plot;
use App\Models\SupervisorViticulturist;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $filterVit = '';

    public string $filterPlot = '';

    public string $filterType = '';

    public string $filterFrom = '';

    public string $filterTo = '';

    protected $queryString = [
        'filterVit' => ['except' => ''],
        'filterPlot' => ['except' => ''],
        'filterType' => ['except' => ''],
        'filterFrom' => ['except' => ''],
        'filterTo' => ['except' => ''],
    ];

    public function updatingFilterVit(): void
    {
        $this->filterPlot = '';
        $this->resetPage();
    }

    public function updatingFilterType(): void
    {
        $this->resetPage();
    }

    public function updatingFilterFrom(): void
    {
        $this->resetPage();
    }

    public function updatingFilterTo(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->filterVit = '';
        $this->filterPlot = '';
        $this->filterType = '';
        $this->filterFrom = '';
        $this->filterTo = '';
        $this->resetPage();
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $supervisorId = Auth::id();

        // Viticultores con acceso al cuaderno concedido por este supervisor
        $accessibleVitIds = SupervisorViticulturist::where('supervisor_id', $supervisorId)
            ->where('notebook_access', true)
            ->pluck('viticulturist_id');

        $viticulturists = User::whereIn('id', $accessibleVitIds)
            ->orderBy('name')
            ->get(['id', 'name']);

        // Parcelas del viticultor seleccionado (para filtro de parcela)
        $plots = collect();
        if ($this->filterVit) {
            $plots = Plot::where('viticulturist_id', $this->filterVit)
                ->where('active', true)
                ->orderBy('name')
                ->get(['id', 'name']);
        }

        $query = AgriculturalActivity::whereIn('viticulturist_id', $accessibleVitIds)
            ->with(['viticulturist:id,name', 'plot:id,name'])
            ->orderByDesc('activity_date');

        if ($this->filterVit) {
            $query->where('viticulturist_id', $this->filterVit);
        }

        if ($this->filterPlot) {
            $query->where('plot_id', $this->filterPlot);
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

        $activities = $query->paginate(25);

        $totalWithAccess = $accessibleVitIds->count();
        $activityTypes = AgriculturalActivity::activityTypes();

        return view('livewire.supervisor.oversight.notebook.index', [
            'activities' => $activities,
            'viticulturists' => $viticulturists,
            'plots' => $plots,
            'activityTypes' => $activityTypes,
            'totalWithAccess' => $totalWithAccess,
        ]);
    }
}
