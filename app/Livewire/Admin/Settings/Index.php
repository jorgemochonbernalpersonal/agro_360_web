<?php

namespace App\Livewire\Admin\Settings;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\AppSetting;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Index extends Component
{
    use WithToastNotifications;

    // Platform
    public bool   $registration_open = true;
    public bool   $maintenance_mode  = false;
    public string $support_email     = '';

    // Beta
    public string $beta_end_date = '';

    protected function rules(): array
    {
        return [
            'support_email' => 'required|email|max:255',
            'beta_end_date' => 'required|date|after:today',
        ];
    }

    protected function messages(): array
    {
        return [
            'support_email.required' => 'El email de soporte es obligatorio.',
            'support_email.email'    => 'Introduce un email válido.',
            'beta_end_date.required' => 'La fecha límite beta es obligatoria.',
            'beta_end_date.after'    => 'La fecha debe ser posterior a hoy.',
        ];
    }

    public function mount(): void
    {
        $this->registration_open = AppSetting::getBool('registration_open', true);
        $this->maintenance_mode  = AppSetting::getBool('maintenance_mode', false);
        $this->support_email     = AppSetting::get('support_email', 'soporte@agro365.es');
        $this->beta_end_date     = AppSetting::get('beta_end_date', '2026-06-30');
    }

    public function savePlatform(): void
    {
        $this->validateOnly('support_email');

        AppSetting::set('registration_open', $this->registration_open ? '1' : '0');
        AppSetting::set('maintenance_mode',  $this->maintenance_mode  ? '1' : '0');
        AppSetting::set('support_email',     $this->support_email);

        $this->toastSuccess('Configuración de plataforma guardada.');
    }

    public function saveBeta(): void
    {
        $this->validateOnly('beta_end_date');

        AppSetting::set('beta_end_date', $this->beta_end_date);

        $this->toastSuccess('Fecha beta actualizada.');
    }

    #[Layout('layouts.app', [
        'title'       => 'Configuración - Agro365',
        'description' => 'Configuración global del sistema',
    ])]
    public function render()
    {
        return view('livewire.admin.settings.index');
    }
}
