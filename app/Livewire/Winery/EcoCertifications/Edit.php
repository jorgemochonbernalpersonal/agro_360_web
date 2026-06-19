<?php

namespace App\Livewire\Winery\EcoCertifications;

use App\Livewire\Winery\AbstractEdit;
use App\Models\EcoCertification;

class Edit extends AbstractEdit
{
    public EcoCertification $ecoCertification;

    public string $name = '';

    public string $certification_type = '';

    public string $certifying_body = '';

    public string $certificate_number = '';

    public string $valid_from = '';

    public string $valid_until = '';

    public string $status = '';

    public string $notes = '';

    public function mount(EcoCertification $ecoCertification): void
    {
        $this->authorize('update', $ecoCertification);
        $this->ecoCertification = $ecoCertification;
        $this->name = $ecoCertification->name;
        $this->certification_type = $ecoCertification->certification_type;
        $this->certifying_body = $ecoCertification->certifying_body ?? '';
        $this->certificate_number = $ecoCertification->certificate_number ?? '';
        $this->valid_from = $ecoCertification->valid_from?->toDateString() ?? '';
        $this->valid_until = $ecoCertification->valid_until?->toDateString() ?? '';
        $this->status = $ecoCertification->status;
        $this->notes = $ecoCertification->notes ?? '';
    }

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

    protected function performUpdate(): void
    {
        $this->ecoCertification->update([
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
        return __('Certificación actualizada correctamente.');
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
