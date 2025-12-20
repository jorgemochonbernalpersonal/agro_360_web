<?php

namespace App\Livewire\Plots;

use App\Models\Plot;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $activeFilter = '';

    protected $queryString = ['search', 'activeFilter'];

    public function render()
    {
        $query = Plot::forUser(Auth::user())
            ->select([
                'id',
                'name',
                'description',
                'area',
                'active',
                // `winery_id` eliminado: la propiedad se deduce por viticultor
                'viticulturist_id',
                'municipality_id',
                'created_at',
                'updated_at',
            ])
            ->with([
                // 'winery:id,name', // relación ya no tiene columna física en plots
                'viticulturist:id,name',
                'municipality:id,name,province_id',
                'municipality.province:id,name',
                'sigpacCodes:id,code',
                'multiplePlotSigpacs:plot_id,plot_geometry_id'
            ]);

        if ($this->search) {
            $search = '%' . strtolower($this->search) . '%';
            $query->whereRaw('LOWER(name) LIKE ?', [$search]);
        }

        if ($this->activeFilter !== '') {
            $query->where('active', $this->activeFilter === '1');
        }

        $plots = $query->latest()->paginate(10);

        return view('livewire.plots.index', [
            'plots' => $plots,
        ])->layout('layouts.app');
    }

    public function delete(Plot $plot)
    {
        if (!Auth::user()->can('delete', $plot)) {
            abort(403);
        }

        $plot->delete();
        $this->toastSuccess('Parcela eliminada correctamente.');
    }
}
