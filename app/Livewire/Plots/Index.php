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
                'id', 'name', 'description', 'area', 'active',
                'winery_id', 'viticulturist_id', 'municipality_id',
                'created_at', 'updated_at'
            ])
            ->with([
                'winery:id,name',
                'viticulturist:id,name',
                'municipality:id,name,province_id',
                'municipality.province:id,name'
            ]);

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
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
        session()->flash('message', 'Parcela eliminada correctamente.');
    }
}
