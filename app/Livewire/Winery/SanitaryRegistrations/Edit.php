<?php

namespace App\Livewire\Winery\SanitaryRegistrations;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\SanitaryRegistration;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Edit extends Component
{
    use WithToastNotifications;

    public SanitaryRegistration $sanitaryRegistration;

    public string $registration_number  = '';
    public string $registration_type    = '';
    public string $activity_description = '';
    public string $registration_date    = '';
    public string $renewal_date         = '';
    public string $issuing_authority    = '';
    public string $status               = '';
    public string $notes                = '';

    public function mount(SanitaryRegistration $sanitaryRegistration): void
    {
        abort_if($sanitaryRegistration->user_id !== Auth::id(), 403);
        $this->sanitaryRegistration   = $sanitaryRegistration;
        $this->registration_number    = $sanitaryRegistration->registration_number;
        $this->registration_type      = $sanitaryRegistration->registration_type;
        $this->activity_description   = $sanitaryRegistration->activity_description ?? '';
        $this->registration_date      = $sanitaryRegistration->registration_date?->toDateString() ?? '';
        $this->renewal_date           = $sanitaryRegistration->renewal_date?->toDateString() ?? '';
        $this->issuing_authority      = $sanitaryRegistration->issuing_authority ?? '';
        $this->status                 = $sanitaryRegistration->status;
        $this->notes                  = $sanitaryRegistration->notes ?? '';
    }

    protected function rules(): array
    {
        return [
            'registration_number'  => ['required', 'string', 'max:100'],
            'registration_type'    => ['required', 'in:' . implode(',', array_keys(SanitaryRegistration::REGISTRATION_TYPES))],
            'activity_description' => ['nullable', 'string', 'max:300'],
            'registration_date'    => ['nullable', 'date'],
            'renewal_date'         => ['nullable', 'date'],
            'issuing_authority'    => ['nullable', 'string', 'max:200'],
            'status'               => ['required', 'in:' . implode(',', array_keys(SanitaryRegistration::STATUSES))],
            'notes'                => ['nullable', 'string'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $this->sanitaryRegistration->update([
            'registration_number'  => $this->registration_number,
            'registration_type'    => $this->registration_type,
            'activity_description' => $this->activity_description ?: null,
            'registration_date'    => $this->registration_date ?: null,
            'renewal_date'         => $this->renewal_date ?: null,
            'issuing_authority'    => $this->issuing_authority ?: null,
            'status'               => $this->status,
            'notes'                => $this->notes ?: null,
        ]);

        $this->toastSuccess('Registro sanitario actualizado correctamente.');
        $this->redirect(route('winery.sanitary-registrations.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.winery.sanitary-registrations.edit', [
            'types'    => SanitaryRegistration::REGISTRATION_TYPES,
            'statuses' => SanitaryRegistration::STATUSES,
        ])->layout('layouts.app');
    }
}
