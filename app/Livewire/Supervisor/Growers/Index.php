<?php

namespace App\Livewire\Supervisor\Growers;

use App\Livewire\Concerns\WithToastNotifications;
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

    public string $search = '';

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

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function updatingSearch(): void { $this->resetPage(); }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->resetPage();
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
            'createDni'   => ['nullable', 'string', 'max:20',
                Rule::unique('users', 'dni')->where('can_login', true),
            ],
            'createPhone' => ['nullable', 'string', 'max:20'],
        ], [
            'createName.required'  => 'El nombre es obligatorio.',
            'createEmail.email'    => 'El email no es válido.',
            'createEmail.unique'   => 'Ya existe un usuario con este email.',
            'createDni.unique'     => 'Ya existe un usuario activo con este DNI.',
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

        $this->showCreateModal = false;
        $this->toastSuccess("Viticultor {$viticulturist->name} creado correctamente.");
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
            'inviteEmail.required' => 'Introduce el email del viticultor.',
            'inviteEmail.email'    => 'El email no es válido.',
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
            $this->toastError('Invitación enviada hace menos de 1 hora. Espera antes de reenviar.');
            return;
        }

        $emailTaken = User::where('email', $this->inviteEmail)
            ->where('id', '!=', $grower->id)
            ->exists();

        if ($emailTaken) {
            $this->addError('inviteEmail', 'Este email ya está registrado en el sistema.');
            return;
        }

        $plainToken = Str::random(64);
        $hashedToken = Hash::make($plainToken);

        $updates = [
            'invitation_token'      => $hashedToken,
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

        $this->toastSuccess('Invitación revocada.');
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

        $query = User::whereIn('id', $poolIds);

        if ($this->search) {
            $term = '%' . mb_strtolower($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(name) LIKE ?', [$term])
                  ->orWhereRaw('LOWER(email) LIKE ?', [$term]);
            });
        }

        $growers          = $query->orderBy('name')->paginate(15);
        $totalGrowerCount = $poolIds->count();

        // For invite modal: ghost viticultors available in this supervisor's pool
        $ghostGrowerForModal = $this->showInviteModal && $this->inviteGrowerId
            ? User::find($this->inviteGrowerId)
            : null;

        return view('livewire.supervisor.growers.index', [
            'growers'              => $growers,
            'plotStatsByVit'       => $plotStatsByVit,
            'activePlantingsByVit' => $activePlantingsByVit,
            'wineryNamesByVit'     => $wineryNamesByVit,
            'totalGrowerCount'     => $totalGrowerCount,
            'ghostGrowerForModal'  => $ghostGrowerForModal,
        ]);
    }
}
