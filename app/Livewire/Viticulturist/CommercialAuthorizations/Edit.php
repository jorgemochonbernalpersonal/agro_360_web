<?php

namespace App\Livewire\Viticulturist\CommercialAuthorizations;

use App\Livewire\Viticulturist\AbstractEdit;
use App\Models\CommercialAuthorization;
use App\Models\Exploitation;
use Illuminate\Validation\Rule;

class Edit extends AbstractEdit
{
    public CommercialAuthorization $commercialAuthorization;

    public string $exploitation_id    = '';
    public string $authorization_type = 'do_registration';
    public string $authorization_code = '';
    public string $description        = '';
    public string $issuing_body       = '';
    public string $issue_date         = '';
    public string $expiry_date        = '';
    public string $document_file      = '';
    public string $notes              = '';

    public function mount(CommercialAuthorization $commercialAuthorization): void
    {
        $this->authorizeOwnership($commercialAuthorization);

        $this->commercialAuthorization = $commercialAuthorization;
        $this->exploitation_id    = (string) ($commercialAuthorization->exploitation_id ?? '');
        $this->authorization_type = $commercialAuthorization->authorization_type;
        $this->authorization_code = (string) ($commercialAuthorization->authorization_code ?? '');
        $this->description        = (string) ($commercialAuthorization->description ?? '');
        $this->issuing_body       = (string) ($commercialAuthorization->issuing_body ?? '');
        $this->issue_date         = $commercialAuthorization->issue_date->format('Y-m-d');
        $this->expiry_date        = $commercialAuthorization->expiry_date?->format('Y-m-d') ?? '';
        $this->document_file      = (string) ($commercialAuthorization->document_file ?? '');
        $this->notes              = (string) ($commercialAuthorization->notes ?? '');
    }

    protected function rules(): array
    {
        return [
            'authorization_type' => 'required|in:' . implode(',', array_keys(CommercialAuthorization::AUTHORIZATION_TYPES)),
            'issue_date'         => 'required|date',
            'expiry_date'        => 'nullable|date|after_or_equal:issue_date',
            'exploitation_id'    => 'nullable|exists:exploitations,id',
            'authorization_code' => 'nullable|string|max:100',
            'description'        => 'nullable|string|max:255',
            'issuing_body'       => 'nullable|string|max:255',
            'document_file'      => 'nullable|string|max:500',
            'notes'              => 'nullable|string',
        ];
    }

    protected function performUpdate(): void
    {
        if ($this->exploitation_id) {
            Exploitation::where('viticulturist_id', $this->viticulturistId())
                ->findOrFail($this->exploitation_id);
        }

        $this->commercialAuthorization->update([
            'exploitation_id'    => $this->exploitation_id ?: null,
            'authorization_type' => $this->authorization_type,
            'authorization_code' => $this->authorization_code ?: null,
            'description'        => $this->description ?: null,
            'issuing_body'       => $this->issuing_body ?: null,
            'issue_date'         => $this->issue_date,
            'expiry_date'        => $this->expiry_date ?: null,
            'document_file'      => $this->document_file ?: null,
            'notes'              => $this->notes ?: null,
        ]);
    }

    protected function successMessage(): string
    {
        return __('Autorización actualizada.');
    }

    protected function indexRoute(): string
    {
        return 'viticulturist.commercial-authorizations.index';
    }

    protected function viewData(): array
    {
        return [
            'exploitations' => Exploitation::forViticulturist($this->viticulturistId())->active()->get(),
            'authTypes'     => CommercialAuthorization::AUTHORIZATION_TYPES,
        ];
    }
}
