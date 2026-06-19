<?php

namespace App\Livewire\Winery\Subproducts;

use App\Livewire\Concerns\WithOwnershipRules;
use App\Livewire\Winery\AbstractEdit;
use App\Models\UnitOfMeasurement;
use App\Models\Wine;
use App\Models\WineSubproduct;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;

/**
 * @property-read mixed $wines
 * @property-read mixed $units
 */
class Edit extends AbstractEdit
{
    use WithOwnershipRules;

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
        $this->authorizeOwnership($subproduct);

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

    protected function performUpdate(): void
    {
        if ($this->wine_id !== '') {
            Wine::where('user_id', Auth::id())->findOrFail($this->wine_id);
        }

        $this->subproduct->update([
            'wine_id' => $this->wine_id ?: null,
            'type' => $this->type,
            'subproduct_date' => $this->subproduct_date,
            'quantity' => $this->quantity,
            'unit_of_measurement_id' => $this->unit_of_measurement_id ?: null,
            'destination' => $this->destination,
            'destination_name' => $this->destination_name ?: null,
            'lot_number' => $this->lot_number ?: null,
            'notes' => $this->notes ?: null,
        ]);
    }

    protected function successMessage(): string
    {
        return __('Subproducto actualizado correctamente.');
    }

    protected function indexRoute(): string
    {
        return 'winery.subproducts.index';
    }

    protected function viewData(): array
    {
        return [
            'types' => WineSubproduct::typeOptions(),
            'destinations' => WineSubproduct::destinationOptions(),
            'wines' => $this->wines,
            'units' => $this->units,
        ];
    }
}
