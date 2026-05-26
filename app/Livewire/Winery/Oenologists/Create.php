<?php

namespace App\Livewire\Winery\Oenologists;

use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Oenologist;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    use WithToastNotifications, WithRoleAwareRedirect;

    public string $name           = '';
    public string $surname        = '';
    public string $license_number = '';
    public string $email          = '';
    public string $phone          = '';
    public string $notes          = '';

    protected function rules(): array
    {
        return [
            'name'           => 'required|string|max:150',
            'surname'        => 'nullable|string|max:150',
            'license_number' => 'nullable|string|max:100',
            'email'          => 'nullable|email|max:255',
            'phone'          => 'nullable|string|max:50',
            'notes'          => 'nullable|string',
        ];
    }

    public function save()
    {
        $this->validate();

        Oenologist::create([
            'user_id'        => Auth::id(),
            'name'           => $this->name,
            'surname'        => $this->surname ?: null,
            'license_number' => $this->license_number ?: null,
            'email'          => $this->email ?: null,
            'phone'          => $this->phone ?: null,
            'notes'          => $this->notes ?: null,
            'active'         => true,
        ]);

        $this->toastSuccess(__('Enólogo creado correctamente.'));
        return $this->roleRedirect('oenologists.index');
    }

    public function render()
    {
        return view('livewire.winery.oenologists.create')->layout('layouts.app');
    }
}
