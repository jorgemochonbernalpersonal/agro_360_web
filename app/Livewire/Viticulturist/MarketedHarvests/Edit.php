<?php

namespace App\Livewire\Viticulturist\MarketedHarvests;

use App\Livewire\Viticulturist\AbstractEdit;
use App\Models\Harvest;
use App\Models\MarketedHarvest;

class Edit extends AbstractEdit
{
    public MarketedHarvest $marketedHarvest;

    public string $harvest_id         = '';
    public string $delivery_date      = '';
    public string $quantity_kg        = '';
    public string $destination_type   = '';
    public string $buyer_name         = '';
    public string $buyer_rega_code    = '';
    public string $transport_document = '';
    public string $vehicle_plate      = '';
    public string $price_per_kg       = '';
    public string $total_value        = '';
    public string $notes              = '';

    public function mount(MarketedHarvest $marketedHarvest): void
    {
        $this->authorizeOwnership($marketedHarvest);

        $this->marketedHarvest    = $marketedHarvest;
        $this->harvest_id         = (string) $marketedHarvest->harvest_id;
        $this->delivery_date      = $marketedHarvest->delivery_date->format('Y-m-d');
        $this->quantity_kg        = (string) $marketedHarvest->quantity_kg;
        $this->destination_type   = $marketedHarvest->destination_type;
        $this->buyer_name         = (string) ($marketedHarvest->buyer_name ?? '');
        $this->buyer_rega_code    = (string) ($marketedHarvest->buyer_rega_code ?? '');
        $this->transport_document = (string) ($marketedHarvest->transport_document ?? '');
        $this->vehicle_plate      = (string) ($marketedHarvest->vehicle_plate ?? '');
        $this->price_per_kg       = (string) ($marketedHarvest->price_per_kg ?? '');
        $this->total_value        = (string) ($marketedHarvest->total_value ?? '');
        $this->notes              = (string) ($marketedHarvest->notes ?? '');
    }

    public function updatedQuantityKg(string $v): void
    {
        if ($v && $this->price_per_kg) {
            $this->total_value = (string) round((float) $v * (float) $this->price_per_kg, 2);
        }
    }

    public function updatedPricePerKg(string $v): void
    {
        if ($v && $this->quantity_kg) {
            $this->total_value = (string) round((float) $this->quantity_kg * (float) $v, 2);
        }
    }

    protected function rules(): array
    {
        return [
            'harvest_id'         => 'required|exists:harvests,id',
            'delivery_date'      => 'required|date',
            'quantity_kg'        => 'required|numeric|min:0.01',
            'destination_type'   => 'required|in:' . implode(',', array_keys(MarketedHarvest::DESTINATION_TYPES)),
            'buyer_name'         => 'nullable|string|max:255',
            'buyer_rega_code'    => 'nullable|string|max:20',
            'transport_document' => 'nullable|string|max:100',
            'vehicle_plate'      => 'nullable|string|max:20',
            'price_per_kg'       => 'nullable|numeric|min:0',
            'total_value'        => 'nullable|numeric|min:0',
            'notes'              => 'nullable|string',
        ];
    }

    protected function performUpdate(): void
    {
        $harvest = Harvest::whereHas('activity', fn($q) => $q->where('viticulturist_id', $this->viticulturistId()))
            ->with('activity')
            ->findOrFail($this->harvest_id);

        $this->marketedHarvest->update([
            'harvest_id'         => $this->harvest_id,
            'campaign_id'        => $harvest->activity->campaign_id,
            'delivery_date'      => $this->delivery_date,
            'quantity_kg'        => $this->quantity_kg,
            'destination_type'   => $this->destination_type,
            'buyer_name'         => $this->buyer_name ?: null,
            'buyer_rega_code'    => $this->buyer_rega_code ?: null,
            'transport_document' => $this->transport_document ?: null,
            'vehicle_plate'      => $this->vehicle_plate ?: null,
            'price_per_kg'       => $this->price_per_kg ?: null,
            'total_value'        => $this->total_value ?: null,
            'notes'              => $this->notes ?: null,
        ]);
    }

    protected function successMessage(): string
    {
        return 'Entrega actualizada correctamente.';
    }

    protected function indexRoute(): string
    {
        return 'viticulturist.marketed-harvests.index';
    }

    protected function viewData(): array
    {
        return [
            'harvests'     => Harvest::whereHas('activity', fn($q) => $q->where('viticulturist_id', $this->viticulturistId()))
                ->with(['plotPlanting.plot', 'plotPlanting.grapeVariety'])
                ->orderByDesc('harvest_start_date')
                ->get(),
            'destinations' => MarketedHarvest::DESTINATION_TYPES,
        ];
    }
}
