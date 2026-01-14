<?php

namespace App\Livewire\Viticulturist\Personal;

use App\Models\Crew;
use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public $search = '';
    public $wineryFilter = '';

    protected $queryString = ['search', 'wineryFilter'];

    public function mount()
    {
        $user = Auth::user();
        
        // Auto-registrar viticultor si no tiene registro en WineryViticulturist
        if ($user && $user->isViticulturist()) {
            $hasRecord = \App\Models\WineryViticulturist::where('viticulturist_id', $user->id)->exists();
            
            if (!$hasRecord) {
                \App\Models\WineryViticulturist::create([
                    'winery_id' => null,
                    'viticulturist_id' => $user->id,
                    'source' => \App\Models\WineryViticulturist::SOURCE_SELF,
                    'parent_viticulturist_id' => null,
                    'assigned_by' => $user->id,
                ]);
                
                \Illuminate\Support\Facades\Log::info('Auto-registered viticulturist in WineryViticulturist', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
            }
        }
    }

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
                $search = '%' . strtolower($this->search) . '%';
                $query->whereRaw('LOWER(name) LIKE ?', [$search])
                      ->orWhereRaw('LOWER(description) LIKE ?', [$search]);
            })
            ->when($this->wineryFilter, fn($q) => $q->forWinery($this->wineryFilter))
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Obtener bodegas del viticultor usando relación
        $wineries = $user->wineries;

        return view('livewire.viticulturist.personal.index', [
            'crews' => $crews,
            'wineries' => $wineries,
        ]);
    }

    public function delete(Crew $crew)
    {
        if (!Auth::user()->can('delete', $crew)) {
            $this->toastError('No tienes permiso para eliminar esta cuadrilla.');
            return;
        }

        if ($crew->activities()->exists()) {
            $this->toastError('No se puede eliminar una cuadrilla con actividades asociadas.');
            return;
        }

        try {
            DB::transaction(function () use ($crew) {
                // Eliminar miembros primero
                $crew->members()->delete();
                $crew->delete();
            });
            
            $this->toastSuccess('Cuadrilla eliminada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error deleting crew', [
                'crew_id' => $crew->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->toastError('Hubo un error al eliminar la cuadrilla. Por favor, inténtalo de nuevo.');
        }
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->wineryFilter = '';
        $this->resetPage();
    }
}

