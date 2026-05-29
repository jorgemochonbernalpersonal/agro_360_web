<?php

namespace App\Livewire\Admin\Sigpac;

use App\Models\SigpacCode;
use App\Livewire\Concerns\WithReadOnlyGuard;
use App\Livewire\Concerns\WithToastNotifications;
use App\Services\SecurityLogger;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithToastNotifications, WithReadOnlyGuard;

    public $search        = '';
    public $roleFilter    = 'all';
    protected $queryString = [
        'search', 'roleFilter',
    ];

    public function updatingSearch()       { $this->resetPage(); }
    public function updatingRoleFilter()   { $this->resetPage(); }

    public function deleteOrphaned()
    {
        if ($this->isReadOnly()) {
            return;
        }

        $count = SigpacCode::doesntHave('plots')->count();

        if ($count === 0) {
            $this->toastError(__('No hay códigos SIGPAC huérfanos.'));
            return;
        }

        SigpacCode::doesntHave('plots')->delete();

        SecurityLogger::logSecurityEvent('sigpac_orphaned_deleted', [
            'admin_id' => Auth::id(),
            'count'    => $count,
        ]);

        $this->toastSuccess("{$count} código(s) SIGPAC huérfanos eliminados.");
    }

    public function deleteSigpac($sigpacId)
    {
        if ($this->isReadOnly()) {
            return;
        }

        $sigpac = SigpacCode::findOrFail($sigpacId);
        $code   = $sigpac->code;

        SecurityLogger::logSecurityEvent('sigpac_deleted', [
            'admin_id'  => Auth::id(),
            'sigpac_id' => $sigpacId,
            'code'      => $code,
        ]);

        $sigpac->delete();
        $this->toastSuccess("Código SIGPAC {$code} eliminado.");
    }

    public function render()
    {
        $query = SigpacCode::query()
            ->with(['plots.viticulturist:id,name,email,role'])
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

        $realBase      = SigpacCode::query();
        $orphanedCount = SigpacCode::doesntHave('plots')->count();

        $stats = [
            'total'    => $realBase->count(),
            'orphaned' => $orphanedCount,
            'by_role'  => [
                'viticulturist' => (clone $realBase)->whereHas('plots.viticulturist', fn($q) => $q->where('role', 'viticulturist'))->count(),
                'winery'        => (clone $realBase)->whereHas('plots.viticulturist', fn($q) => $q->where('role', 'winery'))->count(),
                'supervisor'    => (clone $realBase)->whereHas('plots.viticulturist', fn($q) => $q->where('role', 'supervisor'))->count(),
            ],
        ];

        return view('livewire.admin.sigpac.index', [
            'sigpacs' => $sigpacs,
            'stats'   => $stats,
        ])->layout('layouts.app', [
            'title'       => __('SIGPACs - Admin - Agro365'),
            'description' => __('Visualiza todos los códigos SIGPAC del sistema'),
        ]);
    }
}
