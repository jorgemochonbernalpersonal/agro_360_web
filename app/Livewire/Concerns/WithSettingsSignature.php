<?php

namespace App\Livewire\Concerns;

use App\Models\DigitalSignature;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use RuntimeException;

trait WithSettingsSignature
{
    public string $signaturePassword = '';

    public string $signaturePassword_confirmation = '';

    public bool $hasDigitalSignature = false;

    public bool $showResetPasswordModal = false;

    public string $loginPasswordForReset = '';

    public function loadDigitalSignature(): void
    {
        $this->hasDigitalSignature = DigitalSignature::forUser(Auth::id()) !== null;
    }

    public function saveDigitalSignature(): void
    {
        $this->validate([
            'signaturePassword' => [
                'required', 'string', 'min:8', 'confirmed',
                'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/',
            ],
        ], [
            'signaturePassword.required' => __('La contraseña de firma es obligatoria.'),
            'signaturePassword.min' => __('La contraseña debe tener al menos 8 caracteres.'),
            'signaturePassword.confirmed' => __('Las contraseñas no coinciden.'),
            'signaturePassword.regex' => __('La contraseña debe contener al menos una mayúscula, una minúscula y un número.'),
        ]);

        $forbiddenPasswords = [
            'Password1', 'Password123', 'Password12345',
            'Agro3651', 'Agro365!', 'Agro365123',
            'Qwerty123', 'Abcd1234', 'Admin123',
            'Welcome1', 'Welcome123', 'Firma123',
            '12345678Aa', 'Aa123456', 'Password1!',
        ];

        foreach ($forbiddenPasswords as $forbidden) {
            if (strcasecmp($this->signaturePassword, $forbidden) === 0) {
                $this->addError('signaturePassword', __('Esta contraseña es demasiado común y predecible. Por seguridad, elige una contraseña más única para firmar documentos oficiales.'));

                return;
            }
        }

        try {
            $wasUpdate = $this->hasDigitalSignature;
            DigitalSignature::createOrUpdateForUser(Auth::id(), $this->signaturePassword);

            $this->signaturePassword = '';
            $this->signaturePassword_confirmation = '';
            $this->hasDigitalSignature = true;

            $this->dispatch('signature-updated');
            $this->toastSuccess('Contraseña de firma digital '.($wasUpdate ? __('actualizada') : __('creada')).' correctamente');
        } catch (\Exception $e) {
            $this->toastError($e instanceof RuntimeException ? $e->getMessage() : __('Error al guardar la configuración. Inténtalo de nuevo.'));
        }
    }

    public function openResetPasswordModal(): void
    {
        $this->showResetPasswordModal = true;
    }

    public function closeResetPasswordModal(): void
    {
        $this->showResetPasswordModal = false;
        $this->loginPasswordForReset = '';
        $this->resetValidation('loginPasswordForReset');
    }

    public function resetForgottenSignaturePassword(): void
    {
        $this->validate(
            ['loginPasswordForReset' => 'required|string'],
            ['loginPasswordForReset.required' => __('Debes ingresar tu contraseña de login.')]
        );

        $user = Auth::user();
        if (! Hash::check($this->loginPasswordForReset, $user->password)) {
            $this->addError('loginPasswordForReset', __('Contraseña de login incorrecta.'));

            return;
        }

        try {
            $signature = DigitalSignature::forUser($user->id);
            if ($signature) {
                $resetData = [
                    'reset_at' => now()->format('d/m/Y H:i:s'),
                    'ip_address' => request()->ip(),
                    'browser' => $this->getBrowserName(request()->userAgent()),
                    'device' => $this->getDeviceName(request()->userAgent()),
                ];

                Log::warning('Signature password reset', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip' => $resetData['ip_address'],
                    'browser' => $resetData['browser'],
                    'device' => $resetData['device'],
                ]);

                try {
                    \Mail::to($user->email)->send(
                        new \App\Mail\SignaturePasswordReset($user, $resetData)
                    );
                } catch (\Exception $emailError) {
                    Log::error('Error sending signature reset email: '.$emailError->getMessage());
                }

                $signature->delete();
            }

            $this->hasDigitalSignature = false;
            $this->closeResetPasswordModal();
            $this->dispatch('signature-updated');
            $this->toastSuccess(__('Contraseña de firma eliminada. Te hemos enviado un email de confirmación. Ahora puedes crear una nueva.'));
        } catch (\Exception $e) {
            $this->toastError($e instanceof RuntimeException ? $e->getMessage() : __('Error al resetear. Inténtalo de nuevo.'));
        }
    }

    protected function getBrowserName(string $userAgent): string
    {
        foreach (['Chrome', 'Firefox', 'Safari', 'Edge', 'Opera'] as $browser) {
            if (str_contains($userAgent, $browser)) {
                return __($browser);
            }
        }

        return __('Desconocido');
    }

    protected function getDeviceName(string $userAgent): string
    {
        $devices = [
            'Mobile' => __('Móvil'),
            'Tablet' => __('Tablet'),
            'Windows' => __('Windows PC'),
            'Macintosh' => __('Mac'),
            'Linux' => __('Linux PC'),
        ];

        foreach ($devices as $key => $name) {
            if (str_contains($userAgent, $key)) {
                return $name;
            }
        }

        return __('Escritorio');
    }
}
