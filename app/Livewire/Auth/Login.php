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
    public $remember = true;
    public $recaptchaToken = '';  // Token de reCAPTCHA
    public $showCaptcha = false;  // Control para mostrar CAPTCHA
    public $honeypot = '';  // Honeypot anti-bots

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required',
    ];

    public function mount(): void
    {
        // Redirigir si llegan credenciales como query params (URLs históricas de GET)
        if (request()->query('email') || request()->query('password')) {
            redirect()->route('login')->send();
            return;
        }

        if (session('verified_email')) {
            $this->email = session('verified_email');
        } elseif (request()->query('reset_email')) {
            $this->email = urldecode(request()->query('reset_email'));
        }
    }

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
                'email' => __('Las credenciales no son correctas.'),
            ]);
        }
        
        // Rate limiting: por IP. En producción más estricto, en entornos de desarrollo/test más laxo
        $key = 'login.' . request()->ip();
        $emailKey = 'login.email.' . sha1(strtolower(trim($this->email)));
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
                        'email' => __('Por favor, completa la verificación CAPTCHA.'),
                    ]);
                }
                
                if (!$this->validateRecaptcha($this->recaptchaToken)) {
                    SecurityLogger::logCaptchaValidationFailed($this->email);
                    throw ValidationException::withMessages([
                        'email' => __('La verificación CAPTCHA falló. Por favor, inténtalo de nuevo.'),
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

        // Rate limit por email (bloquea ataques distribuidos desde múltiples IPs)
        if (RateLimiter::tooManyAttempts($emailKey, 10)) {
            $seconds = RateLimiter::availableIn($emailKey);
            SecurityLogger::logRateLimitReached($emailKey, 10);
            throw ValidationException::withMessages([
                'email' => "Demasiados intentos para este email. Por favor, intenta de nuevo en {$seconds} segundos.",
            ]);
        }

        RateLimiter::hit($key, $decaySeconds);
        RateLimiter::hit($emailKey, 3600);

        $this->validate();

        $loginEmail = strtolower(trim($this->email));

        if (!Auth::attempt(['email' => $loginEmail, 'password' => $this->password], $this->remember)) {
            // Incrementar contador de intentos fallidos
            RateLimiter::hit($failedKey, 3600); // Expira en 1 hora

            // Loguear intento fallido
            SecurityLogger::logFailedLogin($loginEmail, 'credenciales_incorrectas');
            
            // Mostrar CAPTCHA si ya hay 2+ intentos fallidos
            if (RateLimiter::attempts($failedKey) >= 3) {
                $this->showCaptcha = true;
                SecurityLogger::logCaptchaActivated($this->email);
            }
            
            throw ValidationException::withMessages([
                'email' => __('Las credenciales no son correctas.'),
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
        if ($user->can_login === false) {
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => __('Tu cuenta aún no está activada. Por favor, contacta con quien te dio de alta o regístrate para activar tu acceso.'),
            ]);
        }

        // Verificar si el email está verificado
        // Permitir login sin verificación si fue creado por otro usuario (viticultor, winery o supervisor)
        if (!$user->hasVerifiedEmail() && !$user->wasCreatedByAnotherUser()) {
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();
            
            throw ValidationException::withMessages([
                'email' => __('Debes verificar tu email antes de iniciar sesión. Revisa tu correo electrónico para el enlace de verificación.'),
            ]);
        }

        session()->regenerate();

        // Registrar última conexión real (no se actualiza en impersonación)
        $user->update(['last_login_at' => now()]);

        // Si fue creado por otro usuario y no ha verificado email, forzar cambio de contraseña
        if ($user->needsPasswordChange()) {
            return $this->redirect(route('auth.change-password-required'), navigate: true);
        }

        // Limpiar contadores de intentos fallidos en login exitoso
        RateLimiter::clear('login.failed.' . request()->ip());
        RateLimiter::clear('login.email.' . sha1(strtolower(trim($this->email))));

        return $this->redirect(route($this->getDashboardRoute()), navigate: true);
    }
    
    /**
     * Validar token de reCAPTCHA con Google
     */
    protected function validateRecaptcha(string $token): bool
    {
        $secretKey = config('services.recaptcha.secret_key');

        if (empty($secretKey)) {
            // Solo permitir sin CAPTCHA en desarrollo/test
            if (app()->environment('production')) {
                \Log::error('reCAPTCHA secret_key no configurado en producción');
                return false;
            }
            return true;
        }

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(5)->asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $secretKey,
                'response' => $token,
                'remoteip' => request()->ip(),
            ]);

            $result = $response->json();

            return isset($result['success']) && $result['success'] === true;
        } catch (\Exception $e) {
            \Log::warning('reCAPTCHA validation failed', [
                'error' => $e->getMessage(),
                'ip' => request()->ip(),
            ]);
            // Fail closed en producción, fail open en desarrollo
            return !app()->environment('production');
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
            'producer'      => 'producer.dashboard',
            default => 'home',
        };
    }

    public function render()
    {
        return view('livewire.auth.login')->layout('layouts.guest');
    }
}

