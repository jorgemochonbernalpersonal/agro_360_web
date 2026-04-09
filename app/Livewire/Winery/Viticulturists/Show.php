<?php

namespace App\Livewire\Winery\Viticulturists;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\NotebookAccessRequest;
use App\Models\User;
use App\Models\WineryViticulturist;
use App\Notifications\NotebookAccessRequestedNotification;
use App\Notifications\ViticulturistInvitationNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Component;

class Show extends Component
{
    use WithToastNotifications;

    public User $viticulturist;
    public WineryViticulturist $relation;

    // Invitation
    public string $inviteEmail    = '';
    public bool   $showEmailField = false;

    public function mount(User $viticulturist): void
    {
        $wineryId = Auth::id();

        $this->relation = WineryViticulturist::where('winery_id', $wineryId)
            ->where('viticulturist_id', $viticulturist->id)
            ->firstOrFail();

        $this->viticulturist = $viticulturist->load(['profile']);

        if ($this->hasRealEmail()) {
            $this->inviteEmail = $this->viticulturist->email;
        }
    }

    // ── Invitation ───────────────────────────────────────────────────────────

    protected function hasRealEmail(): bool
    {
        return $this->viticulturist->email
            && !str_starts_with($this->viticulturist->email, 'viticultores.');
    }

    public function sendInvitation(): void
    {
        $this->validate([
            'inviteEmail' => ['required', 'email', 'max:255'],
        ], [
            'inviteEmail.required' => 'Introduce el email del viticultor.',
            'inviteEmail.email'    => 'El email no es válido.',
        ]);

        // Rate limit: max 1 envío por hora
        if ($this->viticulturist->invitation_sent_at
            && $this->viticulturist->invitation_sent_at->isAfter(now()->subHour())) {
            $this->toastError('Invitación enviada hace menos de 1 hora. Espera antes de reenviar.');
            return;
        }

        $emailTaken = User::where('email', $this->inviteEmail)
            ->where('id', '!=', $this->viticulturist->id)
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

        if (!$this->hasRealEmail()) {
            $updates['email'] = $this->inviteEmail;
        }

        $this->viticulturist->update($updates);
        $this->viticulturist->refresh();

        $this->viticulturist->notify(new ViticulturistInvitationNotification(Auth::user(), $plainToken));

        $this->showEmailField = false;
        $this->toastSuccess("Invitación enviada a {$this->inviteEmail}.");
    }

    public function revokeInvitation(): void
    {
        $this->viticulturist->update([
            'invitation_token'      => null,
            'invitation_expires_at' => null,
            'invitation_sent_at'    => null,
        ]);
        $this->toastSuccess('Invitación revocada.');
    }

    // ── Field notebook access ─────────────────────────────────────────────────

    public function requestNotebookAccess(): void
    {
        $existing = NotebookAccessRequest::where('winery_id', Auth::id())
            ->where('viticulturist_id', $this->viticulturist->id)
            ->first();

        if ($existing && $existing->isPending()) {
            $this->toastInfo('Ya tienes una solicitud pendiente de respuesta.');
            return;
        }

        if ($this->relation->cuaderno_access) {
            $this->toastInfo('Esta bodega ya tiene acceso al cuaderno.');
            return;
        }

        NotebookAccessRequest::updateOrCreate(
            [
                'winery_id'        => Auth::id(),
                'viticulturist_id' => $this->viticulturist->id,
            ],
            [
                'status'       => NotebookAccessRequest::STATUS_PENDING,
                'requested_at' => now(),
                'responded_at' => null,
            ]
        );

        $this->viticulturist->notify(new NotebookAccessRequestedNotification(Auth::user()));

        Cache::forget("nav_badge_notebook_access_{$this->viticulturist->id}");

        $this->toastSuccess('Solicitud enviada. El viticultor recibirá una notificación para aprobarla.');
    }

    public function cancelAccessRequest(): void
    {
        NotebookAccessRequest::where('winery_id', Auth::id())
            ->where('viticulturist_id', $this->viticulturist->id)
            ->where('status', NotebookAccessRequest::STATUS_PENDING)
            ->delete();

        $this->toastSuccess('Solicitud cancelada.');
    }

    // ── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        $this->viticulturist->load([
            'plots.municipality',
            'plots.plantings.grapeVariety',
        ]);

        $plots = $this->viticulturist->plots;

        $totalHa        = $plots->sum('area');
        $totalPlantings = $plots->sum(fn($p) => $p->plantings->count());
        $totalKgLimit   = $plots->sum(fn($p) => $p->plantings->sum('harvest_limit_kg'));

        $isOwn = $this->relation->source === WineryViticulturist::SOURCE_OWN;

        $accessRequest = $this->viticulturist->can_login
            ? NotebookAccessRequest::where('winery_id', Auth::id())
                ->where('viticulturist_id', $this->viticulturist->id)
                ->first()
            : null;

        return view('livewire.winery.viticulturists.show', [
            'plots'          => $plots,
            'totalHa'        => $totalHa,
            'totalPlantings' => $totalPlantings,
            'totalKgLimit'   => $totalKgLimit,
            'relation'       => $this->relation,
            'isOwn'          => $isOwn,
            'hasRealEmail'   => $this->hasRealEmail(),
            'accessRequest'  => $accessRequest,
        ])->layout('layouts.app');
    }
}
