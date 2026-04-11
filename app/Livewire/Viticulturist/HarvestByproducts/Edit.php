<?php

namespace App\Livewire\Viticulturist\HarvestByproducts;

use App\Livewire\Viticulturist\AbstractEdit;
use App\Models\Campaign;
use App\Models\HarvestByproduct;

class Edit extends AbstractEdit
{
    public HarvestByproduct $byproduct;

    public string $campaign_id        = '';
    public string $date               = '';
    public string $byproduct_type     = 'pomace';
    public string $quantity_kg        = '';
    public string $destination_type   = 'cooperative';
    public string $destination_name   = '';
    public string $document_reference = '';
    public string $notes              = '';

    public function mount(HarvestByproduct $byproduct): void
    {
        $this->authorizeOwnership($byproduct);
        $this->byproduct          = $byproduct;
        $this->campaign_id        = (string) $byproduct->campaign_id;
        $this->date               = $byproduct->date->format('Y-m-d');
        $this->byproduct_type     = $byproduct->byproduct_type;
        $this->quantity_kg        = (string) $byproduct->quantity_kg;
        $this->destination_type   = $byproduct->destination_type;
        $this->destination_name   = $byproduct->destination_name;
        $this->document_reference = $byproduct->document_reference ?? '';
        $this->notes              = $byproduct->notes ?? '';
    }

    protected function rules(): array
    {
        return [
            'campaign_id'        => 'required|exists:campaigns,id',
            'date'               => 'required|date',
            'byproduct_type'     => 'required|in:' . implode(',', array_keys(HarvestByproduct::BYPRODUCT_TYPES)),
            'quantity_kg'        => 'required|numeric|min:0.001',
            'destination_type'   => 'required|in:' . implode(',', array_keys(HarvestByproduct::DESTINATION_TYPES)),
            'destination_name'   => 'required|string|max:255',
            'document_reference' => 'nullable|string|max:100',
            'notes'              => 'nullable|string',
        ];
    }

    protected function performUpdate(): void
    {
        $this->byproduct->update([
            'campaign_id'        => $this->campaign_id,
            'date'               => $this->date,
            'byproduct_type'     => $this->byproduct_type,
            'quantity_kg'        => $this->quantity_kg,
            'destination_type'   => $this->destination_type,
            'destination_name'   => $this->destination_name,
            'document_reference' => $this->document_reference ?: null,
            'notes'              => $this->notes ?: null,
        ]);
    }

    protected function successMessage(): string { return 'Registro actualizado correctamente.'; }
    protected function indexRoute(): string      { return 'viticulturist.harvest-byproducts.index'; }

    protected function viewData(): array
    {
        return [
            'campaigns'        => Campaign::forViticulturist($this->viticulturistId())->orderByDesc('year')->get(),
            'byproductTypes'   => HarvestByproduct::BYPRODUCT_TYPES,
            'destinationTypes' => HarvestByproduct::DESTINATION_TYPES,
        ];
    }
}
