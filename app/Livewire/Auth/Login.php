<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

class Login extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required',
    ];

    public function login()
    {
        // Rate limiting: por IP. En producción más estricto, en entornos de desarrollo/test más laxo
        $key = 'login.' . request()->ip();
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

        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            throw ValidationException::withMessages([
                'email' => 'Las credenciales no son correctas.',
            ]);
        }

        $user = Auth::user();

        // Bloquear acceso si la cuenta no está activada para iniciar sesión
        if (property_exists($user, 'can_login') && $user->can_login === false) {
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'Tu cuenta aún no está activada. Por favor, contacta con quien te dio de alta o regístrate para activar tu acceso.',
            ]);
        }

        // Verificar si el email está verificado
        // Permitir login sin verificación si fue creado por otro usuario (viticultor, winery o supervisor)
        if (!$user->hasVerifiedEmail() && !$user->wasCreatedByAnotherUser()) {
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();
            
            throw ValidationException::withMessages([
                'email' => 'Debes verificar tu email antes de iniciar sesión. Revisa tu correo electrónico para el enlace de verificación.',
            ]);
        }

        session()->regenerate();

        // Si fue creado por otro usuario y no ha verificado email, forzar cambio de contraseña
        if ($user->needsPasswordChange()) {
            return $this->redirect(route('auth.change-password-required'), navigate: true);
        }

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
        return view('livewire.auth.login')->layout('layouts.app');
    }
}

