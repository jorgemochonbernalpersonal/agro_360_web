<?php

namespace App\Livewire\Viticulturist\BiodiversityRecords;

use App\Livewire\Viticulturist\AbstractCreate;
use App\Models\BiodiversityRecord;
use App\Models\Campaign;
use App\Models\Plot;

class Create extends AbstractCreate
{
    public string $plot_id      = '';
    public string $campaign_id  = '';
    public string $record_type  = 'cubierta_vegetal';
    public string $description  = '';
    public string $area_m2      = '';
    public string $species      = '';
    public string $record_date  = '';
    public string $notes        = '';

    public function mount(): void
    {
        $this->record_date = now()->format('Y-m-d');
    }

    protected function rules(): array
    {
        return [
            'plot_id'     => $this->plotOwnershipRule(),
            'campaign_id' => $this->campaignOwnershipRule(false),
            'record_type' => 'required|in:' . implode(',', array_keys(BiodiversityRecord::RECORD_TYPES)),
            'description' => 'nullable|string',
            'area_m2'     => 'nullable|numeric|min:0',
            'species'     => 'nullable|string|max:500',
            'record_date' => 'required|date',
            'notes'       => 'nullable|string',
        ];
    }

    protected function performCreate(): void
    {
        BiodiversityRecord::create([
            'viticulturist_id' => $this->viticulturistId(),
            'plot_id'          => $this->plot_id,
            'campaign_id'      => $this->campaign_id ?: null,
            'record_type'      => $this->record_type,
            'description'      => $this->description ?: null,
            'area_m2'          => $this->area_m2 ?: null,
            'species'          => $this->species ?: null,
            'record_date'      => $this->record_date,
            'notes'            => $this->notes ?: null,
        ]);
    }

    protected function successMessage(): string { return __('Registro de biodiversidad creado correctamente.'); }
    protected function indexRoute(): string      { return $this->rolePrefix() . '.biodiversity-records.index'; }

    protected function viewData(): array
    {
        $userId = $this->viticulturistId();

        return [
            'recordTypes' => BiodiversityRecord::RECORD_TYPES,
            'plots'       => Plot::where('viticulturist_id', $userId)->orderBy('name')->with('municipality:id,name')->get(['id', 'name', 'municipality_id']),
            'campaigns'   => Campaign::forViticulturist($userId)->orderByDesc('year')->get(['id', 'name', 'year']),
        ];
    }
}
