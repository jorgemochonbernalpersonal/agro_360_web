<?php

namespace App\Livewire\Viticulturist\Personal;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Crew;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public $search = '';

    public $wineryFilter = '';

    protected $queryString = ['search', 'wineryFilter'];

    public function render()
    {
        $user = Auth::user();

        if (! $user->can('viewAny', Crew::class)) {
            abort(403, __('No tienes permiso para ver cuadrillas.'));
        }

        $crews = Crew::forViticulturist($user->id)
            ->with(['winery', 'viticulturist'])
            ->withCount(['members', 'activities'])
            ->when($this->search, function ($query) {
                $search = '%'.strtolower($this->search).'%';
                $query->whereRaw('LOWER(name) LIKE ?', [$search])
                    ->orWhereRaw('LOWER(description) LIKE ?', [$search]);
            })
            ->when($this->wineryFilter, fn ($q) => $q->forWinery($this->wineryFilter))
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $wineries = $user->wineries;

        $base = Crew::forViticulturist($user->id)->withCount(['members', 'activities']);
        $stats = [
            'total' => Crew::forViticulturist($user->id)->count(),
            'members' => Crew::forViticulturist($user->id)->withCount('members')->get()->sum('members_count'),
            'with_activities' => Crew::forViticulturist($user->id)->has('activities')->count(),
        ];

        return view('livewire.viticulturist.personal.index', [
            'crews' => $crews,
            'wineries' => $wineries,
            'stats' => $stats,
        ]);
    }

    public function delete(Crew $crew)
    {
        if (! Auth::user()->can('delete', $crew)) {
            $this->toastError(__('No tienes permiso para eliminar esta cuadrilla.'));

            return;
        }

        if ($crew->activities()->exists()) {
            $this->toastError(__('No se puede eliminar una cuadrilla con actividades asociadas.'));

            return;
        }

        try {
            DB::transaction(function () use ($crew) {
                // Eliminar miembros primero
                $crew->members()->delete();
                $crew->delete();
            });

            $this->toastSuccess(__('Cuadrilla eliminada correctamente.'));
        } catch (\Exception $e) {
            Log::error('Error deleting crew', [
                'crew_id' => $crew->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->toastError(__('Hubo un error al eliminar la cuadrilla. Por favor, inténtalo de nuevo.'));
        }
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->wineryFilter = '';
        $this->resetPage();
    }
}
