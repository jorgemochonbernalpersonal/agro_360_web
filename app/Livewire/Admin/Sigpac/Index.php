<?php

namespace App\Livewire\Admin\Sigpac;

use App\Models\SigpacCode;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $roleFilter = 'all';

    protected $queryString = ['search', 'roleFilter'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRoleFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = SigpacCode::query()
            ->with(['plots.viticulturist:id,name,email,role', 'use:id,name'])
            ->withCount('plots');

        // Búsqueda
        if ($this->search) {
            $search = '%' . strtolower($this->search) . '%';
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(code) LIKE ?', [$search])
                  ->orWhereHas('plots.viticulturist', function($q) use ($search) {
                      $q->whereRaw('LOWER(name) LIKE ?', [$search])
                        ->orWhereRaw('LOWER(email) LIKE ?', [$search]);
                  });
            });
        }

        // Filtro por rol del usuario
        if ($this->roleFilter !== 'all') {
            $query->whereHas('plots.viticulturist', function($q) {
                $q->where('role', $this->roleFilter);
            });
        }

        $sigpacs = $query->orderBy('code')->paginate(20);

        // Estadísticas
        $stats = [
            'total' => SigpacCode::count(),
            'by_role' => [
                'viticulturist' => SigpacCode::whereHas('plots.viticulturist', fn($q) => $q->where('role', 'viticulturist'))->count(),
                'winery' => SigpacCode::whereHas('plots.viticulturist', fn($q) => $q->where('role', 'winery'))->count(),
                'supervisor' => SigpacCode::whereHas('plots.viticulturist', fn($q) => $q->where('role', 'supervisor'))->count(),
            ],
        ];

        return view('livewire.admin.sigpac.index', [
            'sigpacs' => $sigpacs,
            'stats' => $stats,
        ])->layout('layouts.app', [
            'title' => 'SIGPACs - Admin - Agro365',
            'description' => 'Visualiza todos los códigos SIGPAC del sistema',
        ]);
    }
}

