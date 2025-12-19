<?php

namespace App\Livewire\Plots\Plantings;

use App\Models\Plot;
use App\Models\PlotPlanting;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $year = '';

    protected $queryString = ['search', 'status', 'year'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingYear()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->status = '';
        $this->year = '';
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();

        // Parcelas visibles para el usuario, reutilizando el scope existente
        $visiblePlotIds = Plot::forUser($user)->pluck('id');

        $query = PlotPlanting::with(['plot.viticulturist', 'plot.municipality', 'grapeVariety'])
            ->whereIn('plot_id', $visiblePlotIds);

        if ($this->search) {
            $search = '%' . strtolower($this->search) . '%';
            $query->where(function ($q) use ($search) {
                $q->whereHas('plot', function ($sub) use ($search) {
                    $sub->whereRaw('LOWER(name) LIKE ?', [$search]);
                })->orWhereHas('grapeVariety', function ($sub) use ($search) {
                    $sub->whereRaw('LOWER(name) LIKE ?', [$search])
                        ->orWhereRaw('LOWER(code) LIKE ?', [$search]);
                });
            });
        }

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        if ($this->year !== '') {
            $query->where('planting_year', $this->year);
        }

        $plantings = $query->orderByDesc('created_at')->paginate(10);

        $years = PlotPlanting::whereNotNull('planting_year')
            ->distinct()
            ->orderByDesc('planting_year')
            ->pluck('planting_year');

        return view('livewire.plots.plantings.index', [
            'plantings' => $plantings,
            'years' => $years,
        ])->layout('layouts.app');
    }
}


