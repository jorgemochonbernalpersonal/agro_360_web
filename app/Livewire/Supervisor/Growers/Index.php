<?php

namespace App\Livewire\Supervisor\Growers;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\NotebookAccessRequest;
use App\Models\SupervisorViticulturist;
use App\Models\User;
use App\Models\WineryViticulturist;
use App\Notifications\ViticulturistInvitationNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public string $search        = '';
    public string $statusFilter  = '';

    // ── Create ghost viticulturist ────────────────────────────────────────────
    public bool   $showCreateModal = false;
    public string $createName      = '';
    public string $createEmail     = '';
    public string $createDni       = '';
    public string $createPhone     = '';

    // ── Send invitation ───────────────────────────────────────────────────────
    public bool   $showInviteModal  = false;
    public ?int   $inviteGrowerId   = null;
    public string $inviteEmail      = '';

    // ── Link existing viticulturist ───────────────────────────────────────────
    public bool   $showLinkModal    = false;
    public string $linkQuery        = '';
    public ?int   $linkSelectedId   = null;

    protected $queryString = [
        'search'       => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    public function setStatusFilter(string $status): void
    {
        $this->statusFilter = $status;
        $this->resetPage();
    }

    // ── Link existing viticulturist ───────────────────────────────────────────

    public function openLinkModal(): void
    {
        $this->reset(['linkQuery', 'linkSelectedId']);
        $this->resetErrorBag();
        $this->showLinkModal = true;
    }

    public function closeLinkModal(): void
    {
        $this->showLinkModal = false;
    }

    public function searchLinkCandidates(): array
    {
        $term = trim($this->linkQuery);
        if (strlen($term) < 2) {
            return [];
        }

        $doId    = Auth::id();
        $poolIds = SupervisorViticulturist::where('supervisor_id', $doId)->pluck('viticulturist_id');

        $like = '%' . mb_strtolower($term) . '%';

        return User::whereIn('role', [User::ROLE_VITICULTURIST, User::ROLE_PRODUCER])
            ->where('can_login', true)
            ->whereNotIn('id', $poolIds)
            ->where(function ($q) use ($like, $term) {
                $q->whereRaw('LOWER(name) LIKE ?', [$like])
                  ->orWhereRaw('LOWER(email) LIKE ?', [$like])
                  ->orWhereRaw('LOWER(COALESCE(dni,\'\')) LIKE ?', [$like]);
            })
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'email', 'dni'])
            ->toArray();
    }

    public function selectLinkCandidate(int $userId): void
    {
        $this->linkSelectedId = $userId;
    }

    public function linkExistingGrower(): void
    {
        $this->validate([
            'linkSelectedId' => ['required', 'integer'],
        ], [
            'linkSelectedId.required' => __('Selecciona un viticultor de los resultados.'),
        ]);

        $doId = Auth::id();

        $viticulturist = User::whereIn('role', [User::ROLE_VITICULTURIST, User::ROLE_PRODUCER])
            ->where('can_login', true)
            ->findOrFail($this->linkSelectedId);

        $alreadyInPool = SupervisorViticulturist::where('supervisor_id', $doId)
            ->where('viticulturist_id', $viticulturist->id)
            ->exists();

        if ($alreadyInPool) {
            $this->toastError("{$viticulturist->name} ya pertenece al pool de la denominación.");
            return;
        }

        SupervisorViticulturist::create([
            'supervisor_id'    => $doId,
            'viticulturist_id' => $viticulturist->id,
            'assigned_by'      => $doId,
        ]);

        $this->showLinkModal = false;
        $this->toastSuccess("{$viticulturist->name} vinculado al pool de la denominación.");
    }

    // ── Create modal ─────────────────────────────────────────────────────────

    public function openCreateModal(): void
    {
        $this->reset(['createName', 'createEmail', 'createDni', 'createPhone']);
        $this->resetErrorBag();
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
    }

    public function createGrower(): void
    {
        $this->validate([
            'createName'  => ['required', 'string', 'max:255'],
            'createEmail' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'createDni'   => ['nullable', 'string', 'max:20', Rule::unique('users', 'dni')],
            'createPhone' => ['nullable', 'string', 'max:20'],
        ], [
            'createName.required'  => __('El nombre es obligatorio.'),
            'createEmail.email'    => __('El email no es válido.'),
            'createEmail.unique'   => __('Ya existe un usuario con este email.'),
            'createDni.unique'     => __('Ya existe un usuario activo con este DNI.'),
        ]);

        $viticulturist = User::create([
            'name'      => $this->createName,
            'email'     => $this->createEmail ?: ('viticultores.' . Str::uuid() . '@noemail.agro365.es'),
            'dni'       => $this->createDni ? strtoupper(trim($this->createDni)) : null,
            'role'      => User::ROLE_VITICULTURIST,
            'can_login' => false,
            'password'  => Hash::make(Str::random(40)),
        ]);

        if ($this->createPhone) {
            $viticulturist->profile()->create(['phone' => $this->createPhone]);
        }

        SupervisorViticulturist::create([
            'supervisor_id'    => Auth::id(),
            'viticulturist_id' => $viticulturist->id,
            'assigned_by'      => Auth::id(),
        ]);

        // Auto-send invitation if a real email was provided
        if ($this->createEmail) {
            $plainToken = Str::random(64);
            $viticulturist->update([
                'invitation_token'      => hash('sha256', $plainToken),
                'invitation_sent_at'    => now(),
                'invitation_expires_at' => now()->addDays(7),
            ]);
            $viticulturist->notify(new ViticulturistInvitationNotification(Auth::user(), $plainToken));
        }

        $this->showCreateModal = false;

        $message = $this->createEmail
            ? "Viticultor {$viticulturist->name} creado e invitación enviada correctamente."
            : "Viticultor {$viticulturist->name} creado. Recuerda enviarle una invitación cuando tengas su email.";

        $this->toastSuccess($message);
    }

    // ── Invitation ────────────────────────────────────────────────────────────

    public function openInviteModal(int $growerId): void
    {
        $grower = User::where('id', $growerId)->where('can_login', false)->firstOrFail();

        $this->inviteGrowerId = $growerId;
        $this->inviteEmail    = $this->hasRealEmail($grower) ? $grower->email : '';
        $this->resetErrorBag('inviteEmail');
        $this->showInviteModal = true;
    }

    public function closeInviteModal(): void
    {
        $this->showInviteModal = false;
        $this->inviteGrowerId  = null;
        $this->inviteEmail     = '';
    }

    public function sendInvitation(): void
    {
        $this->validate([
            'inviteEmail' => ['required', 'email', 'max:255'],
        ], [
            'inviteEmail.required' => __('Introduce el email del viticultor.'),
            'inviteEmail.email'    => __('El email no es válido.'),
        ]);

        $grower = User::where('id', $this->inviteGrowerId)
            ->where('can_login', false)
            ->firstOrFail();

        // Guard: viticulturist must belong to this supervisor
        SupervisorViticulturist::where('supervisor_id', Auth::id())
            ->where('viticulturist_id', $grower->id)
            ->firstOrFail();

        // Rate limit: 1 per hour
        if ($grower->invitation_sent_at?->isAfter(now()->subHour())) {
            $this->toastError(__('Invitación enviada hace menos de 1 hora. Espera antes de reenviar.'));
            return;
        }

        $emailTaken = User::where('email', $this->inviteEmail)
            ->where('id', '!=', $grower->id)
            ->exists();

        if ($emailTaken) {
            $this->addError('inviteEmail', __('Este email ya está registrado en el sistema.'));
            return;
        }

        $plainToken = Str::random(64);

        $updates = [
            'invitation_token'      => hash('sha256', $plainToken),
            'invitation_sent_at'    => now(),
            'invitation_expires_at' => now()->addDays(7),
        ];

        if (!$this->hasRealEmail($grower)) {
            $updates['email'] = $this->inviteEmail;
        }

        $grower->update($updates);

        $grower->notify(new ViticulturistInvitationNotification(Auth::user(), $plainToken));

        $this->closeInviteModal();
        $this->toastSuccess("Invitación enviada a {$this->inviteEmail}.");
    }

    public function revokeInvitation(int $growerId): void
    {
        $grower = User::where('id', $growerId)->where('can_login', false)->firstOrFail();

        SupervisorViticulturist::where('supervisor_id', Auth::id())
            ->where('viticulturist_id', $grower->id)
            ->firstOrFail();

        $grower->update([
            'invitation_token'      => null,
            'invitation_expires_at' => null,
            'invitation_sent_at'    => null,
        ]);

        $this->toastSuccess(__('Invitación revocada.'));
    }

    // ── Remove grower from pool ───────────────────────────────────────────────

    public function removeGrower(int $growerId): void
    {
        $doId = Auth::id();

        $relation = SupervisorViticulturist::where('supervisor_id', $doId)
            ->where('viticulturist_id', $growerId)
            ->firstOrFail();

        $grower = User::find($growerId);

        // Remove winery assignments originated by this supervisor
        WineryViticulturist::where('supervisor_id', $doId)
            ->where('viticulturist_id', $growerId)
            ->where('source', WineryViticulturist::SOURCE_SUPERVISOR)
            ->delete();

        // Remove pending notebook access requests from this supervisor
        NotebookAccessRequest::where('supervisor_id', $doId)
            ->where('viticulturist_id', $growerId)
            ->delete();

        $relation->delete();

        $this->toastSuccess(__(':name eliminado del pool de la denominación.', ['name' => $grower?->name ?? __('El viticultor')]));
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    protected function hasRealEmail(User $grower): bool
    {
        return $grower->email && !str_starts_with($grower->email, 'viticultores.');
    }

    // ── Render ────────────────────────────────────────────────────────────────

    #[Layout('layouts.app')]
    public function render()
    {
        $doId = Auth::id();

        // Primary pool: supervisor_viticulturist
        $poolIds = SupervisorViticulturist::where('supervisor_id', $doId)
            ->pluck('viticulturist_id');

        // Winery assignments for pool members (only supervisor-sourced)
        $wineryNamesByVit = WineryViticulturist::where('supervisor_id', $doId)
            ->where('source', WineryViticulturist::SOURCE_SUPERVISOR)
            ->whereIn('viticulturist_id', $poolIds)
            ->with(['winery:id,name'])
            ->get()
            ->groupBy('viticulturist_id')
            ->map(fn($rows) => $rows->pluck('winery.name')->filter()->unique()->implode(', '));

        $plotStatsByVit = DB::table('plots')
            ->whereIn('viticulturist_id', $poolIds)
            ->where('active', true)
            ->select(
                'viticulturist_id',
                DB::raw('COUNT(*) as plot_count'),
                DB::raw('COALESCE(SUM(area), 0) as total_area')
            )
            ->groupBy('viticulturist_id')
            ->get()
            ->keyBy('viticulturist_id');

        $activePlantingsByVit = DB::table('plot_plantings')
            ->join('plots', 'plots.id', '=', 'plot_plantings.plot_id')
            ->whereIn('plots.viticulturist_id', $poolIds)
            ->where('plot_plantings.status', 'active')
            ->select(
                'plots.viticulturist_id',
                DB::raw('COUNT(*) as planting_count'),
            )
            ->groupBy('plots.viticulturist_id')
            ->get()
            ->keyBy('viticulturist_id');

        $allGrowers = User::whereIn('id', $poolIds)->get(['id', 'can_login', 'invitation_token', 'invitation_expires_at']);

        $countsByStatus = [
            'all'     => $allGrowers->count(),
            'ghost'   => $allGrowers->filter(fn($u) => !$u->can_login && (!$u->invitation_token || ($u->invitation_expires_at && $u->invitation_expires_at->isPast())))->count(),
            'invited' => $allGrowers->filter(fn($u) => !$u->can_login && $u->invitation_token && $u->invitation_expires_at?->isFuture())->count(),
            'active'  => $allGrowers->filter(fn($u) => $u->can_login)->count(),
        ];

        $query = User::whereIn('id', $poolIds);

        match ($this->statusFilter) {
            'ghost'   => $query->where('can_login', false)->where(function ($q) {
                $q->whereNull('invitation_token')
                  ->orWhere('invitation_expires_at', '<=', now());
            }),
            'invited' => $query->where('can_login', false)
                               ->whereNotNull('invitation_token')
                               ->where('invitation_expires_at', '>', now()),
            'active'  => $query->where('can_login', true),
            default   => null,
        };

        if ($this->search) {
            $term = '%' . mb_strtolower($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(name) LIKE ?', [$term])
                  ->orWhereRaw('LOWER(email) LIKE ?', [$term]);
            });
        }

        $growers          = $query->orderBy('name')->paginate(15);
        $totalGrowerCount = $poolIds->count();

        $ghostGrowerForModal = $this->showInviteModal && $this->inviteGrowerId
            ? User::find($this->inviteGrowerId)
            : null;

        $linkCandidates = $this->showLinkModal ? $this->searchLinkCandidates() : [];

        $linkSelectedUser = $this->linkSelectedId
            ? collect($linkCandidates)->firstWhere('id', $this->linkSelectedId)
            : null;

        return view('livewire.supervisor.growers.index', [
            'growers'              => $growers,
            'plotStatsByVit'       => $plotStatsByVit,
            'activePlantingsByVit' => $activePlantingsByVit,
            'wineryNamesByVit'     => $wineryNamesByVit,
            'totalGrowerCount'     => $totalGrowerCount,
            'countsByStatus'       => $countsByStatus,
            'ghostGrowerForModal'  => $ghostGrowerForModal,
            'linkCandidates'       => $linkCandidates,
            'linkSelectedUser'     => $linkSelectedUser,
        ]);
    }
}
