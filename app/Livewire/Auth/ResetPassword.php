<?php

namespace App\Livewire\Auth;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\RateLimiter;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\User;

class ResetPassword extends Component
{
    use WithToastNotifications;
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $token = '';
    public $tokenValid = false;

    public function mount($token, $email = null)
    {
        $this->token = $token;
        
        // Obtener email del query string si no viene como parámetro
        if (!$email && request()->has('email')) {
            $email = request()->query('email');
        }
        
        // Decodificar email si viene codificado en la URL
        if ($email) {
            $this->email = urldecode($email);
            
            // Validar que el email existe
            $user = User::where('email', $this->email)->first();
            if (!$user) {
                $this->toastError('El email proporcionado no existe en nuestro sistema.');
                return $this->redirect(route('password.request'), navigate: true);
            }
            
            // Validar token al cargar la página
            // Verificar si existe un registro de reset para este email y si no ha expirado
            $expireMinutes = config('auth.passwords.users.expire', 120);
            
            $resetRecord = DB::table('password_reset_tokens')
                ->where('email', $this->email)
                ->first();
            
            if (!$resetRecord) {
                $this->tokenValid = false;
                $this->toastError('No se encontró una solicitud de restablecimiento para este email. Por favor, solicita uno nuevo.');
            } else {
                // Verificar si el token ha expirado
                $createdAt = \Carbon\Carbon::parse($resetRecord->created_at);
                $expireTime = $createdAt->addMinutes($expireMinutes);
                
                if (now()->greaterThan($expireTime)) {
                    $this->tokenValid = false;
                    $this->toastError('El enlace de restablecimiento ha expirado. Por favor, solicita uno nuevo.');
                } else {
                    // El token existe y no ha expirado
                    // La validación final del token se hará cuando se envíe el formulario
                    $this->tokenValid = true;
                }
            }
        }
    }

    protected function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ];
    }

    protected function messages(): array
    {
        return [
            'email.required' => 'El campo email es obligatorio.',
            'email.email' => 'El email debe ser una dirección de correo válida.',
            'password.required' => 'El campo contraseña es obligatorio.',
            'password.confirmed' => 'Las contraseñas no coinciden. Por favor, verifica que ambas contraseñas sean iguales.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
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
            $this->toastSuccess('Tu contraseña ha sido restablecida correctamente. Ya puedes iniciar sesión.');
            return $this->redirect(route('login'), navigate: true);
        } else {
            throw ValidationException::withMessages([
                'email' => 'El enlace de restablecimiento no es válido o ha expirado. Por favor, solicita uno nuevo.',
            ]);
        }
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.auth.reset-password');
    }
}
