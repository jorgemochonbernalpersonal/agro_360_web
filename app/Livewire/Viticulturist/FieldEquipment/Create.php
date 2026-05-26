<?php

namespace App\Livewire\Viticulturist\FieldEquipment;

use App\Livewire\Viticulturist\AbstractCreate;
use App\Models\FieldEquipment;

class Create extends AbstractCreate
{
    public string $name                 = '';
    public string $equipment_type       = 'sprayer';
    public string $registration_number  = '';
    public string $purchase_date        = '';
    public string $last_inspection_date = '';
    public string $next_inspection_date = '';
    public string $inspection_entity    = '';
    public string $notes                = '';

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

    protected function performCreate(): void
    {
        FieldEquipment::create([
            'viticulturist_id'     => $this->viticulturistId(),
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
        return __('Equipo registrado correctamente.');
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
