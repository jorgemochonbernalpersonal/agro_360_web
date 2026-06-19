<?php

namespace App\Livewire\Winery\Wines\Traits;

use App\Models\WineTransfer;
use App\Services\WineContainerStockService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait WithWineTransferForm
{
    public string $tr_from_container_id = '';

    public string $tr_to_container_id = '';

    public string $tr_quantity = '';

    public string $tr_unit_id = '';

    public string $tr_type = 'racking';

    public string $tr_date = '';

    public string $tr_oenologist_id = '';

    public string $tr_notes = '';

    public function saveTransfer(): void
    {
        $this->validate([
            'tr_from_container_id' => $this->ownedContainerRule(false),
            'tr_to_container_id' => [...$this->ownedContainerRule(), 'different:tr_from_container_id'],
            'tr_quantity' => ['required', 'numeric', 'min:0.001'],
            'tr_unit_id' => ['required', 'exists:units_of_measurement,id'],
            'tr_type' => ['required', 'in:'.implode(',', array_keys(WineTransfer::TRANSFER_TYPES))],
            'tr_date' => ['required', 'date'],
            'tr_oenologist_id' => $this->ownedOenologistRule(),
            'tr_notes' => ['nullable', 'string'],
        ]);

        try {
            DB::transaction(function () {
                $transfer = WineTransfer::create([
                    'wine_id' => $this->wine->id,
                    'from_container_id' => $this->tr_from_container_id ?: null,
                    'to_container_id' => $this->tr_to_container_id,
                    'quantity' => $this->tr_quantity,
                    'unit_of_measurement_id' => $this->tr_unit_id,
                    'transfer_type' => $this->tr_type,
                    'transfer_date' => $this->tr_date,
                    'oenologist_id' => $this->tr_oenologist_id ?: null,
                    'notes' => $this->tr_notes ?: null,
                    'created_by' => Auth::id(),
                ]);
                app(WineContainerStockService::class)->recordTransfer($transfer);
            });
        } catch (\RuntimeException $e) {
            $this->addError('tr_quantity', $e->getMessage());

            return;
        }

        $this->resetTrForm();
        $this->dispatch('close-modal', id: 'modal-transfer');
        $this->toastSuccess(__('Trasvase registrado.'));
    }

    public function deleteTransfer(int $id): void
    {
        $transfer = WineTransfer::where('wine_id', $this->wine->id)->findOrFail($id);

        DB::transaction(function () use ($transfer) {
            app(WineContainerStockService::class)->revertTransfer($transfer);
            $transfer->delete();
        });

        $this->toastSuccess(__('Trasvase eliminado.'));
    }

    private function resetTrForm(): void
    {
        $this->tr_from_container_id = $this->tr_to_container_id = '';
        $this->tr_quantity = $this->tr_unit_id = $this->tr_notes = '';
        $this->tr_oenologist_id = '';
        $this->tr_type = 'racking';
        $this->tr_date = now()->format('Y-m-d');
    }
}
