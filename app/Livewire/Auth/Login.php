<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use App\Services\SecurityLogger;

class Login extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;
    public $recaptchaToken = '';  // Token de reCAPTCHA
    public $showCaptcha = false;  // Control para mostrar CAPTCHA
    public $honeypot = '';  // Honeypot anti-bots

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required',
    ];

    public function login()
    {
        // Honeypot: Si está lleno, es un bot
        if (!empty($this->honeypot)) {
            SecurityLogger::logSecurityEvent('honeypot_triggered', [
                'email' => $this->email,
                'honeypot_value' => substr($this->honeypot, 0, 50), // Solo primeros 50 caracteres
            ]);
            
            // Simular error genérico para no revelar el honeypot
            sleep(2); // Delay para confundir al bot
            throw ValidationException::withMessages([
                'email' => 'Las credenciales no son correctas.',
            ]);
        }
        
        // Rate limiting: por IP. En producción más estricto, en entornos de desarrollo/test más laxo
        $key = 'login.' . request()->ip();
        $failedKey = 'login.failed.' . request()->ip();
        $maxAttempts = app()->environment('production') ? 5 : 100;
        $decaySeconds = app()->environment('production') ? 60 : 10;
        
        // Verificar si se requiere CAPTCHA (después de 3 intentos fallidos)
        $failedAttempts = RateLimiter::attempts($failedKey);
        if ($failedAttempts >= 3) {
            $this->showCaptcha = true;
            
            // Validar reCAPTCHA si está habilitado
            if (config('services.recaptcha.enabled', false)) {
                if (empty($this->recaptchaToken)) {
                    SecurityLogger::logCaptchaActivated($this->email);
                    throw ValidationException::withMessages([
                        'email' => 'Por favor, completa la verificación CAPTCHA.',
                    ]);
                }
                
                if (!$this->validateRecaptcha($this->recaptchaToken)) {
                    SecurityLogger::logCaptchaValidationFailed($this->email);
                    throw ValidationException::withMessages([
                        'email' => 'La verificación CAPTCHA falló. Por favor, inténtalo de nuevo.',
                    ]);
                }
            }
        }
        
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            SecurityLogger::logRateLimitReached($key, $maxAttempts);
            throw ValidationException::withMessages([
                'email' => "Demasiados intentos. Por favor, intenta de nuevo en {$seconds} segundos.",
            ]);
        }

        RateLimiter::hit($key, $decaySeconds);

        $this->validate();

        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            // Incrementar contador de intentos fallidos
            RateLimiter::hit($failedKey, 3600); // Expira en 1 hora
            
            // Loguear intento fallido
            SecurityLogger::logFailedLogin($this->email, 'credenciales_incorrectas');
            
            // Mostrar CAPTCHA si ya hay 2+ intentos fallidos
            if (RateLimiter::attempts($failedKey) >= 3) {
                $this->showCaptcha = true;
                SecurityLogger::logCaptchaActivated($this->email);
            }
            
            throw ValidationException::withMessages([
                'email' => 'Las credenciales no son correctas.',
            ]);
        }

        $user = Auth::user();
        
        // Loguear login exitoso si hubo intentos fallidos previos
        $previousFailedAttempts = RateLimiter::attempts($failedKey);
        if ($previousFailedAttempts > 0) {
            SecurityLogger::logSuccessfulLoginAfterFailures(
                $user->id,
                $user->email,
                $previousFailedAttempts
            );
        }

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

        // Limpiar contador de intentos fallidos en login exitoso
        RateLimiter::clear('login.failed.' . request()->ip());

        return $this->redirect(route($this->getDashboardRoute()), navigate: true);
    }
    
    /**
     * Validar token de reCAPTCHA con Google
     */
    protected function validateRecaptcha(string $token): bool
    {
        $secretKey = config('services.recaptcha.secret_key');
        
        if (empty($secretKey)) {
            // Si no está configurado, permitir login (útil en desarrollo)
            return true;
        }
        
        try {
            $response = \Illuminate\Support\Facades\Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $secretKey,
                'response' => $token,
                'remoteip' => request()->ip(),
            ]);
            
            $result = $response->json();
            
            return isset($result['success']) && $result['success'] === true;
        } catch (\Exception $e) {
            // En caso de error de conexión, permitir login (fail open)
            \Log::warning('reCAPTCHA validation failed', [
                'error' => $e->getMessage(),
                'ip' => request()->ip(),
            ]);
            return true;
        }
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

