<?php

namespace App\Livewire\Viticulturist\PlotEnvironments;

use App\Livewire\Viticulturist\AbstractEdit;
use App\Models\Campaign;
use App\Models\Plot;
use App\Models\PlotEnvironment;
use App\Models\PlotPlanting;
use Illuminate\Validation\Rule;

class Edit extends AbstractEdit
{
    public PlotEnvironment $plotEnvironment;

    public string $campaign_id             = '';
    public string $plot_id                 = '';
    public string $plot_planting_id        = '';
    public bool   $water_intake_nearby     = false;
    public string $water_intake_distance_m = '';
    public bool   $protected_zone_total    = false;
    public bool   $protected_zone_partial  = false;
    public string $protection_zone_type    = '';
    public string $buffer_zone_m           = '';
    public string $slope_pct               = '';
    public bool   $erosion_risk            = false;
    public string $notes                   = '';

    public function mount(PlotEnvironment $plotEnvironment): void
    {
        $this->authorizeOwnership($plotEnvironment);

        $this->plotEnvironment         = $plotEnvironment;
        $this->campaign_id             = (string) $plotEnvironment->campaign_id;
        $this->plot_id                 = (string) $plotEnvironment->plot_id;
        $this->plot_planting_id        = (string) ($plotEnvironment->plot_planting_id ?? '');
        $this->water_intake_nearby     = $plotEnvironment->water_intake_nearby;
        $this->water_intake_distance_m = (string) ($plotEnvironment->water_intake_distance_m ?? '');
        $this->protected_zone_total    = $plotEnvironment->protected_zone_total;
        $this->protected_zone_partial  = $plotEnvironment->protected_zone_partial;
        $this->protection_zone_type    = (string) ($plotEnvironment->protection_zone_type ?? '');
        $this->buffer_zone_m           = (string) ($plotEnvironment->buffer_zone_m ?? '');
        $this->slope_pct               = (string) ($plotEnvironment->slope_pct ?? '');
        $this->erosion_risk            = $plotEnvironment->erosion_risk;
        $this->notes                   = (string) ($plotEnvironment->notes ?? '');
    }

    protected function rules(): array
    {
        return [
            'campaign_id' => [
                'required',
                'exists:campaigns,id',
                Rule::unique('plot_environments', 'campaign_id')
                    ->where('plot_id', $this->plot_id)
                    ->ignore($this->plotEnvironment->id),
            ],
            'plot_id'                 => 'required|exists:plots,id',
            'plot_planting_id'        => 'nullable|exists:plot_plantings,id',
            'water_intake_nearby'     => 'boolean',
            'water_intake_distance_m' => 'nullable|numeric|min:0',
            'protected_zone_total'    => 'boolean',
            'protected_zone_partial'  => 'boolean',
            'protection_zone_type'    => 'nullable|string|max:100',
            'buffer_zone_m'           => 'nullable|numeric|min:0',
            'slope_pct'               => 'nullable|numeric|min:0|max:100',
            'erosion_risk'            => 'boolean',
            'notes'                   => 'nullable|string',
        ];
    }

    protected function performUpdate(): void
    {
        $this->plotEnvironment->update([
            'campaign_id'             => $this->campaign_id,
            'plot_id'                 => $this->plot_id,
            'plot_planting_id'        => $this->plot_planting_id ?: null,
            'water_intake_nearby'     => $this->water_intake_nearby,
            'water_intake_distance_m' => $this->water_intake_distance_m ?: null,
            'protected_zone_total'    => $this->protected_zone_total,
            'protected_zone_partial'  => $this->protected_zone_partial,
            'protection_zone_type'    => $this->protection_zone_type ?: null,
            'buffer_zone_m'           => $this->buffer_zone_m ?: null,
            'slope_pct'               => $this->slope_pct ?: null,
            'erosion_risk'            => $this->erosion_risk,
            'notes'                   => $this->notes ?: null,
        ]);
    }

    protected function successMessage(): string
    {
        return 'Entorno de parcela actualizado.';
    }

    protected function indexRoute(): string
    {
        return 'viticulturist.plot-environments.index';
    }

    protected function viewData(): array
    {
        $id = $this->viticulturistId();

        return [
            'campaigns' => Campaign::forViticulturist($id)->orderByDesc('year')->get(),
            'plots'     => Plot::where('viticulturist_id', $id)->active()->get(),
            'plantings' => PlotPlanting::whereHas('plot', fn($q) => $q->where('viticulturist_id', $id))
                ->with(['plot', 'grapeVariety'])->active()->get(),
        ];
    }
}
