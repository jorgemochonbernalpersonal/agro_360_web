<?php

namespace App\Livewire\Viticulturist\FieldEquipment;

use App\Livewire\Viticulturist\AbstractEdit;
use App\Models\FieldEquipment;

class Edit extends AbstractEdit
{
    public FieldEquipment $fieldEquipment;

    public string $name                 = '';
    public string $equipment_type       = 'sprayer';
    public string $registration_number  = '';
    public string $purchase_date        = '';
    public string $last_inspection_date = '';
    public string $next_inspection_date = '';
    public string $inspection_entity    = '';
    public string $notes                = '';

    public function mount(FieldEquipment $fieldEquipment): void
    {
        $this->authorizeOwnership($fieldEquipment);

        $this->fieldEquipment       = $fieldEquipment;
        $this->name                 = $fieldEquipment->name;
        $this->equipment_type       = $fieldEquipment->equipment_type;
        $this->registration_number  = (string) ($fieldEquipment->registration_number ?? '');
        $this->purchase_date        = $fieldEquipment->purchase_date?->format('Y-m-d') ?? '';
        $this->last_inspection_date = $fieldEquipment->last_inspection_date?->format('Y-m-d') ?? '';
        $this->next_inspection_date = $fieldEquipment->next_inspection_date?->format('Y-m-d') ?? '';
        $this->inspection_entity    = (string) ($fieldEquipment->inspection_entity ?? '');
        $this->notes                = (string) ($fieldEquipment->notes ?? '');
    }

    protected function rules(): array
    {
        return [
            'name'                 => 'required|string|max:100',
            'equipment_type'       => 'required|in:' . implode(',', array_keys(FieldEquipment::TYPES)),
            'registration_number'  => 'nullable|string|max:50',
            'purchase_date'        => 'nullable|date',
            'last_inspection_date' => 'nullable|date',
            'next_inspection_date' => 'nullable|date|after_or_equal:last_inspection_date',
            'inspection_entity'    => 'nullable|string|max:100',
            'notes'                => 'nullable|string',
        ];
    }

    protected function performUpdate(): void
    {
        $this->fieldEquipment->update([
            'name'                 => $this->name,
            'equipment_type'       => $this->equipment_type,
            'registration_number'  => $this->registration_number ?: null,
            'purchase_date'        => $this->purchase_date ?: null,
            'last_inspection_date' => $this->last_inspection_date ?: null,
            'next_inspection_date' => $this->next_inspection_date ?: null,
            'inspection_entity'    => $this->inspection_entity ?: null,
            'notes'                => $this->notes ?: null,
        ]);
    }

    protected function successMessage(): string
    {
        return 'Equipo actualizado correctamente.';
    }

    protected function indexRoute(): string
    {
        return 'viticulturist.field-equipment.index';
    }

    protected function viewData(): array
    {
        return [
            'types' => FieldEquipment::TYPES,
        ];
    }
}
