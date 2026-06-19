<?php

namespace App\Livewire\Winery\SanitaryRegistrations;

use App\Livewire\Winery\AbstractCreate;
use App\Models\SanitaryRegistration;

class Create extends AbstractCreate
{
    public string $registration_number = '';

    public string $registration_type = 'rgseaa';

    public string $activity_description = '';

    public string $registration_date = '';

    public string $renewal_date = '';

    public string $issuing_authority = '';

    public string $status = 'active';

    public string $notes = '';

    protected function rules(): array
    {
        return [
            'registration_number' => ['required', 'string', 'max:100'],
            'registration_type' => ['required', 'in:'.implode(',', array_keys(SanitaryRegistration::REGISTRATION_TYPES))],
            'activity_description' => ['nullable', 'string', 'max:300'],
            'registration_date' => ['nullable', 'date'],
            'renewal_date' => ['nullable', 'date'],
            'issuing_authority' => ['nullable', 'string', 'max:200'],
            'status' => ['required', 'in:'.implode(',', array_keys(SanitaryRegistration::STATUSES))],
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function performCreate(): void
    {
        SanitaryRegistration::create([
            'user_id' => $this->ownerId(),
            'registration_number' => $this->registration_number,
            'registration_type' => $this->registration_type,
            'activity_description' => $this->activity_description ?: null,
            'registration_date' => $this->registration_date ?: null,
            'renewal_date' => $this->renewal_date ?: null,
            'issuing_authority' => $this->issuing_authority ?: null,
            'status' => $this->status,
            'notes' => $this->notes ?: null,
        ]);
    }

    protected function successMessage(): string
    {
        return __('Registro sanitario «:number» creado correctamente.', ['number' => $this->registration_number]);
    }

    protected function indexRoute(): string
    {
        return 'winery.sanitary-registrations.index';
    }

    protected function viewData(): array
    {
        return [
            'types' => SanitaryRegistration::registrationTypeOptions(),
            'statuses' => SanitaryRegistration::statusOptions(),
        ];
    }
}
