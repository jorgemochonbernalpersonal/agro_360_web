<?php

namespace App\Livewire\Viticulturist\AgriInsurance;

use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\AgriInsurance;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Edit extends Component
{
    use WithRoleAwareRedirect, WithToastNotifications;

    public AgriInsurance $insurance;

    public string $policy_number = '';
    public string $insurance_company = '';
    public string $coverage_type = 'comprehensive';
    public string $start_date = '';
    public string $end_date = '';
    public string $insured_amount = '';
    public string $premium = '';
    public string $subsidy_amount = '';
    public string $status = 'active';
    public string $agent_name = '';
    public string $agent_phone = '';
    public string $covered_plots = '';
    public string $notes = '';

    public function mount(AgriInsurance $insurance): void
    {
        if ($insurance->viticulturist_id !== Auth::id()) {
            abort(403);
        }

        $this->insurance        = $insurance;
        $this->policy_number    = $insurance->policy_number ?? '';
        $this->insurance_company = $insurance->insurance_company;
        $this->coverage_type    = $insurance->coverage_type;
        $this->start_date       = $insurance->start_date->format('Y-m-d');
        $this->end_date         = $insurance->end_date->format('Y-m-d');
        $this->insured_amount   = (string) ($insurance->insured_amount ?? '');
        $this->premium          = (string) ($insurance->premium ?? '');
        $this->subsidy_amount   = (string) ($insurance->subsidy_amount ?? '');
        $this->status           = $insurance->status;
        $this->agent_name       = $insurance->agent_name ?? '';
        $this->agent_phone      = $insurance->agent_phone ?? '';
        $this->covered_plots    = $insurance->covered_plots ?? '';
        $this->notes            = $insurance->notes ?? '';
    }

    protected function rules(): array
    {
        return [
            'policy_number'     => 'nullable|string|max:100',
            'insurance_company' => 'required|string|max:255',
            'coverage_type'     => 'required|in:' . implode(',', array_keys(AgriInsurance::COVERAGE_TYPES)),
            'start_date'        => 'required|date',
            'end_date'          => 'required|date|after:start_date',
            'insured_amount'    => 'nullable|numeric|min:0',
            'premium'           => 'nullable|numeric|min:0',
            'subsidy_amount'    => 'nullable|numeric|min:0',
            'status'            => 'required|in:' . implode(',', array_keys(AgriInsurance::STATUSES)),
            'agent_name'        => 'nullable|string|max:255',
            'agent_phone'       => 'nullable|string|max:20',
            'covered_plots'     => 'nullable|string',
            'notes'             => 'nullable|string',
        ];
    }

    public function update(): mixed
    {
        $this->validate();

        $this->insurance->update([
            'policy_number'     => $this->policy_number ?: null,
            'insurance_company' => $this->insurance_company,
            'coverage_type'     => $this->coverage_type,
            'start_date'        => $this->start_date,
            'end_date'          => $this->end_date,
            'insured_amount'    => $this->insured_amount ?: null,
            'premium'           => $this->premium ?: null,
            'subsidy_amount'    => $this->subsidy_amount ?: null,
            'status'            => $this->status,
            'agent_name'        => $this->agent_name ?: null,
            'agent_phone'       => $this->agent_phone ?: null,
            'covered_plots'     => $this->covered_plots ?: null,
            'notes'             => $this->notes ?: null,
        ]);

        $this->toastSuccess('Seguro actualizado correctamente.');

        return $this->viticulturistRoleRedirect('agri-insurance.index');
    }

    public function render()
    {
        return view('livewire.viticulturist.agri-insurance.edit', [
            'coverageTypes' => AgriInsurance::COVERAGE_TYPES,
            'statuses'      => AgriInsurance::STATUSES,
        ])->layout('layouts.app');
    }
}
