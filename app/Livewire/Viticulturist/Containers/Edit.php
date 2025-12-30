<?php

namespace App\Livewire\Viticulturist\Containers;

use App\Models\Container;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;

class Edit extends Component
{
    use WithFileUploads, WithToastNotifications;

    public Container $container;
    
    // Básicos
    public $name = '';
    public $serial_number = '';
    public $description = '';
    
    // Tipo y Material
    public $type_id = '';
    public $material_id = '';
    public $oak_type = '';
    public $toast_type = '';
    
    // Capacidad
    public $capacity = '';
    public $quantity = 1;
    public $unit_of_measurement_id = '';
    
    // Ubicación
    public $container_room_id = '';
    public $x_position = '';
    public $y_position = '';
    
    // Mantenimiento
    public $purchase_date = '';
    public $next_maintenance_date = '';
    public $supplier_name = '';
    
    // Fotos
    public $photos = [];
    public $existingPhotos = [];

    public function mount($id)
    {
        $this->container = Container::where('user_id', Auth::id())->findOrFail($id);
        
        // Cargar datos existentes
        $this->name = $this->container->name;
        $this->serial_number = $this->container->serial_number;
        $this->description = $this->container->description;
        $this->type_id = $this->container->type_id;
        $this->material_id = $this->container->material_id;
        $this->oak_type = $this->container->oak_type;
        $this->toast_type = $this->container->toast_type;
        $this->capacity = $this->container->capacity;
        $this->quantity = $this->container->quantity;
        $this->unit_of_measurement_id = $this->container->unit_of_measurement_id;
        $this->container_room_id = $this->container->container_room_id;
        $this->purchase_date = $this->container->purchase_date?->format('Y-m-d');
        $this->next_maintenance_date = $this->container->next_maintenance_date?->format('Y-m-d');
        $this->supplier_name = $this->container->supplier_name;
        $this->existingPhotos = $this->container->photos ?? [];
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'serial_number' => 'nullable|string|max:100|unique:containers,serial_number,' . $this->container->id,
            'description' => 'nullable|string|max:1000',
            'type_id' => 'required|integer',
            'material_id' => 'required|integer',
            'oak_type' => 'nullable|string|max:255',
            'toast_type' => 'nullable|in:light,medium,medium_plus,heavy',
            'capacity' => 'required|numeric|min:1',
            'quantity' => 'nullable|integer|min:1',
            'unit_of_measurement_id' => 'required|integer',
            'container_room_id' => 'nullable|integer',
            'purchase_date' => 'nullable|date|before_or_equal:today',
            'next_maintenance_date' => 'nullable|date|after:today',
            'supplier_name' => 'nullable|string|max:255',
            'photos.*' => 'nullable|image|max:2048',
        ];
    }

    public function save()
    {
        // Validar que no se reduzca capacidad si está ocupado
        if ($this->capacity < $this->container->used_capacity) {
            $this->addError('capacity', sprintf(
                'No puedes reducir la capacidad a %.2f L porque hay %.2f L ocupados.',
                $this->capacity,
                $this->container->used_capacity
            ));
            return;
        }

        $this->validate();

        try {
            $photoPaths = $this->existingPhotos;
            
            // Subir nuevas fotos
            if ($this->photos) {
                foreach ($this->photos as $photo) {
                    $path = $photo->store('containers', 'public');
                    $photoPaths[] = $path;
                }
            }

            $this->container->update([
                'name' => $this->name,
                'serial_number' => $this->serial_number ?: null,
                'description' => $this->description,
                'type_id' => $this->type_id,
                'material_id' => $this->material_id,
                'oak_type' => $this->oak_type ?: null,
                'toast_type' => $this->toast_type ?: null,
                'capacity' => $this->capacity,
                'quantity' => $this->quantity ?: 1,
                'unit_of_measurement_id' => $this->unit_of_measurement_id,
                'container_room_id' => $this->container_room_id ?: null,
                'purchase_date' => $this->purchase_date ?: null,
                'next_maintenance_date' => $this->next_maintenance_date ?: null,
                'supplier_name' => $this->supplier_name ?: null,
                'photos' => $photoPaths,
                'thumbnail_img' => $photoPaths[0] ?? null,
            ]);

            $this->toastSuccess('Contenedor actualizado correctamente.');
            return redirect()->route('viticulturist.digital-notebook.containers.show', $this->container->id);
            
        } catch (\Exception $e) {
            \Log::error('Error al actualizar contenedor', [
                'error' => $e->getMessage(),
                'container_id' => $this->container->id,
            ]);
            
            $this->toastError('Error al actualizar el contenedor. Por favor, intenta de nuevo.');
        }
    }

    public function render()
    {
        $containerRooms = \App\Models\ContainerRoom::where('user_id', Auth::id())
            ->orderBy('name')
            ->get();

        $unitsOfMeasurement = \App\Models\UnitOfMeasurement::orderBy('name')->get();

        return view('livewire.viticulturist.containers.edit', [
            'containerRooms' => $containerRooms,
            'unitsOfMeasurement' => $unitsOfMeasurement,
        ])->layout('layouts.app');
    }
}
