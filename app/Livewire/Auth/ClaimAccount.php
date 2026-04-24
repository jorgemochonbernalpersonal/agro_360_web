<?php

namespace App\Livewire\Auth;

use App\Models\SupervisorViticulturist;
use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ClaimAccount extends Component
{
    public string $token    = '';
    public string $name     = '';
    public string $email    = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool   $shareCuaderno = true;

    // Estado
    public bool   $tokenValid    = false;
    public bool   $activated     = false;
    public ?User  $pendingUser   = null;
    public ?string $wineryName   = null;  // bodega o DO que invitó
    public string  $invitorType  = 'winery'; // 'winery' | 'supervisor'

    public function mount(string $token): void
    {
        $this->token = $token;

        // Búsqueda directa por hash SHA-256 (O(1) vs O(n) bcrypt)
        $user = User::where('can_login', false)
            ->where('invitation_expires_at', '>', now())
            ->where('invitation_token', hash('sha256', $token))
            ->first();

        if (!$user) {
            $this->tokenValid = false;
            return;
        }

        $this->tokenValid  = true;
        $this->pendingUser = $user;
        $this->name        = $user->name;

        // Pre-rellenar email si ya tiene uno real
        if ($user->email && !str_starts_with($user->email, 'viticultores.')) {
            $this->email = $user->email;
        }

        // Comprobar si lo invitó una bodega
        $wineryRelation = WineryViticulturist::where('viticulturist_id', $user->id)
            ->whereNotNull('winery_id')
            ->with('winery:id,name')
            ->first();

        if ($wineryRelation) {
            $this->invitorType = 'winery';
            $this->wineryName  = $wineryRelation->winery->name;
            return;
        }

        // Comprobar si lo invitó un supervisor (DO)
        $supervisorRelation = SupervisorViticulturist::where('viticulturist_id', $user->id)
            ->with('supervisor:id,name')
            ->first();

        if ($supervisorRelation) {
            $this->invitorType = 'supervisor';
            $this->wineryName  = $supervisorRelation->supervisor->name;
            return;
        }

        // Ghost sin relación vinculada
        $this->tokenValid = false;
    }

    protected function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => [
                'required', 'email', 'max:255',
                'unique:users,email,' . ($this->pendingUser?->id ?? 0),
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required'      => 'El nombre es obligatorio.',
            'email.required'     => 'El email es obligatorio.',
            'email.email'        => 'El email no es válido.',
            'email.unique'       => 'Este email ya está registrado.',
            'password.required'  => 'La contraseña es obligatoria.',
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ];
    }

    public function activate(): void
    {
        if (!$this->tokenValid || !$this->pendingUser) {
            return;
        }

        $this->validate();

        $this->pendingUser->update([
            'name'                   => $this->name,
            'email'                  => $this->email,
            'password'               => Hash::make($this->password),
            'can_login'              => true,
            'email_verified_at'      => now(),
            'invitation_token'       => null,
            'invitation_expires_at'  => null,
            'invitation_sent_at'     => null,
        ]);

        // Auto-aprobar acceso al cuaderno si el viticultor aceptó
        if ($this->shareCuaderno) {
            if ($this->invitorType === 'supervisor') {
                SupervisorViticulturist::where('viticulturist_id', $this->pendingUser->id)
                    ->update([
                        'notebook_access'     => true,
                        'notebook_granted_at' => now(),
                        'notebook_revoked_at' => null,
                    ]);
            } else {
                WineryViticulturist::where('viticulturist_id', $this->pendingUser->id)
                    ->whereNotNull('winery_id')
                    ->update([
                        'notebook_access'     => true,
                        'notebook_granted_at' => now(),
                        'notebook_revoked_at' => null,
                    ]);
            }
        }

        // Login automático con modelo fresco de BD (garantiza email_verified_at cargado)
        Auth::login($this->pendingUser->fresh());

        $this->activated = true;

        $this->redirectRoute('viticulturist.dashboard', navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.claim-account')
            ->layout('layouts.guest');
    }
}
