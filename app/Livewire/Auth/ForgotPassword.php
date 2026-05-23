<?php

namespace App\Livewire\Auth;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\RateLimiter;

use App\Livewire\Concerns\WithToastNotifications;

class ForgotPassword extends Component
{
    use WithToastNotifications;
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
        try {
            $status = Password::sendResetLink(
                ['email' => $this->email]
            );

            if ($status === Password::RESET_LINK_SENT) {
                $message = 'Se ha enviado un enlace de restablecimiento de contraseña a tu correo electrónico.';
                
                if (app()->environment('local')) {
                    Log::info('Password reset link sent', ['email' => $this->email]);
                }
                
                $this->toastSuccess($message);
                $this->email = ''; // Limpiar el campo
            } else {
                // Por seguridad, siempre mostrar el mismo mensaje aunque el email no exista
                $this->toastSuccess('Si el email existe en nuestro sistema, recibirás un enlace de restablecimiento de contraseña.');
                $this->email = ''; // Limpiar el campo
            }
        } catch (\Exception $e) {
            // Log del error (en todos los entornos para debugging en producción también)
            Log::error('Error sending password reset link', [
                'email' => $this->email,
                'error' => $e->getMessage(),
                'environment' => app()->environment(),
                'trace' => app()->environment('local') ? $e->getTraceAsString() : 'Trace hidden in production',
            ]);
            
            // En desarrollo, mostrar el error detallado
            if (app()->environment('local')) {
                $this->toastError('Error al enviar el correo: ' . $e->getMessage() . '. Revisa los logs y MailHog.');
            } else {
                // En producción, mensaje genérico por seguridad
                $this->toastSuccess('Si el email existe en nuestro sistema, recibirás un enlace de restablecimiento de contraseña.');
            }
            $this->email = '';
        }
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.auth.forgot-password');
    }
}
