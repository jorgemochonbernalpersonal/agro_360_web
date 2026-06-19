<?php

namespace App\Livewire\Winery\Wines\Traits;

use App\Models\WineLoss;
use App\Services\WineContainerStockService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait WithWineLossForm
{
    public string $lo_container_id = '';

    public string $lo_type = 'evaporation';

    public string $lo_quantity = '';

    public string $lo_unit_id = '';

    public string $lo_date = '';

    public string $lo_notes = '';

    public function saveLoss(): void
    {
        $this->validate([
            'lo_container_id' => $this->ownedContainerRule(false),
            'lo_type' => ['required', 'in:'.implode(',', array_keys(WineLoss::LOSS_TYPES))],
            'lo_quantity' => ['required', 'numeric', 'min:0.001'],
            'lo_unit_id' => ['required', 'exists:units_of_measurement,id'],
            'lo_date' => ['required', 'date'],
            'lo_notes' => ['nullable', 'string'],
        ]);

        try {
            DB::transaction(function () {
                $loss = WineLoss::create([
                    'wine_id' => $this->wine->id,
                    'container_id' => $this->lo_container_id ?: null,
                    'loss_type' => $this->lo_type,
                    'quantity' => $this->lo_quantity,
                    'unit_of_measurement_id' => $this->lo_unit_id,
                    'loss_date' => $this->lo_date,
                    'notes' => $this->lo_notes ?: null,
                    'created_by' => Auth::id(),
                ]);
                app(WineContainerStockService::class)->recordLoss($loss);
            });
        } catch (\RuntimeException $e) {
            $this->addError('lo_quantity', $e->getMessage());

            return;
        }

        $this->resetLoForm();
        $this->dispatch('close-modal', id: 'modal-loss');
        $this->toastSuccess(__('Merma registrada.'));
    }

    public function deleteLoss(int $id): void
    {
        $loss = WineLoss::where('wine_id', $this->wine->id)->findOrFail($id);

        DB::transaction(function () use ($loss) {
            app(WineContainerStockService::class)->revertLoss($loss);
            $loss->delete();
        });

        $this->toastSuccess(__('Merma eliminada.'));
    }

    private function resetLoForm(): void
    {
        $this->lo_container_id = '';
        $this->lo_type = 'evaporation';
        $this->lo_quantity = $this->lo_unit_id = $this->lo_notes = '';
        $this->lo_date = now()->format('Y-m-d');
    }
}
