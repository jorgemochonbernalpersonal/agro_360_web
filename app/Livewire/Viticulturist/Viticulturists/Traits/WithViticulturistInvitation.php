<?php

namespace App\Livewire\Viticulturist\Viticulturists\Traits;

use App\Models\User;
use App\Models\WineryViticulturist;
use App\Notifications\ViticulturistInvitationNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

trait WithViticulturistInvitation
{
    public bool $showInviteModal = false;

    public ?int $inviteVitId = null;

    public string $inviteEmail = '';

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

        $this->inviteVitId = $vitId;
        $this->inviteEmail = $this->hasRealEmail($vit) ? $vit->email : '';
        $this->showInviteModal = true;
        $this->resetErrorBag('inviteEmail');
    }

    public function closeInviteModal(): void
    {
        $this->showInviteModal = false;
        $this->inviteVitId = null;
        $this->inviteEmail = '';
    }

    public function sendInvitation(): void
    {
        $this->validate([
            'inviteEmail' => ['required', 'email', 'max:255'],
        ]);

        $vit = User::where('id', $this->inviteVitId)->where('can_login', false)->firstOrFail();

        WineryViticulturist::where('parent_viticulturist_id', Auth::id())
            ->where('viticulturist_id', $vit->id)
            ->firstOrFail();

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
            'invitation_token' => hash('sha256', $plainToken),
            'invitation_sent_at' => now(),
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
            'invitation_token' => null,
            'invitation_expires_at' => null,
            'invitation_sent_at' => null,
        ]);

        $this->toastSuccess(__('Invitación revocada.'));
    }

    protected function hasRealEmail(User $vit): bool
    {
        return $vit->email && ! str_starts_with($vit->email, 'viticultores.');
    }
}
