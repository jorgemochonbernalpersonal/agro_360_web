<?php

namespace App\Livewire\Viticulturist\CommercialAuthorizations;

use App\Livewire\Viticulturist\AbstractCreate;
use App\Models\CommercialAuthorization;
use App\Models\Exploitation;
use Illuminate\Validation\Rule;

class Create extends AbstractCreate
{
    public string $exploitation_id    = '';
    public string $authorization_type = 'do_registration';
    public string $authorization_code = '';
    public string $description        = '';
    public string $issuing_body       = '';
    public string $issue_date         = '';
    public string $expiry_date        = '';
    public string $document_file      = '';
    public string $notes              = '';

    public function mount(): void
    {
        $this->issue_date = now()->format('Y-m-d');
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

    protected function performCreate(): void
    {
        if ($this->exploitation_id) {
            Exploitation::where('viticulturist_id', $this->viticulturistId())
                ->findOrFail($this->exploitation_id);
        }

        CommercialAuthorization::create([
            'viticulturist_id'   => $this->viticulturistId(),
            'exploitation_id'    => $this->exploitation_id ?: null,
            'authorization_type' => $this->authorization_type,
            'authorization_code' => $this->authorization_code ?: null,
            'description'        => $this->description ?: null,
            'issuing_body'       => $this->issuing_body ?: null,
            'issue_date'         => $this->issue_date,
            'expiry_date'        => $this->expiry_date ?: null,
            'document_file'      => $this->document_file ?: null,
            'notes'              => $this->notes ?: null,
            'active'             => true,
        ]);
    }

    protected function successMessage(): string
    {
        return __('Autorización registrada correctamente.');
    }

    protected function indexRoute(): string
    {
        return 'viticulturist.commercial-authorizations.index';
    }

    protected function viewData(): array
    {
        return [
            'exploitations' => Exploitation::forViticulturist($this->viticulturistId())->active()->get(),
            'authTypes'     => CommercialAuthorization::authorizationTypeOptions(),
        ];
    }
}
