<?php

namespace App\Livewire\Winery\Cellar\Containers;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Container;
use App\Models\ContainerType;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Edit extends Component
{
    use WithToastNotifications;

    public Container $container;

    public string $name          = '';
    public string $type_id       = '';
    public string $capacity      = '';
    public string $serial_number = '';
    public string $description   = '';
    public string $purchase_date = '';
    public string $supplier_name = '';

    public function mount(Container $container): void
    {
        abort_if($container->user_id !== Auth::id(), 403);

        $this->container   = $container;
        $this->name          = $container->name;
        $this->type_id       = (string) $container->type_id;
        $this->capacity      = (string) $container->capacity;
        $this->serial_number = $container->serial_number ?? '';
        $this->description   = $container->description ?? '';
        $this->purchase_date = $container->purchase_date?->format('Y-m-d') ?? '';
        $this->supplier_name = $container->supplier_name ?? '';
    }

    protected function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:100'],
            'type_id'       => ['required', 'exists:container_types,id'],
            'capacity'      => ['required', 'numeric', 'min:0.01'],
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

        $newCapacity = (float) $this->capacity;

        if ($newCapacity < $this->container->used_capacity) {
            $this->addError('capacity', "La capacidad no puede ser menor que lo ya utilizado ({$this->container->used_capacity} kg).");
            return;
        }

        $this->container->update([
            'name'          => $this->name,
            'type_id'       => (int) $this->type_id,
            'capacity'      => $newCapacity,
            'serial_number' => $this->serial_number ?: null,
            'description'   => $this->description ?: null,
            'purchase_date' => $this->purchase_date ?: null,
            'supplier_name' => $this->supplier_name ?: null,
        ]);

        $this->toastSuccess('Contenedor actualizado correctamente.');
        redirect()->route('winery.containers.index');
    }

    public function render()
    {
        return view('livewire.winery.cellar.containers.edit', [
            'types' => ContainerType::orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
