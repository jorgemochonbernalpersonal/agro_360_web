<?php

namespace App\Livewire\Viticulturist\BiodiversityRecords;

use App\Livewire\Viticulturist\AbstractEdit;
use App\Models\BiodiversityRecord;
use App\Models\Campaign;
use App\Models\Plot;

class Edit extends AbstractEdit
{
    public BiodiversityRecord $biodiversityRecord;

    public string $plot_id      = '';
    public string $campaign_id  = '';
    public string $record_type  = 'cubierta_vegetal';
    public string $description  = '';
    public string $area_m2      = '';
    public string $species      = '';
    public string $record_date  = '';
    public string $notes        = '';

    public function mount(BiodiversityRecord $biodiversityRecord): void
    {
        $this->authorizeOwnership($biodiversityRecord);
        $this->biodiversityRecord = $biodiversityRecord;
        $this->plot_id            = (string) $biodiversityRecord->plot_id;
        $this->campaign_id        = (string) ($biodiversityRecord->campaign_id ?? '');
        $this->record_type        = $biodiversityRecord->record_type;
        $this->description        = $biodiversityRecord->description ?? '';
        $this->area_m2            = (string) ($biodiversityRecord->area_m2 ?? '');
        $this->species            = $biodiversityRecord->species ?? '';
        $this->record_date        = $biodiversityRecord->record_date->format('Y-m-d');
        $this->notes              = $biodiversityRecord->notes ?? '';
    }

    protected function rules(): array
    {
        return [
            'plot_id'     => 'required|exists:plots,id',
            'campaign_id' => 'nullable|exists:campaigns,id',
            'record_type' => 'required|in:' . implode(',', array_keys(BiodiversityRecord::RECORD_TYPES)),
            'description' => 'nullable|string',
            'area_m2'     => 'nullable|numeric|min:0',
            'species'     => 'nullable|string|max:500',
            'record_date' => 'required|date',
            'notes'       => 'nullable|string',
        ];
    }

    protected function performUpdate(): void
    {
        $this->biodiversityRecord->update([
            'plot_id'     => $this->plot_id,
            'campaign_id' => $this->campaign_id ?: null,
            'record_type' => $this->record_type,
            'description' => $this->description ?: null,
            'area_m2'     => $this->area_m2 ?: null,
            'species'     => $this->species ?: null,
            'record_date' => $this->record_date,
            'notes'       => $this->notes ?: null,
        ]);
    }

    protected function successMessage(): string { return 'Registro actualizado correctamente.'; }
    protected function indexRoute(): string      { return $this->rolePrefix() . '.biodiversity-records.index'; }

    protected function viewData(): array
    {
        $userId = $this->viticulturistId();

        return [
            'recordTypes' => BiodiversityRecord::RECORD_TYPES,
            'plots'       => Plot::where('viticulturist_id', $userId)->orderBy('name')->get(['id', 'name', 'municipality_id']),
            'campaigns'   => Campaign::forViticulturist($userId)->orderByDesc('year')->get(['id', 'name', 'year']),
        ];
    }
}
