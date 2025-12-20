<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\RateLimiter;

class ResetPassword extends Component
{
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $token = '';

    public function mount($token, $email = null)
    {
        $this->token = $token;
        $this->email = $email ?? '';
    }

    protected function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ];
    }

    public function resetPassword()
    {
        // Rate limiting: por IP
        $key = 'reset-password.' . request()->ip();
        $maxAttempts = app()->environment('production') ? 5 : 100;
        $decaySeconds = app()->environment('production') ? 60 : 10;
        
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'password' => "Demasiados intentos. Por favor, intenta de nuevo en {$seconds} segundos.",
            ]);
        }

        RateLimiter::hit($key, $decaySeconds);

        $this->validate();

        // Resetear contraseña usando Laravel's Password broker
        $status = Password::reset(
            [
                'email' => $this->email,
                'password' => $this->password,
                'password_confirmation' => $this->password_confirmation,
                'token' => $this->token,
            ],
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            session()->flash('status', 'Tu contraseña ha sido restablecida correctamente. Ya puedes iniciar sesión.');
            return $this->redirect(route('login'), navigate: true);
        } else {
            throw ValidationException::withMessages([
                'email' => 'El enlace de restablecimiento no es válido o ha expirado. Por favor, solicita uno nuevo.',
            ]);
        }
    }

    public function render()
    {
        return view('livewire.auth.reset-password')->layout('layouts.app');
    }
}
