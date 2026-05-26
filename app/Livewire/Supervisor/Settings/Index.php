<?php

namespace App\Livewire\Supervisor\Settings;

use App\Livewire\Concerns\WithToastNotifications;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Index extends Component
{
    use WithToastNotifications;

    public string $name  = '';
    public string $email = '';

    public string $current_password = '';
    public string $new_password     = '';
    public string $confirm_password = '';

    public function mount(): void
    {
        $user        = Auth::user();
        $this->name  = $user->name;
        $this->email = $user->email;
    }

    public function updateProfile(): void
    {
        $this->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . Auth::id(),
        ]);

        Auth::user()->update([
            'name'  => $this->name,
            'email' => $this->email,
        ]);

        $this->toastSuccess(__('Perfil actualizado correctamente.'));
    }

    public function updatePassword(): void
    {
        $this->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:8|same:confirm_password',
            'confirm_password' => 'required|string',
        ]);

        if (!Hash::check($this->current_password, Auth::user()->password)) {
            $this->addError('current_password', __('La contraseña actual no es correcta.'));
            return;
        }

        Auth::user()->update(['password' => Hash::make($this->new_password)]);

        $this->reset(['current_password', 'new_password', 'confirm_password']);
        $this->toastSuccess(__('Contraseña actualizada correctamente.'));
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.supervisor.settings.index');
    }
}
