<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ChangePasswordRequired extends Component
{
    public $current_password = '';
    public $password = '';
    public $password_confirmation = '';

    protected function rules(): array
    {
        return [
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    public function mount()
    {
        $user = Auth::user();
        
        // Si no necesita cambiar contraseña, redirigir al dashboard
        if (!$user->needsPasswordChange()) {
            return $this->redirect(route($this->getDashboardRoute()), navigate: true);
        }
    }

    public function changePassword()
    {
        $this->validate();

        $user = Auth::user();

        // Verificar que la contraseña actual es correcta
        if (!Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'La contraseña actual no es correcta.');
            return;
        }

        // Verificar que la nueva contraseña es diferente a la actual
        if (Hash::check($this->password, $user->password)) {
            $this->addError('password', 'La nueva contraseña debe ser diferente a la actual.');
            return;
        }

        // Actualizar contraseña
        $user->password = Hash::make($this->password);
        
        // Limpiar flag de cambio obligatorio si existe
        if ($user->password_must_reset) {
            $user->password_must_reset = false;
        }
        
        // Verificar email automáticamente cuando cambia la contraseña
        if (!$user->hasVerifiedEmail()) {
            $user->email_verified_at = now();
        }
        
        $user->save();
        
        // Limpiar cache de sesión para que el middleware recalcule
        session()->forget("user_{$user->id}_needs_password_change");
        
        // Limpiar cache en memoria del modelo
        unset($user->_needs_password_change_cache);
        unset($user->_was_created_by_another_cache);

        session()->flash('message', 'Contraseña cambiada correctamente. Tu email ha sido verificado.');
        
        return $this->redirect(route($this->getDashboardRoute()), navigate: true);
    }

    protected function getDashboardRoute(): string
    {
        $user = Auth::user();
        
        return match($user->role) {
            'admin' => 'admin.dashboard',
            'supervisor' => 'supervisor.dashboard',
            'winery' => 'winery.dashboard',
            'viticulturist' => 'viticulturist.dashboard',
            default => 'home',
        };
    }

    public function render()
    {
        return view('livewire.auth.change-password-required')->layout('layouts.app');
    }
}

