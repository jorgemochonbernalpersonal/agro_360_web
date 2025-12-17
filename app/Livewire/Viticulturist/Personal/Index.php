<?php

namespace App\Livewire\Viticulturist\Personal;

use App\Models\Crew;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $wineryFilter = '';

    protected $queryString = ['search', 'wineryFilter'];

    public function render()
    {
        $user = Auth::user();
        
        if (!$user->can('viewAny', Crew::class)) {
            abort(403, 'No tienes permiso para ver cuadrillas.');
        }

        $crews = Crew::forViticulturist($user->id)
            ->with(['winery', 'viticulturist'])
            ->withCount(['members', 'activities'])
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->when($this->wineryFilter, fn($q) => $q->forWinery($this->wineryFilter))
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Obtener bodegas del viticultor usando relación
        $wineries = $user->wineries;

        return view('livewire.viticulturist.personal.index', [
            'crews' => $crews,
            'wineries' => $wineries,
        ])->layout('layouts.app');
    }

    public function delete(Crew $crew)
    {
        if (!Auth::user()->can('delete', $crew)) {
            session()->flash('error', 'No tienes permiso para eliminar esta cuadrilla.');
            return;
        }

        if ($crew->activities()->exists()) {
            session()->flash('error', 'No se puede eliminar una cuadrilla con actividades asociadas.');
            return;
        }

        try {
            DB::transaction(function () use ($crew) {
                // Eliminar miembros primero
                $crew->members()->delete();
                $crew->delete();
            });
            
            session()->flash('message', 'Cuadrilla eliminada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error deleting crew', [
                'crew_id' => $crew->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            session()->flash('error', 'Hubo un error al eliminar la cuadrilla. Por favor, inténtalo de nuevo.');
        }
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->wineryFilter = '';
        $this->resetPage();
    }
}

