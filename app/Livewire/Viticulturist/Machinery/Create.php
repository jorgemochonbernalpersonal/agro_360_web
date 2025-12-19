<?php

namespace App\Livewire\Viticulturist\Machinery;

use App\Models\Machinery;
use App\Models\MachineryType;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads, WithToastNotifications;

    public $name = '';
    public $machinery_type_id = '';
    public $brand = '';
    public $model = '';
    public $serial_number = '';
    public $year = '';
    public $purchase_date = '';
    public $purchase_price = '';
    public $current_value = '';
    public $roma_registration = '';
    public $is_rented = false;
    public $capacity = '';
    public $last_revision_date = '';
    public $image;
    public $notes = '';
    public $active = true;

    public function mount()
    {
        // Validar autorización
        if (!Auth::user()->can('create', Machinery::class)) {
            abort(403, 'No tienes permiso para crear maquinaria.');
        }
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'machinery_type_id' => 'required|exists:machinery_types,id',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'year' => 'nullable|integer|min:1900|max:' . (now()->year + 1),
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric|min:0',
            'current_value' => 'nullable|numeric|min:0',
            'roma_registration' => 'nullable|string|max:255',
            'is_rented' => 'boolean',
            'capacity' => 'nullable|string|max:255',
            'last_revision_date' => 'nullable|date',
            'image' => 'nullable|image|max:2048',
            'notes' => 'nullable|string',
            'active' => 'boolean',
        ];
    }

    public function save()
    {
        $this->validate();

        $user = Auth::user();

        try {
            DB::transaction(function () use ($user) {
                $typeName = null;

                if ($this->machinery_type_id) {
                    $type = MachineryType::find($this->machinery_type_id);
                    $typeName = $type?->name;
                }

                $imagePath = null;
                
                // Guardar imagen si existe
                if ($this->image) {
                    $imagePath = $this->image->store('machinery', 'public');
                }

                Machinery::create([
                    'name' => $this->name,
                    'type' => $typeName ?: null,
                    'machinery_type_id' => $this->machinery_type_id ?: null,
                    'brand' => $this->brand ?: null,
                    'model' => $this->model ?: null,
                    'serial_number' => $this->serial_number ?: null,
                    'year' => $this->year ?: null,
                    'purchase_date' => $this->purchase_date ?: null,
                    'purchase_price' => $this->purchase_price ?: null,
                    'current_value' => $this->current_value ?: null,
                    'roma_registration' => $this->roma_registration ?: null,
                    'is_rented' => $this->is_rented,
                    'capacity' => $this->capacity ?: null,
                    'last_revision_date' => $this->last_revision_date ?: null,
                    'image' => $imagePath,
                    'notes' => $this->notes ?: null,
                    'viticulturist_id' => $user->id,
                    'active' => $this->active,
                ]);
            });

            $this->toastSuccess('Maquinaria creada correctamente.');
            return redirect()->route('viticulturist.machinery.index');
        } catch (\Exception $e) {
            \Log::error('Error al crear maquinaria', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString(),
            ]);

            $this->toastError('Error al crear la maquinaria. Por favor, intenta de nuevo.');
            return;
        }
    }

    public function render()
    {
        $types = MachineryType::where('active', true)
            ->orderBy('name')
            ->get();

        return view('livewire.viticulturist.machinery.create', [
                'machineryTypes' => $types,
            ])
            ->layout('layouts.app');
    }
}
