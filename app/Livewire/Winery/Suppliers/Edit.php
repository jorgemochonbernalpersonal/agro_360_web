<?php

namespace App\Livewire\Winery\Suppliers;

use App\Livewire\Winery\AbstractEdit;
use App\Models\Supplier;

class Edit extends AbstractEdit
{
    public Supplier $supplier;

    public string $name = '';

    public string $contact_person = '';

    public string $email = '';

    public string $phone = '';

    public string $address = '';

    public string $vat_number = '';

    public string $category = '';

    public string $notes = '';

    public function mount(Supplier $supplier): void
    {
        $this->authorize('update', $supplier);
        $this->supplier = $supplier;
        $this->name = $supplier->name;
        $this->contact_person = $supplier->contact_person ?? '';
        $this->email = $supplier->email ?? '';
        $this->phone = $supplier->phone ?? '';
        $this->address = $supplier->address ?? '';
        $this->vat_number = $supplier->vat_number ?? '';
        $this->category = $supplier->category;
        $this->notes = $supplier->notes ?? '';
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:200'],
            'contact_person' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:200'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'vat_number' => ['nullable', 'string', 'max:50'],
            'category' => ['required', 'in:'.implode(',', array_keys(Supplier::CATEGORIES))],
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function performUpdate(): void
    {
        $this->supplier->update([
            'name' => $this->name,
            'contact_person' => $this->contact_person ?: null,
            'email' => $this->email ?: null,
            'phone' => $this->phone ?: null,
            'address' => $this->address ?: null,
            'vat_number' => $this->vat_number ?: null,
            'category' => $this->category,
            'notes' => $this->notes ?: null,
        ]);
    }

    protected function successMessage(): string
    {
        return __('Proveedor actualizado correctamente.');
    }

    protected function indexRoute(): string
    {
        return 'winery.suppliers.index';
    }

    protected function viewData(): array
    {
        return [
            'categories' => Supplier::categoryOptions(),
        ];
    }
}
