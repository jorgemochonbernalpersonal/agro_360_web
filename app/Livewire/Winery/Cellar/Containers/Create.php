<?php

namespace App\Livewire\Winery\Cellar\Containers;

use App\Livewire\Winery\AbstractCreate;
use App\Models\Container;
use App\Models\ContainerRoom;
use App\Models\ContainerType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class Create extends AbstractCreate
{
    public string $name = '';

    public string $type_id = '';

    public string $capacity = '';

    public string $unit = 'kg';

    public string $container_room_id = '';

    public string $serial_number = '';

    public string $description = '';

    public string $purchase_date = '';

    public string $supplier_name = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'type_id' => ['required', 'exists:container_types,id'],
            'capacity' => ['required', 'numeric', 'min:1'],
            'unit' => ['required', 'in:kg,litros'],
            'container_room_id' => ['nullable', Rule::exists('container_rooms', 'id')->where('user_id', Auth::id())],
            'serial_number' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'purchase_date' => ['nullable', 'date'],
            'supplier_name' => ['nullable', 'string', 'max:100'],
        ];
    }

    protected function performCreate(): void
    {
        Container::create([
            'user_id' => $this->ownerId(),
            'name' => $this->name,
            'type_id' => (int) $this->type_id,
            'capacity' => (float) $this->capacity,
            'unit' => $this->unit,
            'container_room_id' => $this->container_room_id ?: null,
            'used_capacity' => 0,
            'serial_number' => $this->serial_number ?: null,
            'description' => $this->description ?: null,
            'purchase_date' => $this->purchase_date ?: null,
            'supplier_name' => $this->supplier_name ?: null,
            'archived' => false,
        ]);
    }

    protected function successMessage(): string
    {
        return "Contenedor «{$this->name}» creado correctamente.";
    }

    protected function indexRoute(): string
    {
        return 'winery.containers.index';
    }

    protected function viewData(): array
    {
        return [
            'types' => ContainerType::orderBy('name')->get(),
            'rooms' => ContainerRoom::where('user_id', Auth::id())->orderBy('name')->get(),
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => __('El nombre del contenedor es obligatorio.'),
            'type_id.required' => __('Selecciona el tipo de contenedor.'),
            'capacity.required' => __('La capacidad es obligatoria.'),
            'capacity.min' => __('La capacidad debe ser mayor que 0.'),
            'unit.required' => __('Selecciona la unidad de medida.'),
            'unit.in' => __('Unidad no válida.'),
        ];
    }
}
