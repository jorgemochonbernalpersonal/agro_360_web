<?php

namespace App\Livewire\Viticulturist\Viticulturists;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\Plot;
use App\Models\Campaign;
use App\Models\Crew;
use App\Models\CrewMember;
use App\Models\Subscription;
use App\Models\Payment;
use App\Models\WineryViticulturist;
use App\Livewire\Concerns\WithUserFilters;
use App\Livewire\Concerns\WithToastNotifications;

class Index extends Component
{
    use WithPagination, WithUserFilters, WithToastNotifications;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $assignToCrewId = '';

    protected $queryString = ['search'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->resetPage();
    }

    public function assignToCrew(int $viticulturistId): void
    {
        if (empty($viticulturistId) || empty($this->assignToCrewId)) {
            $this->toastError('Debes seleccionar una cuadrilla.');
            return;
        }

        $user = Auth::user();

        // Verificar que la cuadrilla pertenece al viticultor actual
        $crew = Crew::forViticulturist($user->id)
            ->where('id', $this->assignToCrewId)
            ->first();

        if (! $crew) {
            $this->toastError('No tienes permiso para gestionar esta cuadrilla.');
            return;
        }

        // Evitar duplicados de CrewMember por viticultor
        $member = CrewMember::where('viticulturist_id', $viticulturistId)->first();

        if ($member && $member->crew_id === $crew->id) {
            $this->toastError('Este viticultor ya forma parte de esta cuadrilla.');
            return;
        }

        try {
            if (! $member) {
                // Crear nuevo trabajador directamente asignado a la cuadrilla
                CrewMember::create([
                    'viticulturist_id' => $viticulturistId,
                    'crew_id' => $crew->id,
                    'assigned_by' => $user->id,
                ]);
            } else {
                // Actualizar trabajador existente (individual u otra cuadrilla) a esta cuadrilla
                $member->update([
                    'crew_id' => $crew->id,
                    'assigned_by' => $user->id,
                ]);
            }

            $this->assignToCrewId = '';

            $this->toastSuccess('Viticultor asignado a la cuadrilla correctamente.');
        } catch (\Exception $e) {
            \Log::error('Error al asignar viticultor a cuadrilla', [
                'error' => $e->getMessage(),
                'viticulturist_id' => $viticulturistId,
                'crew_id' => $this->assignToCrewId,
                'user_id' => $user->id,
            ]);

            $this->toastError('Error al asignar el viticultor a la cuadrilla. Por favor, intenta de nuevo.');
        }
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $user = Auth::user();

        // Usar el trait WithUserFilters para obtener todos los viticultores visibles
        // Esto incluye: el usuario mismo, los que creó, los de sus bodegas, y los del supervisor
        $allVisibleViticulturists = $this->viticulturists;
        $visibleIds = $allVisibleViticulturists->pluck('id');

        $query = User::query()
            ->where('role', 'viticulturist')
            ->whereIn('id', $visibleIds);

        if ($this->search) {
            $search = '%' . strtolower($this->search) . '%';
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(email) LIKE ?', [$search]);
            });
        }

        $viticulturists = $query
            ->orderBy('name')
            ->paginate(10);

        // Cuadrillas disponibles del viticultor actual
        $crews = Crew::forViticulturist($user->id)
            ->orderBy('name')
            ->get();

        // Bodegas del viticultor actual (para jerarquía opcional por bodega)
        $wineries = $user->wineries;

        // Mapa de viticultor -> miembro de cuadrilla (si existe)
        $membersByViticulturist = CrewMember::with('crew')
            ->whereIn('viticulturist_id', $viticulturists->pluck('id'))
            ->get()
            ->keyBy('viticulturist_id');

        return view('livewire.viticulturist.viticulturists.index', [
            'viticulturists' => $viticulturists,
            'crews' => $crews,
            'wineries' => $wineries,
            'membersByViticulturist' => $membersByViticulturist,
        ]);
    }

    public function delete($viticulturistId)
    {
        $user = Auth::user();

        // Verify this user can delete the viticulturist: must be the creator (via winery_viticulturist record)
        $relation = WineryViticulturist::where('viticulturist_id', $viticulturistId)
            ->where('parent_viticulturist_id', $user->id)
            ->first();

        if (!$relation) {
            $this->toastError('No tienes permiso para eliminar este viticultor.');
            return;
        }

        // Check dependent records that would block deletion
        $hasPlots = Plot::where('viticulturist_id', $viticulturistId)->exists();
        $hasCampaigns = Campaign::where('viticulturist_id', $viticulturistId)->exists();
        $hasCrews = Crew::where('viticulturist_id', $viticulturistId)->exists();
        $hasSubs = Subscription::where('user_id', $viticulturistId)->exists();
        $hasPayments = Payment::where('user_id', $viticulturistId)->exists();
        $hasWineryRelations = WineryViticulturist::where('viticulturist_id', $viticulturistId)->exists();

        if ($hasPlots || $hasCampaigns || $hasCrews || $hasSubs || $hasPayments || $hasWineryRelations) {
            $this->toastError('No se puede eliminar el viticultor porque tiene datos relacionados.');
            return;
        }

        $vit = User::find($viticulturistId);
        if (!$vit) {
            $this->toastError('Viticultor no encontrado.');
            return;
        }

        try {
            $vit->delete();
            $this->toastSuccess('Viticultor eliminado correctamente.');
        } catch (\Exception $e) {
            \Log::error('Error al eliminar viticultor', [
                'error' => $e->getMessage(),
                'viticulturist_id' => $viticulturistId,
                'user_id' => $user->id,
            ]);
            $this->toastError('Error al eliminar el viticultor. Por favor, intenta de nuevo.');
        }
    }
}
