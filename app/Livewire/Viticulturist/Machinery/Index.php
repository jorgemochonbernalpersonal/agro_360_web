<?php

namespace App\Livewire\Viticulturist\Machinery;

use App\Models\Machinery;
use App\Livewire\Concerns\WithListing;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithListing, WithToastNotifications;

    public $typeFilter = '';

    protected $queryString = [
        'typeFilter' => ['except' => ''],
    ];

    public function updatingTypeFilter() { $this->resetPage(); }

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
            $this->toastSuccess(__('Maquinaria activada exitosamente.'));
            if ($this->currentTab === 'inactive') $this->currentTab = 'active';
        } else {
            $this->toastSuccess(__('Maquinaria desactivada exitosamente.'));
            if ($this->currentTab === 'active') $this->currentTab = 'inactive';
        }
    }

    public function delete(int $machineryId): void
    {
        $user      = Auth::user();
        $machinery = Machinery::forViticulturist($user->id)->findOrFail($machineryId);

        if (!$user->can('delete', $machinery)) {
            abort(403);
        }

        if ($machinery->activities()->exists()) {
            $this->toastError(__('No se puede eliminar maquinaria con actividades asociadas.'));
            return;
        }

        $machinery->delete();
        $this->toastSuccess(__('Maquinaria eliminada correctamente.'));
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

        // Una sola query para tipos y stats (evita 4 queries extra por render)
        $allMachinery = Machinery::forViticulturist($user->id)->select('active', 'type')->get();
        $types = $allMachinery->pluck('type')->filter()->unique()->sort()->values();
        $stats = [
            'total'       => $allMachinery->count(),
            'active'      => $allMachinery->where('active', true)->count(),
            'inactive'    => $allMachinery->where('active', false)->count(),
            'types_count' => $types->count(),
        ];

        return view('livewire.viticulturist.machinery.index', compact('machinery', 'types', 'stats'))
            ->layout('layouts.app', [
                'title'       => __('Maquinaria Agrícola - Agro365'),
                'description' => __('Gestiona tu flota de maquinaria agrícola. Control de equipos, mantenimiento y registro de uso en actividades del viñedo.'),
            ]);
    }
}
