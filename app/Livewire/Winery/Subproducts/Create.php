<?php

namespace App\Livewire\Winery\Subproducts;

use App\Livewire\Concerns\WithOwnershipRules;
use App\Livewire\Winery\AbstractCreate;
use App\Models\UnitOfMeasurement;
use App\Models\Wine;
use App\Models\WineSubproduct;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;

/**
 * @property-read mixed $wines
 * @property-read mixed $units
 */
class Create extends AbstractCreate
{
    use WithOwnershipRules;

    public string $wine_id = '';

    public string $type = '';

    public string $subproduct_date = '';

    public string $quantity = '';

    public string $unit_of_measurement_id = '';

    public string $destination = '';

    public string $destination_name = '';

    public string $lot_number = '';

    public string $notes = '';

    public function mount(): void
    {
        $this->subproduct_date = now()->toDateString();
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

    protected function performCreate(): void
    {
        if ($this->wine_id !== '') {
            Wine::where('user_id', Auth::id())->findOrFail($this->wine_id);
        }

        WineSubproduct::create([
            'user_id' => $this->ownerId(),
            'wine_id' => $this->wine_id ?: null,
            'type' => $this->type,
            'subproduct_date' => $this->subproduct_date,
            'quantity' => $this->quantity,
            'unit_of_measurement_id' => $this->unit_of_measurement_id ?: null,
            'destination' => $this->destination,
            'destination_name' => $this->destination_name ?: null,
            'lot_number' => $this->lot_number ?: null,
            'notes' => $this->notes ?: null,
            'created_by' => $this->ownerId(),
        ]);
    }

    protected function successMessage(): string
    {
        return __('Subproducto registrado correctamente.');
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
