<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\RateLimiter;

class ForgotPassword extends Component
{
    public $email = '';

    protected $rules = [
        'email' => 'required|email',
    ];

    public function sendResetLink()
    {
        // Rate limiting: por IP
        $key = 'forgot-password.' . request()->ip();
        $maxAttempts = app()->environment('production') ? 5 : 100;
        $decaySeconds = app()->environment('production') ? 60 : 10;
        
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "Demasiados intentos. Por favor, intenta de nuevo en {$seconds} segundos.",
            ]);
        }

        RateLimiter::hit($key, $decaySeconds);

        $this->validate();

        // Enviar enlace de reset usando Laravel's Password broker
        $status = Password::sendResetLink(
            ['email' => $this->email]
        );

        if ($status === Password::RESET_LINK_SENT) {
            session()->flash('status', 'Se ha enviado un enlace de restablecimiento de contraseña a tu correo electrónico.');
            $this->email = ''; // Limpiar el campo
        } else {
            // Por seguridad, siempre mostrar el mismo mensaje aunque el email no exista
            session()->flash('status', 'Si el email existe en nuestro sistema, recibirás un enlace de restablecimiento de contraseña.');
            $this->email = ''; // Limpiar el campo
        }
    }

    public function render()
    {
        return view('livewire.auth.forgot-password')->layout('layouts.app');
    }
}
