<?php

namespace App\Livewire\Winery\EcoCertifications;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\EcoCertification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    use WithToastNotifications;

    public string $name               = '';
    public string $certification_type = 'organic';
    public string $certifying_body    = '';
    public string $certificate_number = '';
    public string $valid_from         = '';
    public string $valid_until        = '';
    public string $status             = 'active';
    public string $notes              = '';

    protected function rules(): array
    {
        return [
            'name'               => ['required', 'string', 'max:200'],
            'certification_type' => ['required', 'in:' . implode(',', array_keys(EcoCertification::CERTIFICATION_TYPES))],
            'certifying_body'    => ['nullable', 'string', 'max:200'],
            'certificate_number' => ['nullable', 'string', 'max:100'],
            'valid_from'         => ['nullable', 'date'],
            'valid_until'        => ['nullable', 'date'],
            'status'             => ['required', 'in:' . implode(',', array_keys(EcoCertification::STATUSES))],
            'notes'              => ['nullable', 'string'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        EcoCertification::create([
            'user_id'            => Auth::id(),
            'name'               => $this->name,
            'certification_type' => $this->certification_type,
            'certifying_body'    => $this->certifying_body ?: null,
            'certificate_number' => $this->certificate_number ?: null,
            'valid_from'         => $this->valid_from ?: null,
            'valid_until'        => $this->valid_until ?: null,
            'status'             => $this->status,
            'notes'              => $this->notes ?: null,
        ]);

        $this->toastSuccess("Certificación «{$this->name}» creada correctamente.");
        $this->redirect(roleRoute('eco-certifications.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.winery.eco-certifications.create', [
            'types'    => EcoCertification::certificationTypeOptions(),
            'statuses' => EcoCertification::statusOptions(),
        ])->layout('layouts.app');
    }
}
