<?php

namespace App\Livewire\Winery\Cellar\Containers;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Container;
use App\Models\ContainerType;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    use WithToastNotifications;

    public string $name          = '';
    public string $type_id       = '';
    public string $capacity      = '';
    public string $serial_number = '';
    public string $description   = '';
    public string $purchase_date = '';
    public string $supplier_name = '';

    protected function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:100'],
            'type_id'       => ['required', 'exists:container_types,id'],
            'capacity'      => ['required', 'numeric', 'min:1'],
            'serial_number' => ['nullable', 'string', 'max:50'],
            'description'   => ['nullable', 'string'],
            'purchase_date' => ['nullable', 'date'],
            'supplier_name' => ['nullable', 'string', 'max:100'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required'     => 'El nombre del contenedor es obligatorio.',
            'type_id.required'  => 'Selecciona el tipo de contenedor.',
            'capacity.required' => 'La capacidad es obligatoria.',
            'capacity.min'      => 'La capacidad debe ser mayor que 0.',
        ];
    }

    public function save(): void
    {
        $this->validate();

        Container::create([
            'user_id'       => Auth::id(),
            'name'          => $this->name,
            'type_id'       => (int) $this->type_id,
            'capacity'      => (float) $this->capacity,
            'used_capacity' => 0,
            'serial_number' => $this->serial_number ?: null,
            'description'   => $this->description ?: null,
            'purchase_date' => $this->purchase_date ?: null,
            'supplier_name' => $this->supplier_name ?: null,
            'archived'      => false,
        ]);

        $this->toastSuccess("Contenedor «{$this->name}» creado correctamente.");
        redirect()->route('winery.containers.index');
    }

    public function render()
    {
        return view('livewire.winery.cellar.containers.create', [
            'types' => ContainerType::orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
