<?php

namespace App\Livewire\Winery\SanitaryRegistrations;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\SanitaryRegistration;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    use WithToastNotifications;

    public string $registration_number  = '';
    public string $registration_type    = 'rgseaa';
    public string $activity_description = '';
    public string $registration_date    = '';
    public string $renewal_date         = '';
    public string $issuing_authority    = '';
    public string $status               = 'active';
    public string $notes                = '';

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

        SanitaryRegistration::create([
            'user_id'              => Auth::id(),
            'registration_number'  => $this->registration_number,
            'registration_type'    => $this->registration_type,
            'activity_description' => $this->activity_description ?: null,
            'registration_date'    => $this->registration_date ?: null,
            'renewal_date'         => $this->renewal_date ?: null,
            'issuing_authority'    => $this->issuing_authority ?: null,
            'status'               => $this->status,
            'notes'                => $this->notes ?: null,
        ]);

        $this->toastSuccess("Registro sanitario «{$this->registration_number}» creado correctamente.");
        $this->redirect(route('winery.sanitary-registrations.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.winery.sanitary-registrations.create', [
            'types'    => SanitaryRegistration::REGISTRATION_TYPES,
            'statuses' => SanitaryRegistration::STATUSES,
        ])->layout('layouts.app');
    }
}
