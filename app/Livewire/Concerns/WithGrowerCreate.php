<?php

namespace App\Livewire\Concerns;

use App\Models\SupervisorViticulturist;
use App\Models\User;
use App\Notifications\ViticulturistInvitationNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

trait WithGrowerCreate
{
    public bool $showCreateModal = false;

    public string $createName = '';

    public string $createEmail = '';

    public string $createDni = '';

    public string $createPhone = '';

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
            'createName' => ['required', 'string', 'max:255'],
            'createEmail' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'createDni' => ['nullable', 'string', 'max:20', Rule::unique('users', 'dni')],
            'createPhone' => ['nullable', 'string', 'max:20'],
        ], [
            'createName.required' => __('El nombre es obligatorio.'),
            'createEmail.email' => __('El email no es válido.'),
            'createEmail.unique' => __('Ya existe un usuario con este email.'),
            'createDni.unique' => __('Ya existe un usuario activo con este DNI.'),
        ]);

        $viticulturist = User::create([
            'name' => $this->createName,
            'email' => $this->createEmail ?: ('viticultores.'.Str::uuid().'@noemail.agro365.es'),
            'dni' => $this->createDni ? strtoupper(trim($this->createDni)) : null,
            'role' => User::ROLE_VITICULTURIST,
            'can_login' => false,
            'password' => Hash::make(Str::random(40)),
        ]);

        if ($this->createPhone) {
            $viticulturist->profile()->create(['phone' => $this->createPhone]);
        }

        SupervisorViticulturist::create([
            'supervisor_id' => Auth::id(),
            'viticulturist_id' => $viticulturist->id,
            'assigned_by' => Auth::id(),
        ]);

        if ($this->createEmail) {
            $plainToken = Str::random(64);
            $viticulturist->update([
                'invitation_token' => hash('sha256', $plainToken),
                'invitation_sent_at' => now(),
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
}
