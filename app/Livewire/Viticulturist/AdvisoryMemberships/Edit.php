<?php

namespace App\Livewire\Viticulturist\AdvisoryMemberships;

use App\Livewire\Viticulturist\AbstractEdit;
use App\Models\AdvisoryMembership;
use App\Models\Campaign;

class Edit extends AbstractEdit
{
    public AdvisoryMembership $advisoryMembership;

    public string $campaign_id = '';

    public string $advisor_name = '';

    public string $license_number = '';

    public string $specialty = 'phytosanitary';

    public string $company_name = '';

    public string $phone = '';

    public string $email = '';

    public function mount(AdvisoryMembership $advisoryMembership): void
    {
        $this->authorizeOwnership($advisoryMembership);

        $this->advisoryMembership = $advisoryMembership;
        $this->campaign_id = (string) ($advisoryMembership->campaign_id ?? '');
        $this->advisor_name = $advisoryMembership->advisor_name;
        $this->license_number = $advisoryMembership->license_number;
        $this->specialty = $advisoryMembership->specialty;
        $this->company_name = (string) ($advisoryMembership->company_name ?? '');
        $this->phone = (string) ($advisoryMembership->phone ?? '');
        $this->email = (string) ($advisoryMembership->email ?? '');
    }

    protected function rules(): array
    {
        return [
            'campaign_id' => $this->campaignOwnershipRule(false),
            'advisor_name' => 'required|string|max:255',
            'license_number' => 'required|string|max:50',
            'specialty' => 'required|in:'.implode(',', array_keys(AdvisoryMembership::SPECIALTIES)),
            'company_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
        ];
    }

    protected function performUpdate(): void
    {
        $this->advisoryMembership->update([
            'campaign_id' => $this->campaign_id ?: null,
            'advisor_name' => $this->advisor_name,
            'license_number' => $this->license_number,
            'specialty' => $this->specialty,
            'company_name' => $this->company_name ?: null,
            'phone' => $this->phone ?: null,
            'email' => $this->email ?: null,
        ]);
    }

    protected function successMessage(): string
    {
        return __('Asesor actualizado correctamente.');
    }

    protected function indexRoute(): string
    {
        return 'viticulturist.advisory-memberships.index';
    }

    protected function viewData(): array
    {
        return [
            'campaigns' => Campaign::forViticulturist($this->viticulturistId())->orderByDesc('year')->get(),
            'specialties' => AdvisoryMembership::SPECIALTIES,
        ];
    }
}
