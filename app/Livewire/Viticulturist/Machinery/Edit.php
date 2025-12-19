<?php

namespace App\Livewire\Viticulturist\Machinery;

use App\Models\Machinery;
use App\Models\MachineryType;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\WithFileUploads;

class Edit extends Component
{
    use WithFileUploads, WithToastNotifications;

    public Machinery $machinery;
    
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
    public $current_image = '';
    public $notes = '';
    public $active = true;

    public function mount(Machinery $machinery)
    {
        // Validar autorización
        if (!Auth::user()->can('update', $machinery)) {
            abort(403, 'No tienes permiso para editar esta maquinaria.');
        }

        $this->machinery = $machinery;
        $this->name = $machinery->name;
        $this->machinery_type_id = $machinery->machinery_type_id;
        $this->brand = $machinery->brand ?? '';
        $this->model = $machinery->model ?? '';
        $this->serial_number = $machinery->serial_number ?? '';
        $this->year = $machinery->year ?? '';
        $this->purchase_date = $machinery->purchase_date?->format('Y-m-d') ?? '';
        $this->purchase_price = $machinery->purchase_price ?? '';
        $this->current_value = $machinery->current_value ?? '';
        $this->roma_registration = $machinery->roma_registration ?? '';
        $this->is_rented = $machinery->is_rented;
        $this->capacity = $machinery->capacity ?? '';
        $this->last_revision_date = $machinery->last_revision_date?->format('Y-m-d') ?? '';
        $this->current_image = $machinery->image ?? '';
        $this->notes = $machinery->notes ?? '';
        $this->active = $machinery->active;
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
                $imagePath = $this->current_image;
                
                // Si hay una nueva imagen, guardarla y eliminar la anterior
                if ($this->image) {
                    // Eliminar imagen anterior si existe
                    if ($imagePath && \Storage::disk('public')->exists($imagePath)) {
                        \Storage::disk('public')->delete($imagePath);
                    }
                    $imagePath = $this->image->store('machinery', 'public');
                }

                $this->machinery->update([
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
                    'active' => $this->active,
                ]);
            });

            $this->toastSuccess('Maquinaria actualizada correctamente.');
            return redirect()->route('viticulturist.machinery.index');
        } catch (\Exception $e) {
            \Log::error('Error al actualizar maquinaria', [
                'error' => $e->getMessage(),
                'machinery_id' => $this->machinery->id,
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString(),
            ]);

            $this->toastError('Error al actualizar la maquinaria. Por favor, intenta de nuevo.');
            return;
        }
    }

    public function render()
    {
        $types = MachineryType::where('active', true)
            ->orderBy('name')
            ->get();

        return view('livewire.viticulturist.machinery.edit', [
                'machineryTypes' => $types,
            ])
            ->layout('layouts.app');
    }
}
