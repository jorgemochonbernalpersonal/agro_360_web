<?php

namespace App\Livewire\Winery\Oenologists;

use App\Livewire\Winery\AbstractEdit;
use App\Models\Oenologist;

class Edit extends AbstractEdit
{
    public Oenologist $oenologist;

    public string $name = '';

    public string $surname = '';

    public string $license_number = '';

    public string $email = '';

    public string $phone = '';

    public string $notes = '';

    public function mount(Oenologist $oenologist): void
    {
        $this->authorize('update', $oenologist);

        $this->oenologist = $oenologist;
        $this->name = $oenologist->name;
        $this->surname = $oenologist->surname ?? '';
        $this->license_number = $oenologist->license_number ?? '';
        $this->email = $oenologist->email ?? '';
        $this->phone = $oenologist->phone ?? '';
        $this->notes = $oenologist->notes ?? '';
    }

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

    protected function performUpdate(): void
    {
        $this->oenologist->update([
            'name' => $this->name,
            'surname' => $this->surname ?: null,
            'license_number' => $this->license_number ?: null,
            'email' => $this->email ?: null,
            'phone' => $this->phone ?: null,
            'notes' => $this->notes ?: null,
        ]);
    }

    protected function successMessage(): string
    {
        return __('Enólogo actualizado correctamente.');
    }

    protected function indexRoute(): string
    {
        return 'winery.oenologists.index';
    }
}
