<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Livewire\Concerns\WithToastNotifications;

class ForcePasswordReset extends Component
{
    use WithToastNotifications;
    public $current_password = '';
    public $new_password = '';
    public $new_password_confirmation = '';

    protected function rules()
    {
        return [
            'current_password' => 'required',
            'new_password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    protected function messages(): array
    {
        return [
            'current_password.required' => __('El campo contraseña temporal es obligatorio.'),
            'new_password.required' => __('El campo nueva contraseña es obligatorio.'),
            'new_password.confirmed' => __('Las contraseñas no coinciden. Por favor, verifica que ambas contraseñas sean iguales.'),
            'new_password.min' => __('La contraseña debe tener al menos 8 caracteres.'),
        ];
    }

    public function updatePassword()
    {
        $this->validate();

        $user = auth()->user();

        // Verificar que la contraseña actual es correcta
        if (!Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', __('La contraseña temporal es incorrecta.'));
            return;
        }

        // Actualizar contraseña y marcar como verificado
        $user->update([
            'password' => Hash::make($this->new_password),
            'password_must_reset' => false,
            'email_verified_at' => now(), // Verifica el email automáticamente
        ]);

        // Regenerar sesión (como si fuera nuevo login)
        auth()->logout();
        auth()->login($user);
        request()->session()->regenerate();

        $this->toastSuccess(__('¡Contraseña actualizada exitosamente! Bienvenido al sistema.'));

        // Redirigir al dashboard correspondiente
        return $this->redirect(route($this->getDashboardRoute()), navigate: true);
    }

    protected function getDashboardRoute(): string
    {
        $user = auth()->user();
        
        return match($user->role) {
            'admin' => 'admin.dashboard',
            'supervisor' => 'supervisor.dashboard',
            'winery' => 'winery.dashboard',
            'viticulturist' => 'viticulturist.dashboard',
            'producer'      => 'producer.dashboard',
            default => 'home',
        };
    }

    public function render()
    {
        return view('livewire.auth.force-password-reset')
            ->layout('layouts.guest');
    }
}
