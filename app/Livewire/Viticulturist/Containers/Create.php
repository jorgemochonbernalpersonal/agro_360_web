<?php

namespace App\Livewire\Viticulturist\Containers;

use App\Models\Container;
use App\Models\ContainerType;
use App\Models\ContainerMaterial;
use App\Models\ContainerRoom;
use App\Models\UnitOfMeasurement;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Create extends Component
{
    use WithFileUploads, WithToastNotifications;

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

    // Crear sala/bodega
    public $room_name = '';
    public $room_description = '';

    public function updatedTypeId($value)
    {
        // Si no es barrica, limpiar campos de roble
        if ($value != 1) { // Asumiendo que 1 = Barrica
            $this->oak_type = '';
            $this->toast_type = '';
        }
    }

    protected function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'serial_number' => 'nullable|string|max:100|unique:containers,serial_number',
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

        return $rules;
    }

    protected function messages()
    {
        return [
            'name.required' => 'El nombre del contenedor es obligatorio.',
            'capacity.required' => 'La capacidad es obligatoria.',
            'capacity.min' => 'La capacidad debe ser mayor a 0.',
            'type_id.required' => 'Debes seleccionar un tipo de contenedor.',
            'material_id.required' => 'Debes seleccionar un material.',
            'unit_of_measurement_id.required' => 'Debes seleccionar una unidad de medida.',
            'photos.*.image' => 'Solo se permiten imágenes.',
            'photos.*.max' => 'Las imágenes no pueden superar 2MB.',
        ];
    }

    public function save()
    {
        $this->validate();

        try {
            $photoPaths = [];
            
            // Subir fotos si existen
            if ($this->photos) {
                foreach ($this->photos as $photo) {
                    $path = $photo->store('containers', 'public');
                    $photoPaths[] = $path;
                }
            }

            $container = Container::create([
                'user_id' => Auth::id(),
                'name' => $this->name,
                'serial_number' => $this->serial_number ?: null,
                'description' => $this->description,
                'type_id' => $this->type_id,
                'material_id' => $this->material_id,
                'oak_type' => $this->oak_type ?: null,
                'toast_type' => $this->toast_type ?: null,
                'capacity' => $this->capacity,
                'used_capacity' => 0,
                'quantity' => $this->quantity ?: 1,
                'unit_of_measurement_id' => $this->unit_of_measurement_id,
                'container_room_id' => $this->container_room_id ?: null,
                'purchase_date' => $this->purchase_date ?: null,
                'next_maintenance_date' => $this->next_maintenance_date ?: null,
                'supplier_name' => $this->supplier_name ?: null,
                'photos' => $photoPaths,
                'thumbnail_img' => $photoPaths[0] ?? null,
                'archived' => false,
            ]);

            $this->toastSuccess('Contenedor creado correctamente.');
            return redirect()->route('viticulturist.digital-notebook.containers.index');
            
        } catch (\Exception $e) {
            \Log::error('Error al crear contenedor', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
            
            $this->toastError('Error al crear el contenedor. Por favor, intenta de nuevo.');
        }
    }

    public function createRoom()
    {
        $this->validate([
            'room_name' => 'required|string|max:255',
            'room_description' => 'nullable|string|max:1000',
        ]);

        try {
            \App\Models\ContainerRoom::create([
                'user_id' => Auth::id(),
                'name' => $this->room_name,
                'description' => $this->room_description,
            ]);

            $this->room_name = '';
            $this->room_description = '';
            
            $this->toastSuccess('Sala/Bodega creada correctamente.');
            
            // Cerrar modal con JavaScript
            $this->dispatch('close-modal');
            
        } catch (\Exception $e) {
            $this->toastError('Error al crear la sala. Por favor, intenta de nuevo.');
        }
    }

    public function render()
    {
        $containerTypes = ContainerType::orderBy('name')->get();
        $containerMaterials = ContainerMaterial::orderBy('name')->get();
        $containerRooms = ContainerRoom::where('user_id', Auth::id())
            ->orderBy('name')
            ->get();
        $unitsOfMeasurement = UnitOfMeasurement::orderBy('name')->get();

        return view('livewire.viticulturist.containers.create', [
            'containerTypes' => $containerTypes,
            'containerMaterials' => $containerMaterials,
            'containerRooms' => $containerRooms,
            'unitsOfMeasurement' => $unitsOfMeasurement,
        ])->layout('layouts.app');
    }
}
