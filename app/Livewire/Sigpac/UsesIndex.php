<?php

namespace App\Livewire\Sigpac;

use App\Models\SigpacUse;
use App\Models\Plot;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class UsesIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedUses = [];

    // Solo persistimos la búsqueda en la query string
    protected $queryString = ['search'];

    public function updatingSelectedUses()
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();
        
        // Obtener IDs de parcelas que el usuario puede ver
        $plotIds = Plot::forUser($user)->pluck('id');
        
        // Normalizar selección (array de IDs enteros)
        $selectedIds = collect($this->selectedUses)
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        // Query base: usos con parcelas visibles
        $query = SigpacUse::query()
            ->whereHas('plots', function($query) use ($plotIds) {
                $query->whereIn('plots.id', $plotIds);
            })
            ->when(!empty($selectedIds), function($query) use ($selectedIds) {
                $query->whereIn('id', $selectedIds);
            })
            ->when($this->search, function($query) {
                $search = '%' . strtolower($this->search) . '%';
                $query->where(function($q) use ($search) {
                    $q->whereRaw('LOWER(code) LIKE ?', [$search])
                      ->orWhereRaw('LOWER(description) LIKE ?', [$search]);
                });
            })
            ->with(['plots' => function($query) use ($plotIds) {
                $query->whereIn('plots.id', $plotIds)
                      ->select('plots.id', 'name');
            }])
            ->withCount(['plots' => function($query) use ($plotIds) {
                $query->whereIn('plots.id', $plotIds);
            }])
            ->orderBy('code');

        $uses = $query->paginate(10);

        // Opciones para el select múltiple (solo usos con parcelas visibles)
        $allUses = SigpacUse::query()
            ->whereHas('plots', function($query) use ($plotIds) {
                $query->whereIn('plots.id', $plotIds);
            })
            ->orderBy('code')
            ->get();

        return view('livewire.sigpac.uses-index', [
            'uses' => $uses,
            'allUses' => $allUses,
        ])->layout('layouts.app');
    }
}

