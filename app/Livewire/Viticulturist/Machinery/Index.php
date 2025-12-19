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

    public $search = '';
    public $typeFilter = '';
    public $activeFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'typeFilter' => ['except' => ''],
        'activeFilter' => ['except' => ''],
    ];

    public function mount()
    {
        // Validar autorización
        if (!Auth::user()->can('viewAny', Machinery::class)) {
            abort(403, 'No tienes permiso para ver maquinaria.');
        }
    }

    public function render()
    {
        $user = Auth::user();

        $query = Machinery::forViticulturist($user->id)
            ->withCount('activities')
            ->with('viticulturist')
            ->orderBy('name');

        if ($this->search) {
            $search = '%' . strtolower($this->search) . '%';
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(brand) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(model) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(serial_number) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(roma_registration) LIKE ?', [$search]);
            });
        }

        if ($this->typeFilter) {
            $query->ofType($this->typeFilter);
        }

        if ($this->activeFilter !== '') {
            $query->where('active', $this->activeFilter === '1');
        }

        $machinery = $query->paginate(10);

        // Obtener tipos únicos para el filtro
        $types = Machinery::forViticulturist($user->id)
            ->select('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        return view('livewire.viticulturist.machinery.index', [
            'machinery' => $machinery,
            'types' => $types,
        ])->layout('layouts.app');
    }

    public function delete($machineryId)
    {
        $machinery = Machinery::withCount('activities')->findOrFail($machineryId);

        if (!Auth::user()->can('delete', $machinery)) {
            $this->toastError('No tienes permiso para eliminar esta maquinaria.');
            return;
        }

        // Validar que no tenga actividades
        if ($machinery->activities_count > 0) {
            $this->toastError('No se puede eliminar una maquinaria que tiene actividades registradas.');
            return;
        }

        try {
            $machinery->delete();
            $this->toastSuccess('Maquinaria eliminada correctamente.');
        } catch (\Exception $e) {
            \Log::error('Error al eliminar maquinaria', [
                'error' => $e->getMessage(),
                'machinery_id' => $machineryId,
                'user_id' => Auth::id(),
            ]);

            $this->toastError('Error al eliminar la maquinaria. Por favor, intenta de nuevo.');
        }
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->typeFilter = '';
        $this->activeFilter = '';
        $this->resetPage();
    }
}
