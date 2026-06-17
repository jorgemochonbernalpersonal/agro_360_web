<?php

namespace App\Livewire\Viticulturist\FertilizationPlans;

use App\Livewire\Viticulturist\AbstractCreate;
use App\Models\Campaign;
use App\Models\FertilizationPlan;

class Create extends AbstractCreate
{
    public string $campaign_id = '';

    public string $plan_year = '';

    public bool $nitrate_zone = false;

    public string $prepared_by = '';

    public string $approval_date = '';

    public string $total_surface_ha = '';

    public string $total_n_kg_ha = '';

    public string $total_p_kg_ha = '';

    public string $total_k_kg_ha = '';

    public string $notes = '';

    public array $lines = [];

    public function mount(): void
    {
        $campaign = Campaign::getOrCreateActiveForYear($this->viticulturistId());
        $this->campaign_id = (string) ($campaign->id ?? '');
        $this->plan_year = (string) now()->year;
        $this->addLine();
    }

    public function addLine(): void
    {
        $this->lines[] = [
            'plot_name' => '',
            'surface_ha' => '',
            'expected_yield_kg' => '',
            'n_kg_ha' => '',
            'p_kg_ha' => '',
            'k_kg_ha' => '',
            'notes' => '',
        ];
    }

    public function removeLine(int $index): void
    {
        array_splice($this->lines, $index, 1);
        $this->lines = array_values($this->lines);
        $this->recalculate();
    }

    public function updatedLines(): void
    {
        $this->recalculate();
    }

    protected function rules(): array
    {
        return [
            'campaign_id' => $this->campaignOwnershipRule(),
            'plan_year' => 'required|integer|min:2000|max:2100',
            'nitrate_zone' => 'boolean',
            'prepared_by' => 'nullable|string|max:255',
            'approval_date' => 'nullable|date',
            'total_surface_ha' => 'nullable|numeric|min:0',
            'total_n_kg_ha' => 'nullable|numeric|min:0',
            'total_p_kg_ha' => 'nullable|numeric|min:0',
            'total_k_kg_ha' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'lines' => 'nullable|array',
            'lines.*.plot_name' => 'nullable|string|max:255',
            'lines.*.surface_ha' => 'nullable|numeric|min:0',
            'lines.*.expected_yield_kg' => 'nullable|numeric|min:0',
            'lines.*.n_kg_ha' => 'nullable|numeric|min:0',
            'lines.*.p_kg_ha' => 'nullable|numeric|min:0',
            'lines.*.k_kg_ha' => 'nullable|numeric|min:0',
            'lines.*.notes' => 'nullable|string|max:500',
        ];
    }

    protected function performCreate(): void
    {
        $filteredLines = array_filter($this->lines, fn ($l) => ! empty($l['plot_name']));

        FertilizationPlan::create([
            'viticulturist_id' => $this->viticulturistId(),
            'campaign_id' => $this->campaign_id,
            'plan_year' => $this->plan_year,
            'nitrate_zone' => $this->nitrate_zone,
            'prepared_by' => $this->prepared_by ?: null,
            'approval_date' => $this->approval_date ?: null,
            'total_surface_ha' => $this->total_surface_ha ?: null,
            'total_n_kg_ha' => $this->total_n_kg_ha ?: null,
            'total_p_kg_ha' => $this->total_p_kg_ha ?: null,
            'total_k_kg_ha' => $this->total_k_kg_ha ?: null,
            'plan_lines' => array_values($filteredLines) ?: null,
            'status' => 'draft',
            'notes' => $this->notes ?: null,
            'active' => true,
        ]);
    }

    protected function successMessage(): string
    {
        return __('Plan de fertilización creado correctamente.');
    }

    protected function indexRoute(): string
    {
        return 'viticulturist.fertilization-plans.index';
    }

    protected function viewData(): array
    {
        return [
            'campaigns' => Campaign::forViticulturist($this->viticulturistId())->orderByDesc('year')->get(),
            'statuses' => FertilizationPlan::statusOptions(),
        ];
    }

    private function recalculate(): void
    {
        $surface = 0;
        foreach ($this->lines as $line) {
            $surface += (float) ($line['surface_ha'] ?? 0);
        }
        $this->total_surface_ha = $surface > 0 ? (string) round($surface, 4) : '';
    }
}
