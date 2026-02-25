<?php

namespace App\Livewire\Viticulturist\FieldEquipment;

use App\Models\FieldEquipment;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithToastNotifications;

    public $showCreateModal = false;
    public $showEditModal = false;
    public $editingId = null;

    // Campos formulario
    public $name = '';
    public $equipment_type = 'sprayer';
    public $registration_number = '';
    public $purchase_date = '';
    public $last_inspection_date = '';
    public $next_inspection_date = '';
    public $inspection_entity = '';
    public $notes = '';

    protected function rules(): array
    {
        return [
            'name'                 => 'required|string|max:100',
            'equipment_type'       => 'required|in:sprayer,spreader,irrigation,tractor,harvester,pruner,mower,other',
            'registration_number'  => 'nullable|string|max:50',
            'purchase_date'        => 'nullable|date',
            'last_inspection_date' => 'nullable|date',
            'next_inspection_date' => 'nullable|date|after_or_equal:last_inspection_date',
            'inspection_entity'    => 'nullable|string|max:100',
            'notes'                => 'nullable|string',
        ];
    }

    public function openCreate()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function openEdit(int $id)
    {
        $equipment = FieldEquipment::forViticulturist(Auth::id())->findOrFail($id);
        $this->editingId             = $id;
        $this->name                  = $equipment->name;
        $this->equipment_type        = $equipment->equipment_type;
        $this->registration_number   = $equipment->registration_number ?? '';
        $this->purchase_date         = $equipment->purchase_date?->format('Y-m-d') ?? '';
        $this->last_inspection_date  = $equipment->last_inspection_date?->format('Y-m-d') ?? '';
        $this->next_inspection_date  = $equipment->next_inspection_date?->format('Y-m-d') ?? '';
        $this->inspection_entity     = $equipment->inspection_entity ?? '';
        $this->notes                 = $equipment->notes ?? '';
        $this->showEditModal         = true;
    }

    public function save()
    {
        $this->validate();

        FieldEquipment::create([
            'viticulturist_id'     => Auth::id(),
            'name'                 => $this->name,
            'equipment_type'       => $this->equipment_type,
            'registration_number'  => $this->registration_number ?: null,
            'purchase_date'        => $this->purchase_date ?: null,
            'last_inspection_date' => $this->last_inspection_date ?: null,
            'next_inspection_date' => $this->next_inspection_date ?: null,
            'inspection_entity'    => $this->inspection_entity ?: null,
            'notes'                => $this->notes ?: null,
        ]);

        $this->showCreateModal = false;
        $this->resetForm();
        $this->toastSuccess('Equipo registrado correctamente.');
    }

    public function update()
    {
        $this->validate();

        $equipment = FieldEquipment::forViticulturist(Auth::id())->findOrFail($this->editingId);
        $equipment->update([
            'name'                 => $this->name,
            'equipment_type'       => $this->equipment_type,
            'registration_number'  => $this->registration_number ?: null,
            'purchase_date'        => $this->purchase_date ?: null,
            'last_inspection_date' => $this->last_inspection_date ?: null,
            'next_inspection_date' => $this->next_inspection_date ?: null,
            'inspection_entity'    => $this->inspection_entity ?: null,
            'notes'                => $this->notes ?: null,
        ]);

        $this->showEditModal = false;
        $this->resetForm();
        $this->toastSuccess('Equipo actualizado correctamente.');
    }

    public function deactivate(int $id)
    {
        $equipment = FieldEquipment::forViticulturist(Auth::id())->findOrFail($id);
        $equipment->update(['active' => false]);
        $this->toastSuccess('Equipo dado de baja.');
    }

    protected function resetForm()
    {
        $this->editingId            = null;
        $this->name                 = '';
        $this->equipment_type       = 'sprayer';
        $this->registration_number  = '';
        $this->purchase_date        = '';
        $this->last_inspection_date = '';
        $this->next_inspection_date = '';
        $this->inspection_entity    = '';
        $this->notes                = '';
        $this->resetValidation();
    }

    public function render()
    {
        $equipment = FieldEquipment::forViticulturist(Auth::id())
            ->active()
            ->orderBy('name')
            ->get();

        return view('livewire.viticulturist.field-equipment.index', [
            'equipment' => $equipment,
            'types'     => FieldEquipment::TYPES,
        ])->layout('layouts.app');
    }
}
