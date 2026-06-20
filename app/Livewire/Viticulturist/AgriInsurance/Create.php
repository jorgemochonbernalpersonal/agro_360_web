<?php

namespace App\Livewire\Viticulturist\AgriInsurance;

use App\Livewire\Viticulturist\AbstractCreate;
use App\Models\AgriInsurance;

class Create extends AbstractCreate
{
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

    public function mount(): void
    {
        $this->start_date = now()->format('Y-m-d');
        $this->end_date = now()->addYear()->format('Y-m-d');
    }

    protected function performCreate(): void
    {
        AgriInsurance::create([
            'viticulturist_id' => $this->ownerId(),
            'policy_number' => $this->policy_number ?: null,
            'insurance_company' => $this->insurance_company,
            'coverage_type' => $this->coverage_type,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'insured_amount' => $this->insured_amount ?: null,
            'premium' => $this->premium ?: null,
            'subsidy_amount' => $this->subsidy_amount ?: null,
            'status' => $this->status,
            'agent_name' => $this->agent_name ?: null,
            'agent_phone' => $this->agent_phone ?: null,
            'covered_plots' => $this->covered_plots ?: null,
            'notes' => $this->notes ?: null,
        ]);
    }

    protected function successMessage(): string
    {
        return __('Seguro agrario registrado correctamente.');
    }

    protected function indexRoute(): string
    {
        return 'agri-insurance.index';
    }

    protected function viewData(): array
    {
        return [
            'coverageTypes' => AgriInsurance::coverageTypeOptions(),
            'statuses' => AgriInsurance::statusOptions(),
        ];
    }

    protected function rules(): array
    {
        return [
            'policy_number' => 'nullable|string|max:100',
            'insurance_company' => 'required|string|max:255',
            'coverage_type' => 'required|in:'.implode(',', array_keys(AgriInsurance::COVERAGE_TYPES)),
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'insured_amount' => 'nullable|numeric|min:0',
            'premium' => 'nullable|numeric|min:0',
            'subsidy_amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:'.implode(',', array_keys(AgriInsurance::STATUSES)),
            'agent_name' => 'nullable|string|max:255',
            'agent_phone' => 'nullable|string|max:20',
            'covered_plots' => 'nullable|string',
            'notes' => 'nullable|string',
        ];
    }
}
