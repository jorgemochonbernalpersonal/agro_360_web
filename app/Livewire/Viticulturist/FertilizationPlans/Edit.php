<?php

namespace App\Livewire\Viticulturist\FertilizationPlans;

use App\Livewire\Viticulturist\AbstractEdit;
use App\Models\Campaign;
use App\Models\FertilizationPlan;

class Edit extends AbstractEdit
{
    public FertilizationPlan $plan;

    public string $campaign_id      = '';
    public string $plan_year        = '';
    public bool   $nitrate_zone     = false;
    public string $prepared_by      = '';
    public string $approval_date    = '';
    public string $total_surface_ha = '';
    public string $total_n_kg_ha    = '';
    public string $total_p_kg_ha    = '';
    public string $total_k_kg_ha    = '';
    public string $status           = 'draft';
    public string $notes            = '';
    public array  $lines            = [];

    public function mount(FertilizationPlan $plan): void
    {
        $this->authorizeOwnership($plan);
        $this->plan             = $plan;
        $this->campaign_id      = (string) $plan->campaign_id;
        $this->plan_year        = (string) $plan->plan_year;
        $this->nitrate_zone     = (bool) $plan->nitrate_zone;
        $this->prepared_by      = $plan->prepared_by ?? '';
        $this->approval_date    = $plan->approval_date?->format('Y-m-d') ?? '';
        $this->total_surface_ha = (string) ($plan->total_surface_ha ?? '');
        $this->total_n_kg_ha    = (string) ($plan->total_n_kg_ha ?? '');
        $this->total_p_kg_ha    = (string) ($plan->total_p_kg_ha ?? '');
        $this->total_k_kg_ha    = (string) ($plan->total_k_kg_ha ?? '');
        $this->status           = $plan->status;
        $this->notes            = $plan->notes ?? '';
        $this->lines            = $plan->plan_lines ?? [];
        if (empty($this->lines)) {
            $this->addLine();
        }
    }

    public function addLine(): void
    {
        $this->lines[] = [
            'plot_name'         => '',
            'surface_ha'        => '',
            'expected_yield_kg' => '',
            'n_kg_ha'           => '',
            'p_kg_ha'           => '',
            'k_kg_ha'           => '',
            'notes'             => '',
        ];
    }

    public function removeLine(int $index): void
    {
        array_splice($this->lines, $index, 1);
        $this->lines = array_values($this->lines);
    }

    protected function rules(): array
    {
        return [
            'campaign_id'               => 'required|exists:campaigns,id',
            'plan_year'                 => 'required|integer|min:2000|max:2100',
            'nitrate_zone'              => 'boolean',
            'prepared_by'               => 'nullable|string|max:255',
            'approval_date'             => 'nullable|date',
            'total_surface_ha'          => 'nullable|numeric|min:0',
            'total_n_kg_ha'             => 'nullable|numeric|min:0',
            'total_p_kg_ha'             => 'nullable|numeric|min:0',
            'total_k_kg_ha'             => 'nullable|numeric|min:0',
            'status'                    => 'required|in:draft,active,archived',
            'notes'                     => 'nullable|string',
            'lines'                     => 'nullable|array',
            'lines.*.plot_name'         => 'nullable|string|max:255',
            'lines.*.surface_ha'        => 'nullable|numeric|min:0',
            'lines.*.expected_yield_kg' => 'nullable|numeric|min:0',
            'lines.*.n_kg_ha'           => 'nullable|numeric|min:0',
            'lines.*.p_kg_ha'           => 'nullable|numeric|min:0',
            'lines.*.k_kg_ha'           => 'nullable|numeric|min:0',
            'lines.*.notes'             => 'nullable|string|max:500',
        ];
    }

    protected function performUpdate(): void
    {
        $filteredLines = array_filter($this->lines, fn($l) => !empty($l['plot_name']));

        $this->plan->update([
            'campaign_id'      => $this->campaign_id,
            'plan_year'        => $this->plan_year,
            'nitrate_zone'     => $this->nitrate_zone,
            'prepared_by'      => $this->prepared_by ?: null,
            'approval_date'    => $this->approval_date ?: null,
            'total_surface_ha' => $this->total_surface_ha ?: null,
            'total_n_kg_ha'    => $this->total_n_kg_ha ?: null,
            'total_p_kg_ha'    => $this->total_p_kg_ha ?: null,
            'total_k_kg_ha'    => $this->total_k_kg_ha ?: null,
            'plan_lines'       => array_values($filteredLines) ?: null,
            'status'           => $this->status,
            'notes'            => $this->notes ?: null,
        ]);
    }

    protected function successMessage(): string { return 'Plan de fertilización actualizado.'; }
    protected function indexRoute(): string      { return 'viticulturist.fertilization-plans.index'; }

    protected function viewData(): array
    {
        return [
            'campaigns' => Campaign::forViticulturist($this->viticulturistId())->orderByDesc('year')->get(),
            'statuses'  => FertilizationPlan::STATUSES,
        ];
    }
}
