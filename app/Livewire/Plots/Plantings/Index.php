<?php

namespace App\Livewire\Plots\Plantings;

use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Livewire\Concerns\WithToastNotifications;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public $currentTab = 'active'; // 'active', 'inactive'
    public $search = '';
    public $status = '';
    public $year = '';

    protected $queryString = [
        'currentTab' => ['as' => 'tab', 'except' => 'active'],
        'search'     => ['except' => ''],
        'status'     => ['except' => ''],
        'year'       => ['except' => ''],
    ];

    public function updatingSearch() { $this->resetPage(); }
    public function updatingStatus() { $this->resetPage(); }
    public function updatingYear()   { $this->resetPage(); }

    public function switchTab($tab)
    {
        $this->currentTab = $tab;
        $this->resetPage();
    }

    public function toggleActive($plantingId)
    {
        $user = Auth::user();
        $visiblePlotIds = Plot::forUser($user)->pluck('id');
        $planting = PlotPlanting::whereIn('plot_id', $visiblePlotIds)->findOrFail($plantingId);

        $newActiveState = !$planting->active;
        $planting->update(['active' => $newActiveState]);

        if ($newActiveState) {
            $this->toastSuccess('Plantación activada exitosamente.');
            if ($this->currentTab === 'inactive') {
                $this->currentTab = 'active';
            }
        } else {
            $this->toastSuccess('Plantación desactivada exitosamente.');
            if ($this->currentTab === 'active') {
                $this->currentTab = 'inactive';
            }
        }
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->status = '';
        $this->year   = '';
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();
        $visiblePlotIds = Plot::forUser($user)->pluck('id');

        $query = PlotPlanting::with(['plot.viticulturist', 'plot.municipality', 'grapeVariety'])
            ->whereIn('plot_id', $visiblePlotIds);

        if ($this->search) {
            $search = '%' . strtolower($this->search) . '%';
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', [$search])
                  ->orWhereHas('plot', fn($sub) => $sub->whereRaw('LOWER(name) LIKE ?', [$search]))
                  ->orWhereHas('grapeVariety', fn($sub) =>
                      $sub->whereRaw('LOWER(name) LIKE ?', [$search])
                          ->orWhereRaw('LOWER(code) LIKE ?', [$search])
                  );
            });
        }

        if ($this->currentTab === 'active') {
            $query->where('active', true);
        } elseif ($this->currentTab === 'inactive') {
            $query->where('active', false);
        }

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        if ($this->year !== '') {
            $query->where('planting_year', $this->year);
        }

        $plantings = $query->orderByDesc('created_at')->paginate(12);

        $years = PlotPlanting::whereIn('plot_id', $visiblePlotIds)
            ->whereNotNull('planting_year')
            ->distinct()
            ->orderByDesc('planting_year')
            ->pluck('planting_year');

        $baseQuery = PlotPlanting::whereIn('plot_id', $visiblePlotIds);
        $stats = [
            'total'      => (clone $baseQuery)->count(),
            'active'     => (clone $baseQuery)->where('active', true)->count(),
            'inactive'   => (clone $baseQuery)->where('active', false)->count(),
            'total_area' => (clone $baseQuery)->sum('area_planted'),
        ];

        return view('livewire.plots.plantings.index', compact('plantings', 'years', 'stats'))
            ->layout('layouts.app', [
                'title'       => 'Plantaciones - Agro365',
                'description' => 'Gestiona las plantaciones de tus parcelas. Variedades de uva, años de plantación, hectáreas y estado de cada viñedo.',
            ]);
    }
}
