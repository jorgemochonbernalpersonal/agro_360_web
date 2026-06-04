<?php

namespace App\Livewire\Viticulturist\HarvestByproducts;

use App\Livewire\Viticulturist\AbstractCreate;
use App\Models\Campaign;
use App\Models\HarvestByproduct;

class Create extends AbstractCreate
{
    public string $campaign_id = '';

    public string $date = '';

    public string $byproduct_type = 'pomace';

    public string $quantity_kg = '';

    public string $destination_type = 'cooperative';

    public string $destination_name = '';

    public string $document_reference = '';

    public string $notes = '';

    public function mount(): void
    {
        $campaign = Campaign::getOrCreateActiveForYear($this->viticulturistId());
        $this->campaign_id = (string) ($campaign?->id ?? '');
        $this->date = now()->format('Y-m-d');
    }

    protected function rules(): array
    {
        return [
            'campaign_id' => $this->campaignOwnershipRule(),
            'date' => 'required|date',
            'byproduct_type' => 'required|in:'.implode(',', array_keys(HarvestByproduct::BYPRODUCT_TYPES)),
            'quantity_kg' => 'required|numeric|min:0.001',
            'destination_type' => 'required|in:'.implode(',', array_keys(HarvestByproduct::DESTINATION_TYPES)),
            'destination_name' => 'required|string|max:255',
            'document_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ];
    }

    protected function performCreate(): void
    {
        HarvestByproduct::create([
            'viticulturist_id' => $this->viticulturistId(),
            'campaign_id' => $this->campaign_id,
            'date' => $this->date,
            'byproduct_type' => $this->byproduct_type,
            'quantity_kg' => $this->quantity_kg,
            'destination_type' => $this->destination_type,
            'destination_name' => $this->destination_name,
            'document_reference' => $this->document_reference ?: null,
            'notes' => $this->notes ?: null,
            'active' => true,
        ]);
    }

    protected function successMessage(): string
    {
        return __('Subproducto registrado correctamente.');
    }

    protected function indexRoute(): string
    {
        return 'viticulturist.harvest-byproducts.index';
    }

    protected function viewData(): array
    {
        return [
            'campaigns' => Campaign::forViticulturist($this->viticulturistId())->orderByDesc('year')->get(),
            'byproductTypes' => HarvestByproduct::BYPRODUCT_TYPES,
            'destinationTypes' => HarvestByproduct::DESTINATION_TYPES,
        ];
    }
}
