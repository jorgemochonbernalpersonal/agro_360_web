<?php

namespace App\Livewire\Viticulturist\WaterConcessions;

use App\Livewire\Viticulturist\AbstractEdit;
use App\Models\Campaign;
use App\Models\WaterConcession;

class Edit extends AbstractEdit
{
    public WaterConcession $concession;

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

    public function mount(WaterConcession $concession): void
    {
        $this->authorizeOwnership($concession);

        $this->concession        = $concession;
        $this->campaign_id       = (string) ($concession->campaign_id ?? '');
        $this->concession_type   = $concession->concession_type;
        $this->concession_number = $concession->concession_number ?? '';
        $this->water_body        = $concession->water_body;
        $this->authority         = $concession->authority;
        $this->concession_date   = $concession->concession_date?->format('Y-m-d') ?? '';
        $this->expiry_date       = $concession->expiry_date?->format('Y-m-d') ?? '';
        $this->max_volume_m3     = (string) $concession->max_volume_m3;
        $this->used_volume_m3    = (string) ($concession->used_volume_m3 ?? '');
        $this->surface_ha        = (string) ($concession->surface_ha ?? '');
        $this->notes             = $concession->notes ?? '';
    }

    protected function rules(): array
    {
        return [
            'campaign_id'       => $this->campaignOwnershipRule(false),
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

    protected function performUpdate(): void
    {
        $this->concession->update([
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
        ]);
    }

    protected function successMessage(): string { return 'Concesión actualizada correctamente.'; }
    protected function indexRoute(): string      { return 'viticulturist.water-concessions.index'; }

    protected function viewData(): array
    {
        return [
            'campaigns'       => Campaign::forViticulturist($this->viticulturistId())->orderByDesc('year')->get(),
            'concessionTypes' => WaterConcession::CONCESSION_TYPES,
        ];
    }
}
