<?php

namespace App\Livewire\Viticulturist\Certifications;

use App\Livewire\Viticulturist\AbstractCreate;
use App\Models\Certification;

class Create extends AbstractCreate
{
    public string $certification_type = 'ecologico';

    public string $certifying_body = '';

    public string $certificate_number = '';

    public string $issue_date = '';

    public string $expiry_date = '';

    public string $scope = '';

    public string $audit_date = '';

    public string $notes = '';

    public function mount(): void
    {
        $this->issue_date = now()->format('Y-m-d');
    }

    protected function rules(): array
    {
        return [
            'certification_type' => 'required|in:'.implode(',', array_keys(Certification::CERTIFICATION_TYPES)),
            'certifying_body' => 'required|string|max:255',
            'certificate_number' => 'nullable|string|max:100',
            'issue_date' => 'required|date',
            'expiry_date' => 'nullable|date|after:issue_date',
            'scope' => 'nullable|string|max:500',
            'audit_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ];
    }

    protected function performCreate(): void
    {
        Certification::create([
            'viticulturist_id' => $this->viticulturistId(),
            'certification_type' => $this->certification_type,
            'certifying_body' => $this->certifying_body,
            'certificate_number' => $this->certificate_number ?: null,
            'issue_date' => $this->issue_date,
            'expiry_date' => $this->expiry_date ?: null,
            'scope' => $this->scope ?: null,
            'audit_date' => $this->audit_date ?: null,
            'notes' => $this->notes ?: null,
            'active' => true,
        ]);
    }

    protected function successMessage(): string
    {
        return __('Certificación registrada correctamente.');
    }

    protected function indexRoute(): string
    {
        return 'viticulturist.certifications.index';
    }

    protected function viewData(): array
    {
        return [
            'certificationTypes' => Certification::certificationTypeOptions(),
        ];
    }
}
