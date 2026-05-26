<?php

namespace App\Livewire\Viticulturist\AdvisoryMemberships;

use App\Livewire\Viticulturist\AbstractCreate;
use App\Models\AdvisoryMembership;
use App\Models\Campaign;

class Create extends AbstractCreate
{
    public string $campaign_id    = '';
    public string $advisor_name   = '';
    public string $license_number = '';
    public string $specialty      = 'phytosanitary';
    public string $company_name   = '';
    public string $phone          = '';
    public string $email          = '';

    protected function rules(): array
    {
        return [
            'campaign_id'    => $this->campaignOwnershipRule(false),
            'advisor_name'   => 'required|string|max:255',
            'license_number' => 'required|string|max:50',
            'specialty'      => 'required|in:' . implode(',', array_keys(AdvisoryMembership::SPECIALTIES)),
            'company_name'   => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:100',
        ];
    }

    protected function performCreate(): void
    {
        AdvisoryMembership::create([
            'viticulturist_id' => $this->viticulturistId(),
            'campaign_id'      => $this->campaign_id ?: null,
            'advisor_name'     => $this->advisor_name,
            'license_number'   => $this->license_number,
            'specialty'        => $this->specialty,
            'company_name'     => $this->company_name ?: null,
            'phone'            => $this->phone ?: null,
            'email'            => $this->email ?: null,
            'active'           => true,
        ]);
    }

    protected function successMessage(): string
    {
        return __('Asesor registrado correctamente.');
    }

    protected function indexRoute(): string
    {
        return 'viticulturist.advisory-memberships.index';
    }

    protected function viewData(): array
    {
        return [
            'campaigns'   => Campaign::forViticulturist($this->viticulturistId())->orderByDesc('year')->get(),
            'specialties' => AdvisoryMembership::SPECIALTIES,
        ];
    }
}
