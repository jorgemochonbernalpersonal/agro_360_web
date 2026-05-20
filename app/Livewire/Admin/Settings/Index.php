<?php

namespace App\Livewire\Admin\Settings;

use App\Livewire\Concerns\WithToastNotifications;
use App\Livewire\Concerns\WithReadOnlyGuard;
use App\Models\AppSetting;
use App\Models\SecurityEvent;
use App\Services\SecurityLogger;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Index extends Component
{
    use WithToastNotifications, WithReadOnlyGuard;

    // Platform
    public bool   $registration_open = true;
    public bool   $maintenance_mode  = false;
    public string $support_email     = '';

    // Beta
    public string $beta_end_date = '';

    // Security
    public int  $password_min_length      = 8;
    public bool $require_strong_password  = false;

    // Modules
    public bool $module_silicie = true;
    public bool $module_pac     = true;

    protected function rules(): array
    {
        return [
            'support_email'       => 'required|email|max:255',
            'beta_end_date'       => 'required|date|after:today',
            'password_min_length' => 'required|integer|min:6|max:32',
        ];
    }

    protected function messages(): array
    {
        return [
            'support_email.required'       => 'El email de soporte es obligatorio.',
            'support_email.email'          => 'Introduce un email válido.',
            'beta_end_date.required'       => 'La fecha límite beta es obligatoria.',
            'beta_end_date.after'          => 'La fecha debe ser posterior a hoy.',
            'password_min_length.min'      => 'El mínimo permitido es 6 caracteres.',
            'password_min_length.max'      => 'El máximo permitido es 32 caracteres.',
        ];
    }

    public function mount(): void
    {
        $this->registration_open = AppSetting::getBool('registration_open', true);
        $this->maintenance_mode  = AppSetting::getBool('maintenance_mode', false);
        $this->support_email     = AppSetting::get('support_email', 'info@agro365.es');
        $this->beta_end_date     = AppSetting::get('beta_end_date', '2026-06-30');

        $this->password_min_length     = (int) AppSetting::get('password_min_length', 8);
        $this->require_strong_password = AppSetting::getBool('require_strong_password', false);

        $this->module_silicie = AppSetting::getBool('module_silicie', true);
        $this->module_pac     = AppSetting::getBool('module_pac', true);
    }

    public function savePlatform(): void
    {
        if ($this->isReadOnly()) return;
        $this->validateOnly('support_email');

        $changes = [];
        $oldRegistration = AppSetting::getBool('registration_open', true);
        $oldMaintenance  = AppSetting::getBool('maintenance_mode', false);
        $oldEmail        = AppSetting::get('support_email', '');

        if ($oldRegistration !== $this->registration_open) {
            $changes['registration_open'] = ['from' => $oldRegistration, 'to' => $this->registration_open];
        }
        if ($oldMaintenance !== $this->maintenance_mode) {
            $changes['maintenance_mode'] = ['from' => $oldMaintenance, 'to' => $this->maintenance_mode];
        }
        if ($oldEmail !== $this->support_email) {
            $changes['support_email'] = ['from' => $oldEmail, 'to' => $this->support_email];
        }

        AppSetting::set('registration_open', $this->registration_open ? '1' : '0');
        AppSetting::set('maintenance_mode',  $this->maintenance_mode  ? '1' : '0');
        AppSetting::set('support_email',     $this->support_email);

        if (!empty($changes)) {
            SecurityLogger::logSecurityEvent('settings_platform_changed', [
                'admin_id' => Auth::id(),
                'changes'  => $changes,
            ]);
        }

        $this->toastSuccess('Configuración de plataforma guardada.');
    }

    public function saveBeta(): void
    {
        if ($this->isReadOnly()) return;
        $this->validateOnly('beta_end_date');

        $oldDate = AppSetting::get('beta_end_date', '');

        AppSetting::set('beta_end_date', $this->beta_end_date);

        if ($oldDate !== $this->beta_end_date) {
            SecurityLogger::logSecurityEvent('settings_beta_date_changed', [
                'admin_id' => Auth::id(),
                'from'     => $oldDate,
                'to'       => $this->beta_end_date,
            ]);
        }

        $this->toastSuccess('Fecha beta actualizada.');
    }

    public function saveSecurity(): void
    {
        if ($this->isReadOnly()) return;
        $this->validateOnly('password_min_length');

        $changes = [];
        $prev = [
            'password_min_length'     => (int) AppSetting::get('password_min_length', 8),
            'require_strong_password' => AppSetting::getBool('require_strong_password', false),
        ];

        if ($prev['password_min_length'] !== $this->password_min_length) {
            $changes['password_min_length'] = ['from' => $prev['password_min_length'], 'to' => $this->password_min_length];
        }
        if ($prev['require_strong_password'] !== $this->require_strong_password) {
            $changes['require_strong_password'] = ['from' => $prev['require_strong_password'], 'to' => $this->require_strong_password];
        }

        AppSetting::set('password_min_length',     (string) $this->password_min_length);
        AppSetting::set('require_strong_password', $this->require_strong_password ? '1' : '0');

        if (!empty($changes)) {
            SecurityLogger::logSecurityEvent('settings_security_changed', [
                'admin_id' => Auth::id(),
                'changes'  => $changes,
            ]);
        }

        $this->toastSuccess('Política de contraseñas guardada.');
    }

    public function saveModules(): void
    {
        if ($this->isReadOnly()) return;
        $changes = [];
        $prev = [
            'module_silicie' => AppSetting::getBool('module_silicie', true),
            'module_pac'     => AppSetting::getBool('module_pac', true),
        ];

        if ($prev['module_silicie'] !== $this->module_silicie) {
            $changes['module_silicie'] = ['from' => $prev['module_silicie'], 'to' => $this->module_silicie];
        }
        if ($prev['module_pac'] !== $this->module_pac) {
            $changes['module_pac'] = ['from' => $prev['module_pac'], 'to' => $this->module_pac];
        }

        AppSetting::set('module_silicie', $this->module_silicie ? '1' : '0');
        AppSetting::set('module_pac',     $this->module_pac     ? '1' : '0');

        if (!empty($changes)) {
            SecurityLogger::logSecurityEvent('settings_modules_changed', [
                'admin_id' => Auth::id(),
                'changes'  => $changes,
            ]);
        }

        $this->toastSuccess('Configuración de módulos guardada.');
    }

    private function lastChangeFor(string $event): ?SecurityEvent
    {
        try {
            return SecurityEvent::where('event', $event)
                ->orderByDesc('created_at')
                ->first();
        } catch (\Throwable) {
            return null;
        }
    }

    #[Layout('layouts.app', [
        'title'       => 'Configuración - Agro365',
        'description' => 'Configuración global del sistema',
    ])]
    public function render()
    {
        return view('livewire.admin.settings.index', [
            'lastPlatform' => $this->lastChangeFor('settings_platform_changed'),
            'lastBeta'     => $this->lastChangeFor('settings_beta_date_changed'),
            'lastSecurity' => $this->lastChangeFor('settings_security_changed'),
            'lastModules'  => $this->lastChangeFor('settings_modules_changed'),
        ]);
    }
}
