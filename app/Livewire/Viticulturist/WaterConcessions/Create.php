<?php

namespace App\Livewire\Viticulturist\WaterConcessions;

use App\Livewire\Viticulturist\AbstractCreate;
use App\Models\Campaign;
use App\Models\WaterConcession;

class Create extends AbstractCreate
{
    public string $campaign_id       = '';
    public string $concession_type   = 'superficial';
    public string $concession_number = '';
    public string $water_body        = '';
    public string $authority         = '';
    public string $concession_date   = '';
    public string $expiry_date       = '';
    public string $max_volume_m3     = '';
    public string $used_volume_m3    = '';
    public string $surface_ha        = '';
    public string $notes             = '';

    public function mount(): void
    {
        $campaign = Campaign::getOrCreateActiveForYear($this->viticulturistId());
        $this->campaign_id = (string) ($campaign?->id ?? '');
    }

    protected function rules(): array
    {
        return [
            'campaign_id'       => 'nullable|exists:campaigns,id',
            'concession_type'   => 'required|in:' . implode(',', array_keys(WaterConcession::CONCESSION_TYPES)),
            'concession_number' => 'nullable|string|max:100',
            'water_body'        => 'required|string|max:255',
            'authority'         => 'required|string|max:255',
            'concession_date'   => 'nullable|date',
            'expiry_date'       => 'nullable|date|after_or_equal:concession_date',
            'max_volume_m3'     => 'required|numeric|min:0.001',
            'used_volume_m3'    => 'nullable|numeric|min:0',
            'surface_ha'        => 'nullable|numeric|min:0.0001',
            'notes'             => 'nullable|string',
        ];
    }

    protected function performCreate(): void
    {
        WaterConcession::create([
            'viticulturist_id'  => $this->viticulturistId(),
            'campaign_id'       => $this->campaign_id ?: null,
            'concession_type'   => $this->concession_type,
            'concession_number' => $this->concession_number ?: null,
            'water_body'        => $this->water_body,
            'authority'         => $this->authority,
            'concession_date'   => $this->concession_date ?: null,
            'expiry_date'       => $this->expiry_date ?: null,
            'max_volume_m3'     => $this->max_volume_m3,
            'used_volume_m3'    => $this->used_volume_m3 ?: null,
            'surface_ha'        => $this->surface_ha ?: null,
            'notes'             => $this->notes ?: null,
            'active'            => true,
        ]);
    }

    protected function successMessage(): string { return 'Concesión de agua registrada correctamente.'; }
    protected function indexRoute(): string      { return 'viticulturist.water-concessions.index'; }

    protected function viewData(): array
    {
        return [
            'campaigns'       => Campaign::forViticulturist($this->viticulturistId())->orderByDesc('year')->get(),
            'concessionTypes' => WaterConcession::CONCESSION_TYPES,
        ];
    }
}
