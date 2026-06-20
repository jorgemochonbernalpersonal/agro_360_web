<?php

namespace App\Livewire\Viticulturist\Personal;

use App\Livewire\Viticulturist\AbstractIndex;
use App\Models\Crew;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Index extends AbstractIndex
{
    public string $search = '';

    public string $wineryFilter = '';

    protected $queryString = ['search', 'wineryFilter'];

    protected function baseQuery(): Builder
    {
        return Crew::forViticulturist($this->viticulturistId())
            ->with(['winery', 'viticulturist'])
            ->withCount(['members', 'activities']);
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $search = '%'.strtolower($this->search).'%';
            $query->whereRaw('LOWER(name) LIKE ?', [$search])
                ->orWhereRaw('LOWER(description) LIKE ?', [$search]);
        }

        if ($this->wineryFilter) {
            $query->forWinery($this->wineryFilter);
        }
    }

    protected function defaultOrderBy(): array
    {
        return ['created_at', 'desc'];
    }

    protected function perPage(): int
    {
        return 10;
    }

    protected function filterDefaults(): array
    {
        return ['search' => '', 'wineryFilter' => ''];
    }

    protected function viewData(mixed $entries): array
    {
        return [
            'crews' => $entries,
            'wineries' => Auth::user()->wineries,
            'stats' => [
                'total' => Crew::forViticulturist($this->viticulturistId())->count(),
                'members' => Crew::forViticulturist($this->viticulturistId())->withCount('members')->get()->sum('members_count'),
                'with_activities' => Crew::forViticulturist($this->viticulturistId())->has('activities')->count(),
            ],
        ];
    }

    public function delete(Crew $crew): void
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
                $crew->members()->delete();
                $crew->delete();
            });

            $this->toastSuccess(__('Cuadrilla eliminada correctamente.'));
        } catch (\Exception $e) {
            Log::error('Error deleting crew', [
                'crew_id' => $crew->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            $this->toastError(__('Hubo un error al eliminar la cuadrilla. Por favor, inténtalo de nuevo.'));
        }
    }
}
