<?php

namespace App\Livewire\Winery\Suppliers;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    use WithToastNotifications;

    public string $name = '';

    public string $contact_person = '';

    public string $email = '';

    public string $phone = '';

    public string $address = '';

    public string $vat_number = '';

    public string $category = 'other';

    public string $notes = '';

    public function save(): void
    {
        $this->validate();

        Supplier::create([
            'user_id' => Auth::id(),
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

        $this->toastSuccess("Proveedor «{$this->name}» creado correctamente.");
        $this->redirect(roleRoute('suppliers.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.winery.suppliers.create', [
            'categories' => Supplier::categoryOptions(),
        ])->layout('layouts.app');
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
}
