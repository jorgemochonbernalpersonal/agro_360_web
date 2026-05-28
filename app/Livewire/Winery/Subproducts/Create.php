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

class Create extends Component
{
    use WithOwnershipRules, WithToastNotifications, WithRoleAwareRedirect;

    public string $wine_id                  = '';
    public string $type                     = '';
    public string $subproduct_date          = '';
    public string $quantity                 = '';
    public string $unit_of_measurement_id   = '';
    public string $destination              = '';
    public string $destination_name         = '';
    public string $lot_number               = '';
    public string $notes                    = '';

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
            'wine_id'                => $this->ownedWineRule(false),
            'type'                   => ['required', 'string', 'in:' . implode(',', array_keys(WineSubproduct::TYPES))],
            'subproduct_date'        => ['required', 'date'],
            'quantity'               => ['required', 'numeric', 'min:0.001'],
            'unit_of_measurement_id' => ['nullable', 'exists:units_of_measurement,id'],
            'destination'            => ['required', 'string', 'in:' . implode(',', array_keys(WineSubproduct::DESTINATIONS))],
            'destination_name'       => ['nullable', 'string', 'max:200'],
            'lot_number'             => ['nullable', 'string', 'max:100'],
            'notes'                  => ['nullable', 'string'],
        ];
    }

    protected function messages(): array
    {
        return [
            'type.required'        => __('Debes seleccionar el tipo de subproducto.'),
            'quantity.required'    => __('Indica la cantidad generada.'),
            'quantity.min'         => __('La cantidad debe ser mayor que cero.'),
            'destination.required' => __('Debes indicar el destino.'),
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        // Ownership check if wine is specified
        if (! empty($data['wine_id'])) {
            Wine::where('user_id', Auth::id())->findOrFail($data['wine_id']);
        }

        WineSubproduct::create([
            'user_id'                => Auth::id(),
            'wine_id'                => $data['wine_id'] ?: null,
            'type'                   => $data['type'],
            'subproduct_date'        => $data['subproduct_date'],
            'quantity'               => $data['quantity'],
            'unit_of_measurement_id' => $data['unit_of_measurement_id'] ?: null,
            'destination'            => $data['destination'],
            'destination_name'       => $data['destination_name'] ?: null,
            'lot_number'             => $data['lot_number'] ?: null,
            'notes'                  => $data['notes'] ?: null,
            'created_by'             => Auth::id(),
        ]);

        $this->toastSuccess(__('Subproducto registrado correctamente.'));
        $this->roleRedirect('subproducts.index');
    }

    public function render()
    {
        return view('livewire.winery.subproducts.create', [
            'types'        => WineSubproduct::typeOptions(),
            'destinations' => WineSubproduct::destinationOptions(),
            'wines'        => $this->wines,
            'units'        => $this->units,
        ])->layout('layouts.app');
    }
}
