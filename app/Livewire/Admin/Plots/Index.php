<?php

namespace App\Livewire\Admin\Plots;

use App\Models\Plot;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $activeFilter = '';
    public $roleFilter = 'all';

    protected $queryString = ['search', 'activeFilter', 'roleFilter'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingActiveFilter()
    {
        $this->resetPage();
    }

    public function updatingRoleFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Plot::query()
            ->select([
                'id',
                'name',
                'description',
                'area',
                'active',
                'viticulturist_id',
                'municipality_id',
                'created_at',
                'updated_at',
            ])
            ->with([
                'viticulturist:id,name,email,role',
                'municipality:id,name,province_id',
                'municipality.province:id,name',
                'sigpacCodes:id,code',
            ]);

        // Búsqueda
        if ($this->search) {
            $search = '%' . strtolower($this->search) . '%';
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(description) LIKE ?', [$search])
                  ->orWhereHas('viticulturist', function($q) use ($search) {
                      $q->whereRaw('LOWER(name) LIKE ?', [$search])
                        ->orWhereRaw('LOWER(email) LIKE ?', [$search]);
                  });
            });
        }

        // Filtro por estado activo
        if ($this->activeFilter !== '') {
            $query->where('active', $this->activeFilter === '1');
        }

        // Filtro por rol del viticultor
        if ($this->roleFilter !== 'all') {
            $query->whereHas('viticulturist', function($q) {
                $q->where('role', $this->roleFilter);
            });
        }

        $plots = $query->latest()->paginate(20);

        // Estadísticas
        $stats = [
            'total' => Plot::count(),
            'active' => Plot::where('active', true)->count(),
            'total_area' => Plot::sum('area') ?? 0,
            'by_role' => [
                'viticulturist' => Plot::whereHas('viticulturist', fn($q) => $q->where('role', 'viticulturist'))->count(),
                'winery' => Plot::whereHas('viticulturist', fn($q) => $q->where('role', 'winery'))->count(),
                'supervisor' => Plot::whereHas('viticulturist', fn($q) => $q->where('role', 'supervisor'))->count(),
            ],
        ];

        return view('livewire.admin.plots.index', [
            'plots' => $plots,
            'stats' => $stats,
        ])->layout('layouts.app', [
            'title' => 'Parcelas - Admin - Agro365',
            'description' => 'Visualiza todas las parcelas del sistema',
        ]);
    }
}

