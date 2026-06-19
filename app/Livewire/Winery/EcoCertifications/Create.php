<?php

namespace App\Livewire\Winery\EcoCertifications;

use App\Livewire\Winery\AbstractCreate;
use App\Models\EcoCertification;

class Create extends AbstractCreate
{
    public string $name = '';

    public string $certification_type = 'organic';

    public string $certifying_body = '';

    public string $certificate_number = '';

    public string $valid_from = '';

    public string $valid_until = '';

    public string $status = 'active';

    public string $notes = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:200'],
            'certification_type' => ['required', 'in:'.implode(',', array_keys(EcoCertification::CERTIFICATION_TYPES))],
            'certifying_body' => ['nullable', 'string', 'max:200'],
            'certificate_number' => ['nullable', 'string', 'max:100'],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date'],
            'status' => ['required', 'in:'.implode(',', array_keys(EcoCertification::STATUSES))],
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function performCreate(): void
    {
        EcoCertification::create([
            'user_id' => $this->ownerId(),
            'name' => $this->name,
            'certification_type' => $this->certification_type,
            'certifying_body' => $this->certifying_body ?: null,
            'certificate_number' => $this->certificate_number ?: null,
            'valid_from' => $this->valid_from ?: null,
            'valid_until' => $this->valid_until ?: null,
            'status' => $this->status,
            'notes' => $this->notes ?: null,
        ]);
    }

    protected function successMessage(): string
    {
        return __('Certificación «:name» creada correctamente.', ['name' => $this->name]);
    }

    protected function indexRoute(): string
    {
        return 'winery.eco-certifications.index';
    }

    protected function viewData(): array
    {
        return [
            'types' => EcoCertification::certificationTypeOptions(),
            'statuses' => EcoCertification::statusOptions(),
        ];
    }
}
