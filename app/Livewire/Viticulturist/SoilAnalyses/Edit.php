<?php

namespace App\Livewire\Viticulturist\SoilAnalyses;

use App\Livewire\Viticulturist\AbstractEdit;
use App\Models\Campaign;
use App\Models\Plot;
use App\Models\SoilAnalysis;

class Edit extends AbstractEdit
{
    public SoilAnalysis $soilAnalysis;

    public string $plot_id                 = '';
    public string $campaign_id             = '';
    public string $analysis_date           = '';
    public string $laboratory              = '';
    public string $sample_depth_cm         = '';
    public string $ph                      = '';
    public string $organic_matter          = '';
    public string $nitrogen_total          = '';
    public string $phosphorus              = '';
    public string $potassium               = '';
    public string $calcium                 = '';
    public string $magnesium               = '';
    public string $texture_class           = '';
    public string $electrical_conductivity = '';
    public string $limestone               = '';
    public string $notes                   = '';

    public function mount(SoilAnalysis $soilAnalysis): void
    {
        $this->authorizeOwnership($soilAnalysis);
        $this->soilAnalysis            = $soilAnalysis;
        $this->plot_id                 = (string) $soilAnalysis->plot_id;
        $this->campaign_id             = (string) ($soilAnalysis->campaign_id ?? '');
        $this->analysis_date           = $soilAnalysis->analysis_date->format('Y-m-d');
        $this->laboratory              = $soilAnalysis->laboratory ?? '';
        $this->sample_depth_cm         = (string) ($soilAnalysis->sample_depth_cm ?? '');
        $this->ph                      = (string) ($soilAnalysis->ph ?? '');
        $this->organic_matter          = (string) ($soilAnalysis->organic_matter ?? '');
        $this->nitrogen_total          = (string) ($soilAnalysis->nitrogen_total ?? '');
        $this->phosphorus              = (string) ($soilAnalysis->phosphorus ?? '');
        $this->potassium               = (string) ($soilAnalysis->potassium ?? '');
        $this->calcium                 = (string) ($soilAnalysis->calcium ?? '');
        $this->magnesium               = (string) ($soilAnalysis->magnesium ?? '');
        $this->texture_class           = $soilAnalysis->texture_class ?? '';
        $this->electrical_conductivity = (string) ($soilAnalysis->electrical_conductivity ?? '');
        $this->limestone               = (string) ($soilAnalysis->limestone ?? '');
        $this->notes                   = $soilAnalysis->notes ?? '';
    }

    protected function rules(): array
    {
        return [
            'plot_id'                 => $this->plotOwnershipRule(),
            'campaign_id'             => 'nullable',
            'analysis_date'           => 'required|date',
            'laboratory'              => 'nullable|string|max:255',
            'sample_depth_cm'         => 'nullable|integer|min:0|max:300',
            'ph'                      => 'nullable|numeric|min:0|max:14',
            'organic_matter'          => 'nullable|numeric|min:0',
            'nitrogen_total'          => 'nullable|numeric|min:0',
            'phosphorus'              => 'nullable|numeric|min:0',
            'potassium'               => 'nullable|numeric|min:0',
            'calcium'                 => 'nullable|numeric|min:0',
            'magnesium'               => 'nullable|numeric|min:0',
            'texture_class'           => 'nullable|in:' . implode(',', array_keys(SoilAnalysis::TEXTURE_CLASSES)),
            'electrical_conductivity' => 'nullable|numeric|min:0',
            'limestone'               => 'nullable|numeric|min:0|max:100',
            'notes'                   => 'nullable|string',
        ];
    }

    protected function performUpdate(): void
    {
        $this->soilAnalysis->update([
            'plot_id'                 => $this->plot_id,
            'campaign_id'             => $this->campaign_id ?: null,
            'analysis_date'           => $this->analysis_date,
            'laboratory'              => $this->laboratory ?: null,
            'sample_depth_cm'         => $this->sample_depth_cm ?: null,
            'ph'                      => $this->ph ?: null,
            'organic_matter'          => $this->organic_matter ?: null,
            'nitrogen_total'          => $this->nitrogen_total ?: null,
            'phosphorus'              => $this->phosphorus ?: null,
            'potassium'               => $this->potassium ?: null,
            'calcium'                 => $this->calcium ?: null,
            'magnesium'               => $this->magnesium ?: null,
            'texture_class'           => $this->texture_class ?: null,
            'electrical_conductivity' => $this->electrical_conductivity ?: null,
            'limestone'               => $this->limestone ?: null,
            'notes'                   => $this->notes ?: null,
        ]);
    }

    protected function successMessage(): string { return __('Análisis de suelo actualizado correctamente.'); }
    protected function indexRoute(): string      { return $this->rolePrefix() . '.soil-analyses.index'; }

    protected function viewData(): array
    {
        $userId = $this->viticulturistId();

        return [
            'textureClasses' => SoilAnalysis::textureClassOptions(),
            'plots'          => Plot::where('viticulturist_id', $userId)->orderBy('name')->get(),
            'campaigns'      => Campaign::forViticulturist($userId)->orderBy('year', 'desc')->get(),
        ];
    }
}
