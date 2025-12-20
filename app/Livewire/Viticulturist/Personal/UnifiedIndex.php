<?php

namespace App\Livewire\Viticulturist\Personal;

use App\Models\AgriculturalActivity;
use App\Models\Crew;
use App\Models\CrewMember;
use App\Models\User;
use App\Models\WineryViticulturist;
use App\Notifications\ViticulturistInvitationNotification;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UnifiedIndex extends Component
{
    use WithPagination, WithToastNotifications;

    public $viewMode = 'personal'; // 'personal' o 'crews'
    public $search = '';
    public $wineryFilter = '';
    public $statusFilter = ''; // 'in_crew', 'individual', 'unassigned'
    public $crewFilter = ''; // Filtro por cuadrilla específica
    
    // Para asignaciones
    public $assignToCrewId = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'wineryFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'crewFilter' => ['except' => ''],
        'viewMode' => ['except' => 'personal'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingWineryFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingCrewFilter()
    {
        $this->resetPage();
    }

    public function switchView($mode)
    {
        $this->viewMode = $mode;
        $this->resetPage();
    }

    public function assignToCrew(int $viticulturistId): void
    {
        if (empty($viticulturistId) || empty($this->assignToCrewId)) {
            $this->toastError('Debes seleccionar un equipo.');
            return;
        }

        $user = Auth::user();

        // Verificar que el viticultor pertenece al usuario actual
        $canEdit = WineryViticulturist::editableBy($user)
            ->where('viticulturist_id', $viticulturistId)
            ->exists();

        if (! $canEdit) {
            $this->toastError('No tienes permiso para gestionar este viticultor.');
            return;
        }

        // Verificar que la cuadrilla pertenece al viticultor actual
        $crew = Crew::forViticulturist($user->id)
            ->where('id', $this->assignToCrewId)
            ->first();

        if (! $crew) {
            $this->toastError('No tienes permiso para gestionar este equipo.');
            return;
        }

        // Evitar duplicados de CrewMember por viticultor
        $member = CrewMember::where('viticulturist_id', $viticulturistId)->first();

        if ($member && $member->crew_id === $crew->id) {
            $this->toastError('Este viticultor ya forma parte de este equipo.');
            return;
        }

        try {
            if (! $member) {
                // Crear nuevo miembro directamente asignado al equipo
                CrewMember::create([
                    'viticulturist_id' => $viticulturistId,
                    'crew_id' => $crew->id,
                    'assigned_by' => $user->id,
                ]);
            } else {
                // Actualizar miembro existente (sin equipo u otro equipo) a este equipo
                $member->update([
                    'crew_id' => $crew->id,
                    'assigned_by' => $user->id,
                ]);
            }

            $this->assignToCrewId = '';

            $this->toastSuccess('Viticultor asignado al equipo correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al asignar viticultor a equipo', [
                'error' => $e->getMessage(),
                'viticulturist_id' => $viticulturistId,
                'crew_id' => $this->assignToCrewId,
                'user_id' => $user->id,
            ]);

            $this->toastError('Error al asignar el viticultor al equipo. Por favor, intenta de nuevo.');
        }
    }

    public function makeIndividual(int $viticulturistId): void
    {
        $user = Auth::user();

        // Verificar que el viticultor pertenece al usuario actual
        $canEdit = WineryViticulturist::editableBy($user)
            ->where('viticulturist_id', $viticulturistId)
            ->exists();

        if (! $canEdit) {
            $this->toastError('No tienes permiso para gestionar este viticultor.');
            return;
        }

        $member = CrewMember::where('viticulturist_id', $viticulturistId)->first();

        if (!$member) {
            // Crear como trabajador sin equipo
            CrewMember::create([
                'viticulturist_id' => $viticulturistId,
                'crew_id' => null,
                'assigned_by' => $user->id,
            ]);
            $this->toastSuccess('Viticultor marcado como sin equipo.');
        } else {
            // Convertir a sin equipo
            $member->update(['crew_id' => null]);
            $this->toastSuccess('Viticultor convertido a sin equipo.');
        }
    }

    public function deleteCrew(Crew $crew)
    {
        if (!Auth::user()->can('delete', $crew)) {
            $this->toastError('No tienes permiso para eliminar este equipo.');
            return;
        }

        if ($crew->activities()->exists()) {
            $this->toastError('No se puede eliminar un equipo con actividades asociadas.');
            return;
        }

        try {
            DB::transaction(function () use ($crew) {
                // Eliminar miembros primero
                $crew->members()->delete();
                $crew->delete();
            });
            
            $this->toastSuccess('Equipo eliminado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error deleting crew', [
                'crew_id' => $crew->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->toastError('Hubo un error al eliminar el equipo. Por favor, inténtalo de nuevo.');
        }
    }

    public function sendInvitation(int $viticulturistId): void
    {
        $user = Auth::user();

        // Verificar que el viticultor pertenece al usuario actual
        $canEdit = WineryViticulturist::editableBy($user)
            ->where('viticulturist_id', $viticulturistId)
            ->exists();

        if (!$canEdit) {
            $this->toastError('No tienes permiso para gestionar este viticultor.');
            return;
        }

        $viticulturist = User::find($viticulturistId);

        if (!$viticulturist) {
            $this->toastError('Viticultor no encontrado.');
            return;
        }

        // Verificar que no se haya enviado ya la invitación
        if ($viticulturist->invitation_sent_at !== null) {
            $this->toastError('La invitación ya fue enviada anteriormente.');
            return;
        }

        // Verificar que el viticultor aún no puede hacer login (estado inicial)
        if ($viticulturist->can_login) {
            $this->toastError('Este viticultor ya puede iniciar sesión. No es necesario enviar invitación.');
            return;
        }

        try {
            $viticulturist->notify(new ViticulturistInvitationNotification($user));
            
            // Marcar que se envió la invitación
            $viticulturist->update([
                'invitation_sent_at' => now(),
            ]);

            $this->toastSuccess('Invitación enviada correctamente por email.');
        } catch (\Exception $e) {
            Log::error('Error al enviar invitación', [
                'error' => $e->getMessage(),
                'viticulturist_id' => $viticulturistId,
                'user_id' => $user->id,
            ]);

            $this->toastError('Error al enviar la invitación. Por favor, intenta de nuevo.');
        }
    }

    public function deleteViticulturist($viticulturistId)
    {
        $user = Auth::user();

        // Verify this user can delete the viticulturist: must be the creator
        $relation = WineryViticulturist::where('viticulturist_id', $viticulturistId)
            ->where('parent_viticulturist_id', $user->id)
            ->first();

        if (!$relation) {
            $this->toastError('No tienes permiso para eliminar este viticultor.');
            return;
        }

        // Check dependent records
        $hasPlots = \App\Models\Plot::where('viticulturist_id', $viticulturistId)->exists();
        $hasCampaigns = \App\Models\Campaign::where('viticulturist_id', $viticulturistId)->exists();
        $hasCrews = Crew::where('viticulturist_id', $viticulturistId)->exists();
        $hasSubs = \App\Models\Subscription::where('user_id', $viticulturistId)->exists();
        $hasPayments = \App\Models\Payment::where('user_id', $viticulturistId)->exists();
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
            Log::error('Error al eliminar viticultor', [
                'error' => $e->getMessage(),
                'viticulturist_id' => $viticulturistId,
                'user_id' => $user->id,
            ]);
            $this->toastError('Error al eliminar el viticultor. Por favor, intenta de nuevo.');
        }
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->wineryFilter = '';
        $this->statusFilter = '';
        $this->crewFilter = '';
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();
        $wineries = $user->wineries;

        if ($this->viewMode === 'personal') {
            return $this->renderPersonalView($user, $wineries);
        } else {
            return $this->renderCrewsView($user, $wineries);
        }
    }

    private function renderPersonalView($user, $wineries)
    {
        // IDs de viticultores creados por este viticultor
        $createdViticulturistIds = WineryViticulturist::editableBy($user)
            ->pluck('viticulturist_id');

        $query = User::query()
            ->where('role', 'viticulturist')
            ->whereIn('id', $createdViticulturistIds);

        // Búsqueda
        if ($this->search) {
            $search = '%' . strtolower($this->search) . '%';
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(email) LIKE ?', [$search]);
            });
        }

        // Filtro por bodega
        if ($this->wineryFilter) {
            $viticulturistIds = WineryViticulturist::where('winery_id', $this->wineryFilter)
                ->pluck('viticulturist_id');
            $query->whereIn('id', $viticulturistIds);
        }

        // Filtro por cuadrilla específica
        if ($this->crewFilter) {
            $crewMemberIds = CrewMember::where('crew_id', $this->crewFilter)
                ->pluck('viticulturist_id');
            $query->whereIn('id', $crewMemberIds);
        }

        // Filtro de estado - aplicar antes de paginar
        if ($this->statusFilter) {
            if ($this->statusFilter === 'in_crew') {
                // Solo viticultores que están en alguna cuadrilla
                $crewMemberIds = CrewMember::whereNotNull('crew_id')
                    ->pluck('viticulturist_id');
                $query->whereIn('id', $crewMemberIds);
            } elseif ($this->statusFilter === 'individual') {
                // Solo viticultores que son individuales (tienen CrewMember pero sin crew_id)
                $crewMemberIds = CrewMember::whereNull('crew_id')
                    ->pluck('viticulturist_id');
                $query->whereIn('id', $crewMemberIds);
            } elseif ($this->statusFilter === 'unassigned') {
                // Solo viticultores que no tienen CrewMember
                $assignedIds = CrewMember::pluck('viticulturist_id');
                $query->whereNotIn('id', $assignedIds);
            }
        }

        $viticulturists = $query->orderBy('name')->paginate(15);

        // Obtener miembros de una vez
        $membersByViticulturist = CrewMember::with('crew')
            ->whereIn('viticulturist_id', $viticulturists->pluck('id'))
            ->get()
            ->keyBy('viticulturist_id');

        // Cuadrillas disponibles
        $crews = Crew::forViticulturist($user->id)
            ->orderBy('name')
            ->get();

        // Calcular estadísticas para todos los viticultores (no solo los paginados)
        $allViticulturists = User::where('role', 'viticulturist')
            ->whereIn('id', $createdViticulturistIds)
            ->get();
        
        $allMembers = CrewMember::whereIn('viticulturist_id', $allViticulturists->pluck('id'))
            ->with('crew')
            ->get()
            ->keyBy('viticulturist_id');
        
        $inCrewCount = $allMembers->filter(fn($m) => $m->crew_id !== null)->count();
        $individualCount = $allMembers->filter(fn($m) => $m->crew_id === null)->count();
        $unassignedCount = $allViticulturists->count() - $allMembers->count();
        $crewsCount = Crew::forViticulturist($user->id)->count();

        // Eager load wineries para cada viticultor paginado
        $viticulturistIds = $viticulturists->pluck('id');
        $wineryRelations = WineryViticulturist::whereIn('viticulturist_id', $viticulturistIds)
            ->with('winery')
            ->get()
            ->groupBy('viticulturist_id');
        
        $wineriesByViticulturist = $wineryRelations->map(function($relations) {
            return $relations->pluck('winery')->filter()->unique('id')->values();
        });

        return view('livewire.viticulturist.personal.unified-index', [
            'viticulturists' => $viticulturists,
            'membersByViticulturist' => $membersByViticulturist,
            'crews' => $crews,
            'wineries' => $wineries,
            'viticulturistsCount' => $allViticulturists->count(),
            'inCrewCount' => $inCrewCount,
            'individualCount' => $individualCount,
            'unassignedCount' => $unassignedCount,
            'crewsCount' => $crewsCount,
            'wineriesByViticulturist' => $wineriesByViticulturist,
        ])->layout('layouts.app');
    }

    private function renderCrewsView($user, $wineries)
    {
        if (!$user->can('viewAny', Crew::class)) {
            abort(403, 'No tienes permiso para ver equipos.');
        }

        $query = Crew::forViticulturist($user->id)
            ->with(['winery', 'viticulturist'])
            ->withCount(['members', 'activities']);

        if ($this->search) {
            $search = '%' . strtolower($this->search) . '%';
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(description) LIKE ?', [$search]);
            });
        }

        if ($this->wineryFilter) {
            $query->forWinery($this->wineryFilter);
        }

        $crewsPaginated = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Calcular estadísticas para equipos
        $allCrews = Crew::forViticulturist($user->id)->get();
        $crewsCount = $allCrews->count();
        
        // Calcular estadísticas de viticultores para el panel
        $createdViticulturistIds = WineryViticulturist::editableBy($user)
            ->pluck('viticulturist_id');
        $allViticulturists = User::where('role', 'viticulturist')
            ->whereIn('id', $createdViticulturistIds)
            ->count();

        return view('livewire.viticulturist.personal.unified-index', [
            'crewsPaginated' => $crewsPaginated,
            'crews' => $allCrews,
            'wineries' => $wineries,
            'viticulturistsCount' => $allViticulturists,
            'crewsCount' => $crewsCount,
        ])->layout('layouts.app');
    }
}

