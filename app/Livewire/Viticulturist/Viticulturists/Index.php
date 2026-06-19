<?php

namespace App\Livewire\Viticulturist\Viticulturists;

use App\Livewire\Concerns\WithToastNotifications;
use App\Livewire\Concerns\WithUserFilters;
use App\Livewire\Viticulturist\Viticulturists\Traits\WithViticulturistInvitation;
use App\Models\Campaign;
use App\Models\Crew;
use App\Models\CrewMember;
use App\Models\Payment;
use App\Models\Plot;
use App\Models\Subscription;
use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * @property-read mixed $viticulturists
 */
class Index extends Component
{
    use WithPagination, WithToastNotifications, WithUserFilters, WithViticulturistInvitation;

    public string $search = '';

    public string $assignToCrewId = '';

    protected $paginationTheme = 'tailwind';

    protected $queryString = ['search'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    public function assignToCrew(int $viticulturistId): void
    {
        if (empty($viticulturistId) || empty($this->assignToCrewId)) {
            $this->toastError(__('Debes seleccionar una cuadrilla.'));

            return;
        }

        $user = Auth::user();

        $crew = Crew::forViticulturist($user->id)
            ->where('id', $this->assignToCrewId)
            ->first();

        if (! $crew) {
            $this->toastError(__('No tienes permiso para gestionar esta cuadrilla.'));

            return;
        }

        $member = CrewMember::where('viticulturist_id', $viticulturistId)->first();

        if ($member && $member->crew_id === $crew->id) {
            $this->toastError(__('Este viticultor ya forma parte de esta cuadrilla.'));

            return;
        }

        try {
            if (! $member) {
                CrewMember::create([
                    'viticulturist_id' => $viticulturistId,
                    'crew_id' => $crew->id,
                    'assigned_by' => $user->id,
                ]);
            } else {
                $member->update([
                    'crew_id' => $crew->id,
                    'assigned_by' => $user->id,
                ]);
            }

            $this->assignToCrewId = '';
            $this->toastSuccess(__('Viticultor asignado a la cuadrilla correctamente.'));
        } catch (\Exception $e) {
            \Log::error('Error al asignar viticultor a cuadrilla', [
                'error' => $e->getMessage(),
                'viticulturist_id' => $viticulturistId,
                'crew_id' => $this->assignToCrewId,
                'user_id' => $user->id,
            ]);
            $this->toastError(__('Error al asignar el viticultor a la cuadrilla. Por favor, intenta de nuevo.'));
        }
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $user = Auth::user();

        $allVisibleViticulturists = $this->viticulturists;
        $visibleIds = $allVisibleViticulturists->pluck('id');

        $query = User::query()
            ->where('role', 'viticulturist')
            ->whereIn('id', $visibleIds);

        if ($this->search) {
            $search = '%'.strtolower($this->search).'%';
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', [$search])
                    ->orWhereRaw('LOWER(email) LIKE ?', [$search]);
            });
        }

        $viticulturists = $query->orderBy('name')->paginate(10);

        $crews = Crew::forViticulturist($user->id)->orderBy('name')->get();
        $wineries = $user->wineries;

        $membersByViticulturist = CrewMember::with('crew')
            ->whereIn('viticulturist_id', $viticulturists->pluck('id'))
            ->get()
            ->keyBy('viticulturist_id');

        $allIds = $allVisibleViticulturists->pluck('id');
        $stats = [
            'total' => $allIds->count(),
            'with_crew' => CrewMember::whereIn('viticulturist_id', $allIds)->distinct('viticulturist_id')->count(),
            'with_access' => User::whereIn('id', $allIds)->where('can_login', true)->count(),
        ];

        $mySubVitIds = WineryViticulturist::where('parent_viticulturist_id', Auth::id())
            ->pluck('viticulturist_id')
            ->toArray();

        return view('livewire.viticulturist.viticulturists.index', [
            'viticulturists' => $viticulturists,
            'crews' => $crews,
            'wineries' => $wineries,
            'membersByViticulturist' => $membersByViticulturist,
            'stats' => $stats,
            'mySubVitIds' => $mySubVitIds,
        ]);
    }

    public function delete($viticulturistId)
    {
        $user = Auth::user();

        $relation = WineryViticulturist::where('viticulturist_id', $viticulturistId)
            ->where('parent_viticulturist_id', $user->id)
            ->first();

        if (! $relation) {
            $this->toastError(__('No tienes permiso para eliminar este viticultor.'));

            return;
        }

        $hasPlots = Plot::where('viticulturist_id', $viticulturistId)->exists();
        $hasCampaigns = Campaign::where('viticulturist_id', $viticulturistId)->exists();
        $hasCrews = Crew::where('viticulturist_id', $viticulturistId)->exists();
        $hasSubs = Subscription::where('user_id', $viticulturistId)->exists();
        $hasPayments = Payment::where('user_id', $viticulturistId)->exists();
        $hasWineryRelations = WineryViticulturist::where('viticulturist_id', $viticulturistId)
            ->where(function ($q) use ($user) {
                $q->where('source', '!=', WineryViticulturist::SOURCE_VITICULTURIST)
                    ->orWhere('parent_viticulturist_id', '!=', $user->id);
            })
            ->exists();

        if ($hasPlots || $hasCampaigns || $hasCrews || $hasSubs || $hasPayments || $hasWineryRelations) {
            $this->toastError(__('No se puede eliminar el viticultor porque tiene datos relacionados.'));

            return;
        }

        $vit = User::find($viticulturistId);
        if (! $vit) {
            $this->toastError(__('Viticultor no encontrado.'));

            return;
        }

        try {
            $vit->delete();
            $this->toastSuccess(__('Viticultor eliminado correctamente.'));
        } catch (\Exception $e) {
            \Log::error('Error al eliminar viticultor', [
                'error' => $e->getMessage(),
                'viticulturist_id' => $viticulturistId,
                'user_id' => $user->id,
            ]);
            $this->toastError(__('Error al eliminar el viticultor. Por favor, intenta de nuevo.'));
        }
    }
}
