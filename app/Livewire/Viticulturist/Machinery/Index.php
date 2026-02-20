<?php

namespace App\Livewire\Viticulturist\Machinery;

use App\Models\Machinery;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public $currentTab = 'active'; // 'active', 'inactive'
    public $search     = '';
    public $typeFilter = '';

    protected $queryString = [
        'currentTab' => ['as' => 'tab', 'except' => 'active'],
        'search'     => ['except' => ''],
        'typeFilter' => ['except' => ''],
    ];

    public function updatingSearch()     { $this->resetPage(); }
    public function updatingTypeFilter() { $this->resetPage(); }

    public function switchTab($tab)
    {
        $this->currentTab = $tab;
        $this->resetPage();
    }

    public function toggleActive($machineryId)
    {
        $user     = Auth::user();
        $machinery = Machinery::forViticulturist($user->id)->findOrFail($machineryId);

        if (!$user->can('update', $machinery)) {
            abort(403);
        }

        $newActive = !$machinery->active;
        $machinery->update(['active' => $newActive]);

        if ($newActive) {
            $this->toastSuccess('Maquinaria activada exitosamente.');
            if ($this->currentTab === 'inactive') $this->currentTab = 'active';
        } else {
            $this->toastSuccess('Maquinaria desactivada exitosamente.');
            if ($this->currentTab === 'active') $this->currentTab = 'inactive';
        }
    }

    public function clearFilters()
    {
        $this->search     = '';
        $this->typeFilter = '';
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();

        $query = Machinery::forViticulturist($user->id)
            ->withCount('activities')
            ->orderBy('name');

        if ($this->search) {
            $search = '%' . strtolower($this->search) . '%';
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(brand) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(model) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(roma_registration) LIKE ?', [$search]);
            });
        }

        if ($this->typeFilter) {
            $query->ofType($this->typeFilter);
        }

        if ($this->currentTab === 'active') {
            $query->where('active', true);
        } elseif ($this->currentTab === 'inactive') {
            $query->where('active', false);
        }

        $machinery = $query->paginate(12);

        $types = Machinery::forViticulturist($user->id)
            ->select('type')
            ->whereNotNull('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        $baseQuery = Machinery::forViticulturist($user->id);
        $stats = [
            'total'    => (clone $baseQuery)->count(),
            'active'   => (clone $baseQuery)->where('active', true)->count(),
            'inactive' => (clone $baseQuery)->where('active', false)->count(),
        ];

        return view('livewire.viticulturist.machinery.index', compact('machinery', 'types', 'stats'))
            ->layout('layouts.app', [
                'title'       => 'Maquinaria Agrícola - Agro365',
                'description' => 'Gestiona tu flota de maquinaria agrícola. Control de equipos, mantenimiento y registro de uso en actividades del viñedo.',
            ]);
    }
}
