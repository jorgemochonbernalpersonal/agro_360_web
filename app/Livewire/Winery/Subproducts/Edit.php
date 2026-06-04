<?php

namespace App\Livewire\Winery\Subproducts;

use App\Livewire\Concerns\WithOwnershipRules;
use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\UnitOfMeasurement;
use App\Models\Wine;
use App\Models\WineSubproduct;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Edit extends Component
{
    use WithOwnershipRules, WithRoleAwareRedirect, WithToastNotifications;

    public WineSubproduct $subproduct;

    public string $wine_id = '';

    public string $type = '';

    public string $subproduct_date = '';

    public string $quantity = '';

    public string $unit_of_measurement_id = '';

    public string $destination = '';

    public string $destination_name = '';

    public string $lot_number = '';

    public string $notes = '';

    public function mount(WineSubproduct $subproduct): void
    {
        if ($subproduct->user_id !== Auth::id()) {
            abort(403);
        }

        $this->subproduct = $subproduct;
        $this->wine_id = (string) ($subproduct->wine_id ?? '');
        $this->type = $subproduct->type;
        $this->subproduct_date = $subproduct->subproduct_date->toDateString();
        $this->quantity = (string) $subproduct->quantity;
        $this->unit_of_measurement_id = (string) ($subproduct->unit_of_measurement_id ?? '');
        $this->destination = $subproduct->destination;
        $this->destination_name = $subproduct->destination_name ?? '';
        $this->lot_number = $subproduct->lot_number ?? '';
        $this->notes = $subproduct->notes ?? '';
    }

    #[Computed]
    public function wines()
    {
        return Wine::where('user_id', Auth::id())
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function units()
    {
        return UnitOfMeasurement::orderBy('name')->get();
    }

    public function save(): void
    {
        $data = $this->validate();

        if (! empty($data['wine_id'])) {
            Wine::where('user_id', Auth::id())->findOrFail($data['wine_id']);
        }

        $this->subproduct->update([
            'wine_id' => $data['wine_id'] ?: null,
            'type' => $data['type'],
            'subproduct_date' => $data['subproduct_date'],
            'quantity' => $data['quantity'],
            'unit_of_measurement_id' => $data['unit_of_measurement_id'] ?: null,
            'destination' => $data['destination'],
            'destination_name' => $data['destination_name'] ?: null,
            'lot_number' => $data['lot_number'] ?: null,
            'notes' => $data['notes'] ?: null,
        ]);

        $this->toastSuccess(__('Subproducto actualizado correctamente.'));
        $this->roleRedirect('subproducts.index');
    }

    public function render()
    {
        return view('livewire.winery.subproducts.edit', [
            'types' => WineSubproduct::typeOptions(),
            'destinations' => WineSubproduct::destinationOptions(),
            'wines' => $this->wines,
            'units' => $this->units,
        ])->layout('layouts.app');
    }

    protected function rules(): array
    {
        return [
            'wine_id' => $this->ownedWineRule(false),
            'type' => ['required', 'string', 'in:'.implode(',', array_keys(WineSubproduct::TYPES))],
            'subproduct_date' => ['required', 'date'],
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'unit_of_measurement_id' => ['nullable', 'exists:units_of_measurement,id'],
            'destination' => ['required', 'string', 'in:'.implode(',', array_keys(WineSubproduct::DESTINATIONS))],
            'destination_name' => ['nullable', 'string', 'max:200'],
            'lot_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function messages(): array
    {
        return [
            'type.required' => __('Debes seleccionar el tipo de subproducto.'),
            'quantity.required' => __('Indica la cantidad generada.'),
            'quantity.min' => __('La cantidad debe ser mayor que cero.'),
            'destination.required' => __('Debes indicar el destino.'),
        ];
    }
}
