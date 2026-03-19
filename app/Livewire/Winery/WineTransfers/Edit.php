<?php

namespace App\Livewire\Winery\WineTransfers;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Container;
use App\Models\Oenologist;
use App\Models\UnitOfMeasurement;
use App\Models\Wine;
use App\Models\WineTransfer;
use App\Services\WineContainerStockService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Edit extends Component
{
    use WithToastNotifications;

    public WineTransfer $transfer;
    public array $oldData             = [];

    public string $wine_id                = '';
    public string $from_container_id      = '';
    public string $to_container_id        = '';
    public string $quantity               = '';
    public string $unit_of_measurement_id = '';
    public string $transfer_type          = 'racking';
    public string $transfer_date          = '';
    public string $oenologist_id          = '';
    public string $notes                  = '';

    public function mount(WineTransfer $transfer): void
    {
        abort_if($transfer->wine?->user_id !== Auth::id(), 403);

        $this->transfer               = $transfer;

        // Snapshot para poder revertir el stock si se edita
        $this->oldData = [
            'wine_id'          => $transfer->wine_id,
            'from_container_id'=> $transfer->from_container_id,
            'to_container_id'  => $transfer->to_container_id,
            'quantity'         => $transfer->quantity,
        ];
        $this->wine_id                = (string) $transfer->wine_id;
        $this->from_container_id      = $transfer->from_container_id ? (string) $transfer->from_container_id : '';
        $this->to_container_id        = (string) $transfer->to_container_id;
        $this->quantity               = (string) $transfer->quantity;
        $this->unit_of_measurement_id = (string) $transfer->unit_of_measurement_id;
        $this->transfer_type          = $transfer->transfer_type;
        $this->transfer_date          = $transfer->transfer_date instanceof \Carbon\Carbon
            ? $transfer->transfer_date->format('Y-m-d')
            : $transfer->transfer_date;
        $this->oenologist_id          = $transfer->oenologist_id ? (string) $transfer->oenologist_id : '';
        $this->notes                  = $transfer->notes ?? '';
    }

    protected function rules(): array
    {
        return [
            'wine_id'                => ['required', 'exists:wines,id'],
            'from_container_id'      => ['nullable', 'exists:containers,id'],
            'to_container_id'        => ['required', 'exists:containers,id'],
            'quantity'               => ['required', 'numeric', 'min:0.001'],
            'unit_of_measurement_id' => ['required', 'exists:units_of_measurement,id'],
            'transfer_type'          => ['required', 'in:' . implode(',', array_keys(WineTransfer::TRANSFER_TYPES))],
            'transfer_date'          => ['required', 'date'],
            'oenologist_id'          => ['nullable', 'exists:oenologists,id'],
            'notes'                  => ['nullable', 'string'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        // Validate destination container capacity (add back what the old transfer was using)
        if ($this->to_container_id) {
            $dest = Container::where('user_id', Auth::id())->find($this->to_container_id);
            if ($dest) {
                $oldQty = (float) ($this->oldData['to_container_id'] == $this->to_container_id ? $this->oldData['quantity'] : 0);
                $available = $dest->getAvailableCapacity() + $oldQty;
                if ($available < (float) $this->quantity) {
                    $this->addError('quantity', 'El contenedor destino no tiene capacidad suficiente (' . number_format($available, 1) . ' L disponibles).');
                    return;
                }
            }
        }

        $this->transfer->update([
            'wine_id'                => $this->wine_id,
            'from_container_id'      => $this->from_container_id ?: null,
            'to_container_id'        => $this->to_container_id,
            'quantity'               => $this->quantity,
            'unit_of_measurement_id' => $this->unit_of_measurement_id,
            'transfer_type'          => $this->transfer_type,
            'transfer_date'          => $this->transfer_date,
            'oenologist_id'          => $this->oenologist_id ?: null,
            'notes'                  => $this->notes ?: null,
        ]);

        $this->transfer->refresh();
        app(WineContainerStockService::class)->updateTransfer($this->transfer, $this->oldData);

        $this->toastSuccess('Trasvase actualizado.');
        $this->redirect(route('winery.wine-transfers.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.winery.wine-transfers.edit', [
            'wines'       => Wine::where('user_id', Auth::id())->orderBy('name')->get(),
            'containers'  => Container::where('user_id', Auth::id())->orderBy('name')->get(),
            'units'       => UnitOfMeasurement::orderBy('name')->get(),
            'types'       => WineTransfer::TRANSFER_TYPES,
            'oenologists' => Oenologist::where('user_id', Auth::id())->orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
