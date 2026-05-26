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
use App\Notifications\ViticulturistInvitationNotification;
use Illuminate\Support\Str;

class Index extends Component
{
    use WithPagination, WithUserFilters, WithToastNotifications;

    protected $paginationTheme = 'tailwind';

    public string $search          = '';
    public string $assignToCrewId  = '';

    // ── Invitaciones ─────────────────────────────────────────────────────────
    public bool   $showInviteModal = false;
    public ?int   $inviteVitId     = null;
    public string $inviteEmail     = '';

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
            $this->toastError(__('Debes seleccionar una cuadrilla.'));
            return;
        }

        $user = Auth::user();

        // Verificar que la cuadrilla pertenece al viticultor actual
        $crew = Crew::forViticulturist($user->id)
            ->where('id', $this->assignToCrewId)
            ->first();

        if (! $crew) {
            $this->toastError(__('No tienes permiso para gestionar esta cuadrilla.'));
            return;
        }

        // Evitar duplicados de CrewMember por viticultor
        $member = CrewMember::where('viticulturist_id', $viticulturistId)->first();

        if ($member && $member->crew_id === $crew->id) {
            $this->toastError(__('Este viticultor ya forma parte de esta cuadrilla.'));
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

    // ── Invitar sub-viticultor ────────────────────────────────────────────────

    protected function hasRealEmail(User $vit): bool
    {
        return $vit->email && ! str_starts_with($vit->email, 'viticultores.');
    }

    public function openInviteModal(int $vitId): void
    {
        $vit = WineryViticulturist::where('parent_viticulturist_id', Auth::id())
            ->where('viticulturist_id', $vitId)
            ->firstOrFail()
            ->viticulturist;

        if ($vit->can_login) {
            $this->toastError(__('Este viticultor ya tiene acceso activo.'));
            return;
        }

        $this->inviteVitId   = $vitId;
        $this->inviteEmail   = $this->hasRealEmail($vit) ? $vit->email : '';
        $this->showInviteModal = true;
        $this->resetErrorBag('inviteEmail');
    }

    public function closeInviteModal(): void
    {
        $this->showInviteModal = false;
        $this->inviteVitId    = null;
        $this->inviteEmail    = '';
    }

    public function sendInvitation(): void
    {
        $this->validate([
            'inviteEmail' => ['required', 'email', 'max:255'],
        ]);

        $vit = User::where('id', $this->inviteVitId)->where('can_login', false)->firstOrFail();

        // Guard: debe ser subviticultor creado por este viticultor
        WineryViticulturist::where('parent_viticulturist_id', Auth::id())
            ->where('viticulturist_id', $vit->id)
            ->firstOrFail();

        // Rate limit: 1 por hora
        if ($vit->invitation_sent_at?->isAfter(now()->subHour())) {
            $this->toastError(__('Invitación enviada hace menos de 1 hora. Espera antes de reenviar.'));
            return;
        }

        if (User::where('email', $this->inviteEmail)->where('id', '!=', $vit->id)->exists()) {
            $this->addError('inviteEmail', __('Este email ya está registrado en el sistema.'));
            return;
        }

        $plainToken = Str::random(64);

        $updates = [
            'invitation_token'      => hash('sha256', $plainToken),
            'invitation_sent_at'    => now(),
            'invitation_expires_at' => now()->addDays(7),
        ];

        if (! $this->hasRealEmail($vit)) {
            $updates['email'] = $this->inviteEmail;
        }

        $vit->update($updates);
        $vit->notify(new ViticulturistInvitationNotification(Auth::user(), $plainToken));

        $this->closeInviteModal();
        $this->toastSuccess("Invitación enviada a {$this->inviteEmail}.");
    }

    public function revokeInvitation(int $vitId): void
    {
        $vit = User::where('id', $vitId)->where('can_login', false)->firstOrFail();

        WineryViticulturist::where('parent_viticulturist_id', Auth::id())
            ->where('viticulturist_id', $vitId)
            ->firstOrFail();

        $vit->update([
            'invitation_token'      => null,
            'invitation_expires_at' => null,
            'invitation_sent_at'    => null,
        ]);

        $this->toastSuccess(__('Invitación revocada.'));
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

        $allIds = $allVisibleViticulturists->pluck('id');
        $stats = [
            'total'      => $allIds->count(),
            'with_crew'  => CrewMember::whereIn('viticulturist_id', $allIds)->distinct('viticulturist_id')->count(),
            'with_access'=> User::whereIn('id', $allIds)->where('can_login', true)->count(),
        ];

        // IDs de sub-viticultores creados por este viticultor (para mostrar botón invitar)
        $mySubVitIds = WineryViticulturist::where('parent_viticulturist_id', Auth::id())
            ->pluck('viticulturist_id')
            ->toArray();

        return view('livewire.viticulturist.viticulturists.index', [
            'viticulturists'          => $viticulturists,
            'crews'                   => $crews,
            'wineries'                => $wineries,
            'membersByViticulturist'  => $membersByViticulturist,
            'stats'                   => $stats,
            'mySubVitIds'             => $mySubVitIds,
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
            $this->toastError(__('No tienes permiso para eliminar este viticultor.'));
            return;
        }

        // Check dependent records that would block deletion
        $hasPlots = Plot::where('viticulturist_id', $viticulturistId)->exists();
        $hasCampaigns = Campaign::where('viticulturist_id', $viticulturistId)->exists();
        $hasCrews = Crew::where('viticulturist_id', $viticulturistId)->exists();
        $hasSubs = Subscription::where('user_id', $viticulturistId)->exists();
        $hasPayments = Payment::where('user_id', $viticulturistId)->exists();
        // Exclude the creator's own link record (source=viticulturist, parent=current user)
        // to avoid permanently blocking deletion of sub-viticulturists.
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
        if (!$vit) {
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
