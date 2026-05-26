<?php

namespace App\Livewire\Viticulturist\ResidueAnalyses;

use App\Livewire\Viticulturist\AbstractCreate;
use App\Models\Campaign;
use App\Models\PlotPlanting;
use App\Models\ResidueAnalysis;

class Create extends AbstractCreate
{
    public string $campaign_id              = '';
    public string $plot_planting_id         = '';
    public string $analysis_date            = '';
    public string $sample_date              = '';
    public string $laboratory_name          = '';
    public string $laboratory_accreditation = '';
    public string $sample_type              = '';
    public bool   $overall_compliant        = true;
    public string $certificate_file         = '';
    public string $notes                    = '';

    public function mount(): void
    {
        $campaign = Campaign::getOrCreateActiveForYear($this->viticulturistId());
        $this->campaign_id   = (string) ($campaign?->id ?? '');
        $this->analysis_date = now()->format('Y-m-d');
    }

    protected function rules(): array
    {
        return [
            'campaign_id'              => $this->campaignOwnershipRule(),
            'plot_planting_id'         => $this->plotPlantingOwnershipRule(),
            'analysis_date'            => 'required|date',
            'sample_date'              => 'nullable|date',
            'laboratory_name'          => 'required|string|max:255',
            'laboratory_accreditation' => 'nullable|string|max:50',
            'sample_type'              => 'nullable|string|max:50',
            'overall_compliant'        => 'boolean',
            'certificate_file'         => 'nullable|string|max:500',
            'notes'                    => 'nullable|string',
        ];
    }

    protected function performCreate(): void
    {
        ResidueAnalysis::create([
            'campaign_id'              => $this->campaign_id,
            'plot_planting_id'         => $this->plot_planting_id ?: null,
            'viticulturist_id'         => $this->viticulturistId(),
            'analysis_date'            => $this->analysis_date,
            'sample_date'              => $this->sample_date ?: null,
            'laboratory_name'          => $this->laboratory_name,
            'laboratory_accreditation' => $this->laboratory_accreditation ?: null,
            'sample_type'              => $this->sample_type ?: null,
            'overall_compliant'        => $this->overall_compliant,
            'certificate_file'         => $this->certificate_file ?: null,
            'notes'                    => $this->notes ?: null,
            'active'                   => true,
        ]);
    }

    protected function successMessage(): string
    {
        return __('Análisis registrado correctamente.');
    }

    protected function indexRoute(): string
    {
        return 'viticulturist.residue-analyses.index';
    }

    protected function viewData(): array
    {
        $id = $this->viticulturistId();

        return [
            'campaigns' => Campaign::forViticulturist($id)->orderByDesc('year')->get(),
            'plantings' => PlotPlanting::whereHas('plot', fn($q) => $q->where('viticulturist_id', $id))
                ->with(['plot', 'grapeVariety'])->active()->get(),
        ];
    }
}
