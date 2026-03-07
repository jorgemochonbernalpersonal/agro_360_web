<?php

namespace App\Livewire\Winery\Viticulturists;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\User;
use App\Models\WineryViticulturist;
use App\Notifications\ViticulturistInvitationNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

class Show extends Component
{
    use WithToastNotifications;

    public User $viticulturist;
    public WineryViticulturist $relation;

    // Invitación
    public string $inviteEmail    = '';
    public bool   $showEmailField = false;

    public function mount(User $viticulturist): void
    {
        $wineryId = Auth::id();

        $this->relation = WineryViticulturist::where('winery_id', $wineryId)
            ->where('viticulturist_id', $viticulturist->id)
            ->firstOrFail();

        $this->viticulturist = $viticulturist->load([
            'plots.municipality',
            'plots.plantings.grapeVariety',
            'profile',
        ]);

        // Pre-rellenar email si ya tiene uno real
        if ($this->hasRealEmail()) {
            $this->inviteEmail = $this->viticulturist->email;
        }
    }

    // ── Invitación ───────────────────────────────────────────────────────────

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

        // Verificar que el email no está ya en uso por otro usuario
        $emailTaken = User::where('email', $this->inviteEmail)
            ->where('id', '!=', $this->viticulturist->id)
            ->exists();

        if ($emailTaken) {
            $this->addError('inviteEmail', 'Este email ya está registrado en el sistema.');
            return;
        }

        // Generar token único
        $token = Str::random(64);

        // Actualizar usuario: email real si era fake, token, sent_at, expires_at (7 días)
        $updates = [
            'invitation_token'      => $token,
            'invitation_sent_at'    => now(),
            'invitation_expires_at' => now()->addDays(7),
        ];

        if (!$this->hasRealEmail()) {
            $updates['email'] = $this->inviteEmail;
        }

        $this->viticulturist->update($updates);
        $this->viticulturist->refresh();

        // Enviar notificación
        $this->viticulturist->notify(new ViticulturistInvitationNotification(Auth::user(), $token));

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

    // ── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        $plots = $this->viticulturist->plots;

        $totalHa        = $plots->sum('area');
        $totalPlantings = $plots->sum(fn($p) => $p->plantings->count());
        $totalKgLimit   = $plots->sum(fn($p) => $p->plantings->sum('harvest_limit_kg'));

        $isOwn = $this->relation->source === WineryViticulturist::SOURCE_OWN;

        return view('livewire.winery.viticulturists.show', [
            'plots'          => $plots,
            'totalHa'        => $totalHa,
            'totalPlantings' => $totalPlantings,
            'totalKgLimit'   => $totalKgLimit,
            'relation'       => $this->relation,
            'isOwn'          => $isOwn,
            'hasRealEmail'   => $this->hasRealEmail(),
        ])->layout('layouts.app');
    }
}
