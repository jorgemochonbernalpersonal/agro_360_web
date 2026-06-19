<?php

namespace App\Livewire\Winery\Wines\Traits;

use App\Models\Harvest;
use App\Models\WineHarvest;
use Illuminate\Support\Facades\Auth;

trait WithWineComposition
{
    public string $co_harvest_id = '';

    public string $co_quantity_kg = '';

    public function linkHarvest(): void
    {
        $this->validate([
            'co_harvest_id' => $this->ownedHarvestRule(),
            'co_quantity_kg' => ['required', 'numeric', 'min:0.001'],
        ]);

        $harvest = Harvest::where('winery_id', Auth::id())->findOrFail($this->co_harvest_id);

        WineHarvest::updateOrCreate(
            ['wine_id' => $this->wine->id, 'harvest_id' => $harvest->id],
            ['quantity_kg' => $this->co_quantity_kg]
        );

        $this->recalculateCompositionPercentages();
        $this->co_harvest_id = '';
        $this->co_quantity_kg = '';
        $this->dispatch('close-modal', id: 'modal-composition');
        $this->toastSuccess(__('Recepción vinculada al lote.'));
    }

    public function unlinkHarvest(int $wineHarvestId): void
    {
        WineHarvest::where('wine_id', $this->wine->id)->findOrFail($wineHarvestId)->delete();
        $this->recalculateCompositionPercentages();
        $this->toastSuccess(__('Recepción desvinculada.'));
    }

    private function recalculateCompositionPercentages(): void
    {
        $entries = WineHarvest::where('wine_id', $this->wine->id)->get();
        $total = $entries->sum('quantity_kg');

        if ($total <= 0) {
            return;
        }

        foreach ($entries as $entry) {
            $entry->update(['percentage' => round(($entry->quantity_kg / $total) * 100, 2)]);
        }
    }
}
