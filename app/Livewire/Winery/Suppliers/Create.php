<?php

namespace App\Livewire\Winery\Suppliers;

use App\Livewire\Winery\AbstractCreate;
use App\Models\Supplier;

class Create extends AbstractCreate
{
    public string $name = '';

    public string $contact_person = '';

    public string $email = '';

    public string $phone = '';

    public string $address = '';

    public string $vat_number = '';

    public string $category = 'other';

    public string $notes = '';

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

    protected function performCreate(): void
    {
        Supplier::create([
            'user_id' => $this->ownerId(),
            'name' => $this->name,
            'contact_person' => $this->contact_person ?: null,
            'email' => $this->email ?: null,
            'phone' => $this->phone ?: null,
            'address' => $this->address ?: null,
            'vat_number' => $this->vat_number ?: null,
            'category' => $this->category,
            'notes' => $this->notes ?: null,
            'active' => true,
        ]);
    }

    protected function successMessage(): string
    {
        return __('Proveedor «:name» creado correctamente.', ['name' => $this->name]);
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
