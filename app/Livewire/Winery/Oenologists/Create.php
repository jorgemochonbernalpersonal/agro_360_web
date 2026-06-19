<?php

namespace App\Livewire\Winery\Oenologists;

use App\Livewire\Winery\AbstractCreate;
use App\Models\Oenologist;

class Create extends AbstractCreate
{
    public string $name = '';

    public string $surname = '';

    public string $license_number = '';

    public string $email = '';

    public string $phone = '';

    public string $notes = '';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:150',
            'surname' => 'nullable|string|max:150',
            'license_number' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ];
    }

    protected function performCreate(): void
    {
        Oenologist::create([
            'user_id' => $this->ownerId(),
            'name' => $this->name,
            'surname' => $this->surname ?: null,
            'license_number' => $this->license_number ?: null,
            'email' => $this->email ?: null,
            'phone' => $this->phone ?: null,
            'notes' => $this->notes ?: null,
            'active' => true,
        ]);
    }

    protected function successMessage(): string
    {
        return __('Enólogo creado correctamente.');
    }

    protected function indexRoute(): string
    {
        return 'winery.oenologists.index';
    }
}
