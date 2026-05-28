<?php

namespace App\Livewire\Winery\EcoCertifications;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\EcoCertification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Edit extends Component
{
    use WithToastNotifications;

    public EcoCertification $ecoCertification;

    public string $name               = '';
    public string $certification_type = '';
    public string $certifying_body    = '';
    public string $certificate_number = '';
    public string $valid_from         = '';
    public string $valid_until        = '';
    public string $status             = '';
    public string $notes              = '';

    public function mount(EcoCertification $ecoCertification): void
    {
        abort_if($ecoCertification->user_id !== Auth::id(), 403);
        $this->ecoCertification  = $ecoCertification;
        $this->name               = $ecoCertification->name;
        $this->certification_type = $ecoCertification->certification_type;
        $this->certifying_body    = $ecoCertification->certifying_body ?? '';
        $this->certificate_number = $ecoCertification->certificate_number ?? '';
        $this->valid_from         = $ecoCertification->valid_from?->toDateString() ?? '';
        $this->valid_until        = $ecoCertification->valid_until?->toDateString() ?? '';
        $this->status             = $ecoCertification->status;
        $this->notes              = $ecoCertification->notes ?? '';
    }

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

        $this->ecoCertification->update([
            'name'               => $this->name,
            'certification_type' => $this->certification_type,
            'certifying_body'    => $this->certifying_body ?: null,
            'certificate_number' => $this->certificate_number ?: null,
            'valid_from'         => $this->valid_from ?: null,
            'valid_until'        => $this->valid_until ?: null,
            'status'             => $this->status,
            'notes'              => $this->notes ?: null,
        ]);

        $this->toastSuccess(__('Certificación actualizada correctamente.'));
        $this->redirect(roleRoute('eco-certifications.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.winery.eco-certifications.edit', [
            'types'    => EcoCertification::certificationTypeOptions(),
            'statuses' => EcoCertification::statusOptions(),
        ])->layout('layouts.app');
    }
}
