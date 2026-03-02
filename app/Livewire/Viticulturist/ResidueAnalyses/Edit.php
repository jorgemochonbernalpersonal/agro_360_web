<?php

namespace App\Livewire\Viticulturist\ResidueAnalyses;

use App\Livewire\Viticulturist\AbstractEdit;
use App\Models\Campaign;
use App\Models\PlotPlanting;
use App\Models\ResidueAnalysis;

class Edit extends AbstractEdit
{
    public ResidueAnalysis $residueAnalysis;

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

    public function mount(ResidueAnalysis $residueAnalysis): void
    {
        $this->authorizeOwnership($residueAnalysis);

        $this->residueAnalysis          = $residueAnalysis;
        $this->campaign_id              = (string) $residueAnalysis->campaign_id;
        $this->plot_planting_id         = (string) ($residueAnalysis->plot_planting_id ?? '');
        $this->analysis_date            = $residueAnalysis->analysis_date->format('Y-m-d');
        $this->sample_date              = $residueAnalysis->sample_date?->format('Y-m-d') ?? '';
        $this->laboratory_name          = $residueAnalysis->laboratory_name;
        $this->laboratory_accreditation = (string) ($residueAnalysis->laboratory_accreditation ?? '');
        $this->sample_type              = (string) ($residueAnalysis->sample_type ?? '');
        $this->overall_compliant        = $residueAnalysis->overall_compliant;
        $this->certificate_file         = (string) ($residueAnalysis->certificate_file ?? '');
        $this->notes                    = (string) ($residueAnalysis->notes ?? '');
    }

    protected function rules(): array
    {
        return [
            'campaign_id'              => 'required|exists:campaigns,id',
            'plot_planting_id'         => 'nullable|exists:plot_plantings,id',
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

    protected function performUpdate(): void
    {
        $this->residueAnalysis->update([
            'campaign_id'              => $this->campaign_id,
            'plot_planting_id'         => $this->plot_planting_id ?: null,
            'analysis_date'            => $this->analysis_date,
            'sample_date'              => $this->sample_date ?: null,
            'laboratory_name'          => $this->laboratory_name,
            'laboratory_accreditation' => $this->laboratory_accreditation ?: null,
            'sample_type'              => $this->sample_type ?: null,
            'overall_compliant'        => $this->overall_compliant,
            'certificate_file'         => $this->certificate_file ?: null,
            'notes'                    => $this->notes ?: null,
        ]);
    }

    protected function successMessage(): string
    {
        return 'Análisis actualizado correctamente.';
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
                ->with(['plot', 'grape'])->active()->get(),
        ];
    }
}
