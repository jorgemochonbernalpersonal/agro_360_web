<?php

namespace App\Livewire\Winery\Wines\Traits;

use App\Models\WineAdditive;
use App\Models\WinerySupply;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait WithWineAdditiveForm
{
    public string $ad_supply_id = '';

    public string $ad_process_detail_id = '';

    public string $ad_oenologist_id = '';

    public string $ad_additive_name = '';

    public string $ad_quantity = '';

    public string $ad_unit_id = '';

    public string $ad_date = '';

    public string $ad_notes = '';

    public function updatedAdSupplyId(): void
    {
        if ($this->ad_supply_id) {
            $supply = WinerySupply::find($this->ad_supply_id);
            if ($supply) {
                $this->ad_additive_name = $supply->name;
                $this->ad_unit_id = (string) ($supply->unit_of_measurement_id ?? '');
            }
        }
    }

    public function saveAdditive(): void
    {
        $this->validate([
            'ad_additive_name' => ['required', 'string', 'max:200'],
            'ad_quantity' => ['required', 'numeric', 'min:0.001'],
            'ad_unit_id' => ['required', 'exists:units_of_measurement,id'],
            'ad_date' => ['required', 'date'],
            'ad_supply_id' => $this->ownedWinerySupplyRule(false),
            'ad_process_detail_id' => $this->ownedWineProcessDetailRule(false),
            'ad_oenologist_id' => $this->ownedOenologistRule(),
            'ad_notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () {
            WineAdditive::create([
                'wine_id' => $this->wine->id,
                'wine_process_detail_id' => $this->ad_process_detail_id ?: null,
                'winery_supply_id' => $this->ad_supply_id ?: null,
                'oenologist_id' => $this->ad_oenologist_id ?: null,
                'unit_of_measurement_id' => $this->ad_unit_id,
                'additive_name' => $this->ad_additive_name,
                'quantity' => $this->ad_quantity,
                'application_date' => $this->ad_date,
                'notes' => $this->ad_notes ?: null,
                'created_by' => Auth::id(),
            ]);

            if ($this->ad_supply_id) {
                $supply = WinerySupply::where('user_id', Auth::id())->lockForUpdate()->find($this->ad_supply_id);
                if ($supply) {
                    $supply->decrement('current_stock', (float) $this->ad_quantity);
                }
            }
        });

        $this->resetAdForm();
        $this->dispatch('close-modal', id: 'modal-additive');
        $this->toastSuccess(__('Aditivo registrado correctamente.'));
    }

    public function deleteAdditive(int $id): void
    {
        $additive = WineAdditive::where('wine_id', $this->wine->id)->findOrFail($id);

        DB::transaction(function () use ($additive) {
            if ($additive->winery_supply_id) {
                $supply = WinerySupply::where('user_id', Auth::id())->lockForUpdate()->find($additive->winery_supply_id);
                if ($supply) {
                    $supply->increment('current_stock', (float) $additive->quantity);
                }
            }
            $additive->delete();
        });

        $this->toastSuccess(__('Aditivo eliminado.'));
    }

    private function resetAdForm(): void
    {
        $this->ad_supply_id = $this->ad_process_detail_id = $this->ad_oenologist_id = '';
        $this->ad_additive_name = $this->ad_quantity = $this->ad_unit_id = $this->ad_notes = '';
        $this->ad_date = now()->format('Y-m-d');
    }
}
