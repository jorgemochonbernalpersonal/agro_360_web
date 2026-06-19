<?php

namespace App\Livewire\Concerns;

use App\Models\SupervisorViticulturist;
use App\Models\User;
use App\Notifications\ViticulturistInvitationNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

trait WithGrowerInvitation
{
    public bool $showInviteModal = false;

    public ?int $inviteGrowerId = null;

    public string $inviteEmail = '';

    public function openInviteModal(int $growerId): void
    {
        $grower = User::where('id', $growerId)->where('can_login', false)->firstOrFail();

        $this->inviteGrowerId = $growerId;
        $this->inviteEmail = $this->hasRealEmail($grower) ? $grower->email : '';
        $this->resetErrorBag('inviteEmail');
        $this->showInviteModal = true;
    }

    public function closeInviteModal(): void
    {
        $this->showInviteModal = false;
        $this->inviteGrowerId = null;
        $this->inviteEmail = '';
    }

    public function sendInvitation(): void
    {
        $this->validate([
            'inviteEmail' => ['required', 'email', 'max:255'],
        ], [
            'inviteEmail.required' => __('Introduce el email del viticultor.'),
            'inviteEmail.email' => __('El email no es válido.'),
        ]);

        $grower = User::where('id', $this->inviteGrowerId)
            ->where('can_login', false)
            ->firstOrFail();

        SupervisorViticulturist::where('supervisor_id', Auth::id())
            ->where('viticulturist_id', $grower->id)
            ->firstOrFail();

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
            'invitation_token' => hash('sha256', $plainToken),
            'invitation_sent_at' => now(),
            'invitation_expires_at' => now()->addDays(7),
        ];

        if (! $this->hasRealEmail($grower)) {
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
            'invitation_token' => null,
            'invitation_expires_at' => null,
            'invitation_sent_at' => null,
        ]);

        $this->toastSuccess(__('Invitación revocada.'));
    }

    protected function hasRealEmail(User $grower): bool
    {
        return $grower->email && ! str_starts_with($grower->email, 'viticultores.');
    }
}
