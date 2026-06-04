<?php

namespace App\Livewire\Winery\Harvest\Forecasts;

use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\EstimatedYield;
use App\Models\WineryYieldForecast;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Edit extends Component
{
    use WithRoleAwareRedirect, WithToastNotifications;

    public WineryYieldForecast $forecast;

    public string $estimated_kg = '';

    public string $estimation_date = '';

    public string $status = 'draft';

    public string $notes = '';

    // Contexto (read-only)
    public string $viticulturistName = '';

    public string $plantingLabel = '';

    public string $campaignLabel = '';

    public int $vintageYear = 0;

    // PAC
    public ?float $pacLimit = null;

    public ?float $pacLimitRaw = null;

    public ?int $ageFactor = null;

    // Aforo del viticultor para referencia
    public ?float $viticEstimateKg = null;

    public ?string $viticEstimateStatus = null;

    public function mount(WineryYieldForecast $forecast): void
    {
        abort_unless($forecast->winery_id === Auth::id(), 403);

        $this->forecast = $forecast->load([
            'viticulturist:id,name',
            'plotPlanting.grapeVariety',
            'plotPlanting.plot',
            'campaign',
        ]);

        $planting = $forecast->plotPlanting;

        $this->viticulturistName = $forecast->viticulturist?->name ?? '—';
        $this->plantingLabel = ($planting?->grapeVariety?->name ?? $planting?->name ?? '—')
            .($planting?->plot ? ' — '.$planting->plot->name : '');
        $this->campaignLabel = $forecast->campaign?->year ?? '—';
        $this->vintageYear = $forecast->vintage_year;

        $this->estimated_kg = (string) $forecast->estimated_kg;
        $this->estimation_date = $forecast->estimation_date?->format('Y-m-d') ?? '';
        $this->status = $forecast->status;
        $this->notes = $forecast->notes ?? '';

        // PAC reference
        $this->pacLimitRaw = $planting?->harvest_limit_kg ? (float) $planting->harvest_limit_kg : null;
        $this->pacLimit = $planting?->effectiveHarvestLimitKg($forecast->vintage_year);
        if ($this->pacLimitRaw && $this->pacLimit !== null) {
            $this->ageFactor = $this->pacLimitRaw > 0
                ? (int) round($this->pacLimit / $this->pacLimitRaw * 100)
                : 100;
        }

        // Aforo del viticultor para referencia
        if ($planting) {
            $viticEstimate = EstimatedYield::where('plot_planting_id', $planting->id)
                ->whereHas('campaign', fn ($q) => $q->where('year', $forecast->vintage_year))
                ->orderByRaw("CASE status WHEN 'confirmed' THEN 0 ELSE 1 END")
                ->first();

            if ($viticEstimate) {
                $this->viticEstimateKg = (float) $viticEstimate->estimated_total_yield;
                $this->viticEstimateStatus = $viticEstimate->status;
            }
        }
    }

    public function save(): mixed
    {
        $this->validate();

        $this->forecast->update([
            'estimated_kg' => (float) $this->estimated_kg,
            'estimation_date' => $this->estimation_date,
            'status' => $this->status,
            'notes' => $this->notes ?: null,
        ]);

        $this->toastSuccess(__('Previsión actualizada correctamente.'));

        return $this->roleRedirect('harvest-forecasts.index');
    }

    public function render()
    {
        return view('livewire.winery.harvest.forecasts.edit')
            ->layout('layouts.app');
    }

    protected function rules(): array
    {
        return [
            'estimated_kg' => ['required', 'numeric', 'min:1'],
            'estimation_date' => ['required', 'date'],
            'status' => ['required', 'in:draft,confirmed'],
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function messages(): array
    {
        return [
            'estimated_kg.required' => __('Introduce los kg estimados.'),
            'estimated_kg.min' => __('Los kg estimados deben ser mayor que 0.'),
        ];
    }
}
