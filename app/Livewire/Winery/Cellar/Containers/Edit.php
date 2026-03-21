<?php

namespace App\Livewire\Winery\Cellar\Containers;

use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Container;
use App\Models\ContainerRoom;
use App\Models\ContainerType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Edit extends Component
{
    use WithToastNotifications, WithRoleAwareRedirect;

    public Container $container;

    public string $name          = '';
    public string $type_id       = '';
    public string $capacity      = '';
    public string $unit               = 'kg';
    public string $container_room_id  = '';
    public string $serial_number      = '';
    public string $description   = '';
    public string $purchase_date = '';
    public string $supplier_name = '';

    public function mount(Container $container): void
    {
        abort_if($container->user_id !== Auth::id(), 403);

        $this->container     = $container;
        $this->name          = $container->name;
        $this->type_id       = (string) $container->type_id;
        $this->capacity      = (string) $container->capacity;
        $this->unit               = $container->unit ?? 'kg';
        $this->container_room_id  = $container->container_room_id ? (string) $container->container_room_id : '';
        $this->serial_number      = $container->serial_number ?? '';
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
            'unit'               => ['required', 'in:kg,litros'],
            'container_room_id'  => ['nullable', Rule::exists('container_rooms', 'id')->where('user_id', Auth::id())],
            'serial_number'      => ['nullable', 'string', 'max:50'],
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
            'unit.required'     => 'Selecciona la unidad de medida.',
            'unit.in'           => 'Unidad no válida.',
        ];
    }

    public function save(): mixed
    {
        $this->validate();

        $newCapacity = (float) $this->capacity;
        $containerId = $this->container->id;

        $error = DB::transaction(function () use ($newCapacity, $containerId) {
            $fresh = Container::where('id', $containerId)->where('user_id', Auth::id())->lockForUpdate()->first();
            if (!$fresh) return 'Contenedor no encontrado.';

            // No se puede cambiar la unidad si el contenedor tiene contenido
            if ($fresh->getTotalUsed() > 0 && $this->unit !== ($fresh->unit ?? 'kg')) {
                return 'No se puede cambiar la unidad de un contenedor con contenido.';
            }

            $totalUsed = $fresh->getTotalUsed();
            if ($newCapacity < $totalUsed) {
                $unit = $fresh->unit ?? 'kg';
                return "La capacidad no puede ser menor que lo ya utilizado ({$totalUsed} {$unit}).";
            }

            $fresh->update([
                'name'              => $this->name,
                'type_id'           => (int) $this->type_id,
                'capacity'          => $newCapacity,
                'unit'              => $this->unit,
                'container_room_id' => $this->container_room_id ?: null,
                'serial_number'     => $this->serial_number ?: null,
                'description'       => $this->description ?: null,
                'purchase_date'     => $this->purchase_date ?: null,
                'supplier_name'     => $this->supplier_name ?: null,
            ]);
            return null;
        });

        if ($error) {
            $this->addError('capacity', $error);
            return null;
        }

        $this->toastSuccess('Contenedor actualizado correctamente.');
        return $this->roleRedirect('containers.index');
    }

    public function render()
    {
        return view('livewire.winery.cellar.containers.edit', [
            'types'    => ContainerType::orderBy('name')->get(),
            'rooms'    => ContainerRoom::where('user_id', Auth::id())->orderBy('name')->get(),
            'hasStock' => $this->container->getTotalUsed() > 0,
        ])->layout('layouts.app');
    }
}
