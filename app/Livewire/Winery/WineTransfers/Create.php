<?php

namespace App\Livewire\Winery\WineTransfers;

use App\Livewire\Concerns\WithOwnershipRules;
use App\Livewire\Winery\AbstractCreate;
use App\Models\Container;
use App\Models\Oenologist;
use App\Models\UnitOfMeasurement;
use App\Models\Wine;
use App\Models\WineTransfer;
use App\Services\WineContainerStockService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class Create extends AbstractCreate
{
    use WithOwnershipRules;

    public string $wine_id = '';

    public string $from_container_id = '';

    public string $to_container_id = '';

    public string $quantity = '';

    public string $unit_of_measurement_id = '';

    public string $transfer_type = 'racking';

    public string $transfer_date = '';

    public string $oenologist_id = '';

    public string $notes = '';

    public function mount(): void
    {
        $this->transfer_date = now()->format('Y-m-d');
    }

    protected function rules(): array
    {
        return [
            'wine_id' => ['required', Rule::exists('wines', 'id')->where('user_id', Auth::id())],
            'from_container_id' => ['nullable', Rule::exists('containers', 'id')->where('user_id', Auth::id())],
            'to_container_id' => ['required', Rule::exists('containers', 'id')->where('user_id', Auth::id())],
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'unit_of_measurement_id' => ['required', 'exists:units_of_measurement,id'],
            'transfer_type' => ['required', 'in:'.implode(',', array_keys(WineTransfer::TRANSFER_TYPES))],
            'transfer_date' => ['required', 'date'],
            'oenologist_id' => $this->ownedOenologistRule(),
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function performCreate(): void
    {
        // Validate destination container has enough free capacity
        if ($this->to_container_id) {
            $dest = Container::where('user_id', Auth::id())->find($this->to_container_id);
            if ($dest && $dest->getAvailableCapacity() < (float) $this->quantity) {
                throw ValidationException::withMessages([
                    'quantity' => __('El contenedor destino no tiene capacidad suficiente (:available L disponibles).', ['available' => number_format($dest->getAvailableCapacity(), 1)]),
                ]);
            }
        }

        $transfer = WineTransfer::create([
            'wine_id' => $this->wine_id,
            'from_container_id' => $this->from_container_id ?: null,
            'to_container_id' => $this->to_container_id,
            'quantity' => $this->quantity,
            'unit_of_measurement_id' => $this->unit_of_measurement_id,
            'transfer_type' => $this->transfer_type,
            'transfer_date' => $this->transfer_date,
            'oenologist_id' => $this->oenologist_id ?: null,
            'notes' => $this->notes ?: null,
            'created_by' => $this->ownerId(),
        ]);

        app(WineContainerStockService::class)->recordTransfer($transfer);
    }

    protected function successMessage(): string
    {
        return __('Trasvase registrado correctamente.');
    }

    protected function indexRoute(): string
    {
        return 'winery.wine-transfers.index';
    }

    protected function viewData(): array
    {
        return [
            'wines' => Wine::where('user_id', Auth::id())->orderBy('name')->get(),
            'containers' => Container::where('user_id', Auth::id())->orderBy('name')->get(),
            'units' => UnitOfMeasurement::orderBy('name')->get(),
            'types' => WineTransfer::transferTypeOptions(),
            'oenologists' => Oenologist::where('user_id', Auth::id())->orderBy('name')->get(),
        ];
    }
}
