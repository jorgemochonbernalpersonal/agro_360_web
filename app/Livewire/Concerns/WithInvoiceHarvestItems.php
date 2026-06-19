<?php

namespace App\Livewire\Concerns;

use App\Models\Harvest;
use App\Models\HarvestStock;
use Illuminate\Support\Facades\Auth;

trait WithInvoiceHarvestItems
{
    public $selectedCampaign = '';

    public $selectedHarvestId = '';

    public $availableHarvests = [];

    public function updatedSelectedCampaign(): void
    {
        $this->loadHarvests();
        $this->selectedHarvestId = '';
    }

    public function loadHarvests(): void
    {
        $user = Auth::user();

        $harvests = Harvest::whereHas('activity', fn ($q) => $q->where('viticulturist_id', $user->id))
            ->with(['activity.plot', 'plotPlanting.grapeVariety', 'activity.campaign', 'container'])
            ->when($this->selectedCampaign, fn ($q) => $q->whereHas('activity', fn ($q) => $q->where('campaign_id', $this->selectedCampaign)))
            ->where('total_weight', '>', 0)
            ->orderBy('harvest_start_date', 'desc')
            ->get();

        $harvestIds = $harvests->pluck('id');
        $latestStocks = HarvestStock::whereIn('harvest_id', $harvestIds)
            ->whereRaw('id = (SELECT MAX(hs2.id) FROM harvest_stocks hs2 WHERE hs2.harvest_id = harvest_stocks.harvest_id)')
            ->get()
            ->keyBy('harvest_id');

        $this->availableHarvests = $harvests
            ->map(function ($harvest) use ($latestStocks) {
                $latestStock = $latestStocks->get($harvest->id);
                $harvest->available_qty_computed = $latestStock
                    ? (float) $latestStock->available_qty
                    : (float) $harvest->total_weight;

                return $harvest;
            })
            ->filter(fn ($h) => $h->available_qty_computed > 0)
            ->values();
    }

    public function addHarvestToInvoice(): void
    {
        if (! $this->selectedHarvestId) {
            return;
        }

        $harvest = Harvest::with(['activity.plot', 'plotPlanting.grapeVariety'])
            ->find($this->selectedHarvestId);

        if (! $harvest) {
            $this->toastError(__('Cosecha no encontrada.'));

            return;
        }

        foreach ($this->items as $item) {
            if (isset($item['harvest_id']) && $item['harvest_id'] == $harvest->id) {
                $this->toastError(__('Esta cosecha ya está en la factura actual.'));

                return;
            }
        }

        $latestStock = HarvestStock::where('harvest_id', $harvest->id)->latest('id')->first();
        $availableQty = $latestStock
            ? (float) $latestStock->available_qty
            : (float) $harvest->total_weight;

        if ($availableQty <= 0) {
            $this->toastError(__('Esta cosecha no tiene stock disponible para facturar.'));

            return;
        }

        $user = Auth::user();
        $defaultTax = $user->defaultTax()->first()
            ?? $this->availableTaxes->where('code', 'IVA')->where('rate', 21)->first()
            ?? $this->availableTaxes->first();

        $grapeVarietyName = $harvest->plotPlanting->grapeVariety->name ?? 'Uva';
        $plotName = $harvest->activity->plot->name ?? '';
        $itemName = $grapeVarietyName.($plotName ? ' - '.$plotName : '');

        $this->items[] = [
            'id' => null,
            'harvest_id' => $harvest->id,
            'name' => $itemName,
            'description' => __('Cosecha del ').$harvest->harvest_start_date->format('d/m/Y').
                ($harvest->plotPlanting->grapeVariety ? ' - Variedad: '.$harvest->plotPlanting->grapeVariety->name : ''),
            'sku' => __('HARV-').$harvest->id,
            'quantity' => $availableQty,
            'unit' => 'kg',
            'available_qty' => $availableQty,
            'total_weight' => (float) $harvest->total_weight,
            'unit_price' => $harvest->price_per_kg ?? 0,
            'discount_percentage' => 0,
            'tax_id' => $defaultTax ? $defaultTax->id : null,
            'concept_type' => 'harvest',
        ];

        $this->selectedHarvestId = '';

        if (property_exists($this, 'harvestAdded')) {
            $this->harvestAdded = true;
        }

        $this->toastSuccess(__('Cosecha añadida a la factura.'));
    }
}
