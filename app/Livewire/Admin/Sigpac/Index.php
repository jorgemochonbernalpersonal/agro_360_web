<?php

namespace App\Livewire\Admin\Sigpac;

use App\Models\SigpacCode;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public $search     = '';
    public $roleFilter = 'all';

    protected $queryString = ['search', 'roleFilter'];

    public function updatingSearch()    { $this->resetPage(); }
    public function updatingRoleFilter(){ $this->resetPage(); }

    public function deleteOrphaned()
    {
        $count = SigpacCode::doesntHave('plots')->count();

        if ($count === 0) {
            $this->toastError('No hay códigos SIGPAC huérfanos.');
            return;
        }

        SigpacCode::doesntHave('plots')->delete();
        $this->toastSuccess("{$count} código(s) SIGPAC huérfanos eliminados.");
    }

    public function deleteSigpac($sigpacId)
    {
        $sigpac = SigpacCode::findOrFail($sigpacId);
        $code   = $sigpac->code;
        $sigpac->delete();
        $this->toastSuccess("Código SIGPAC {$code} eliminado.");
    }

    public function render()
    {
        $query = SigpacCode::query()
            ->with(['plots.viticulturist:id,name,email,role', 'use:id,name'])
            ->withCount('plots');

        if ($this->search) {
            $search = '%' . strtolower($this->search) . '%';
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(code) LIKE ?', [$search])
                  ->orWhereHas('plots.viticulturist', function ($q) use ($search) {
                      $q->whereRaw('LOWER(name) LIKE ?', [$search])
                        ->orWhereRaw('LOWER(email) LIKE ?', [$search]);
                  });
            });
        }

        if ($this->roleFilter !== 'all') {
            $query->whereHas('plots.viticulturist', fn($q) => $q->where('role', $this->roleFilter));
        }

        $sigpacs = $query->orderBy('code')->paginate(20);

        $orphanedCount = SigpacCode::doesntHave('plots')->count();

        $stats = [
            'total'    => SigpacCode::count(),
            'orphaned' => $orphanedCount,
            'by_role'  => [
                'viticulturist' => SigpacCode::whereHas('plots.viticulturist', fn($q) => $q->where('role', 'viticulturist'))->count(),
                'winery'        => SigpacCode::whereHas('plots.viticulturist', fn($q) => $q->where('role', 'winery'))->count(),
                'supervisor'    => SigpacCode::whereHas('plots.viticulturist', fn($q) => $q->where('role', 'supervisor'))->count(),
            ],
        ];

        return view('livewire.admin.sigpac.index', [
            'sigpacs' => $sigpacs,
            'stats'   => $stats,
        ])->layout('layouts.app', [
            'title'       => 'SIGPACs - Admin - Agro365',
            'description' => 'Visualiza todos los códigos SIGPAC del sistema',
        ]);
    }
}
