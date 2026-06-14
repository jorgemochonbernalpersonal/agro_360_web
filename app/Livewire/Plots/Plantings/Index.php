<?php

namespace App\Livewire\Plots\Plantings;

use App\Livewire\Concerns\WithListing;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Plot;
use App\Models\PlotPlanting;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    use WithListing, WithToastNotifications;

    public $status = '';

    public $year = '';

    public $cropType = '';

    public $plotSearch = '';

    protected $queryString = [
        'status' => ['except' => ''],
        'year' => ['except' => ''],
        'cropType' => ['as' => 'crop', 'except' => ''],
    ];

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingYear()
    {
        $this->resetPage();
    }

    public function updatingCropType()
    {
        $this->resetPage();
    }

    public function toggleActive($plantingId)
    {
        $user = Auth::user();
        $visiblePlotIds = Plot::forUser($user)->pluck('id');
        $planting = PlotPlanting::whereIn('plot_id', $visiblePlotIds)->findOrFail($plantingId);

        $newActiveState = ! $planting->active;
        $planting->update(['active' => $newActiveState]);

        if ($newActiveState) {
            $this->toastSuccess(__('Plantación activada exitosamente.'));
            if ($this->currentTab === 'inactive') {
                $this->currentTab = 'active';
            }
        } else {
            $this->toastSuccess(__('Plantación desactivada exitosamente.'));
            if ($this->currentTab === 'active') {
                $this->currentTab = 'inactive';
            }
        }
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->status = '';
        $this->year = '';
        $this->cropType = '';
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();
        $visiblePlotIds = Plot::forUser($user)->pluck('id');

        // Winery puro (sin acceso de viticultor) solo ve variedades de uva/vino.
        // Viticulturist y producer ven todos los tipos de cultivo.
        $wineryOnly = $user->hasWineryAccess() && ! $user->hasViticulturistAccess();

        $query = PlotPlanting::with(['plot.viticulturist', 'plot.municipality', 'grapeVariety', 'trainingSystem'])
            ->whereIn('plot_id', $visiblePlotIds);

        if ($wineryOnly) {
            $query->whereHas('grapeVariety', fn ($q) => $q->where('crop_type', 'wine'));
        } elseif ($this->cropType !== '') {
            $query->whereHas('grapeVariety', fn ($q) => $q->where('crop_type', $this->cropType));
        }

        if ($this->search) {
            $search = '%'.strtolower($this->search).'%';
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', [$search])
                    ->orWhereHas('plot', fn ($sub) => $sub->whereRaw('LOWER(name) LIKE ?', [$search]))
                    ->orWhereHas('grapeVariety', fn ($sub) => $sub->whereRaw('LOWER(name) LIKE ?', [$search])
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

        $baseQuery = PlotPlanting::whereIn('plot_id', $visiblePlotIds);
        if ($wineryOnly) {
            $baseQuery->whereHas('grapeVariety', fn ($q) => $q->where('crop_type', 'wine'));
        }

        $years = (clone $baseQuery)
            ->whereNotNull('planting_year')
            ->distinct()
            ->orderByDesc('planting_year')
            ->pluck('planting_year');

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'active' => (clone $baseQuery)->where('active', true)->count(),
            'inactive' => (clone $baseQuery)->where('active', false)->count(),
            'total_area' => (clone $baseQuery)->sum('area_planted'),
        ];

        $selectablePlots = Plot::forUser($user)
            ->where('active', true)
            ->when($this->plotSearch, fn ($q) => $q->whereRaw('LOWER(name) LIKE ?', ['%'.strtolower($this->plotSearch).'%'])
            )
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('livewire.plots.plantings.index', compact('plantings', 'years', 'stats', 'wineryOnly', 'selectablePlots'))
            ->layout('layouts.app', [
                'title' => __('Plantaciones - Agro365'),
                'description' => __('Gestiona las plantaciones de tus parcelas. Variedades de uva, años de plantación, hectáreas y estado de cada viñedo.'),
            ]);
    }
}
