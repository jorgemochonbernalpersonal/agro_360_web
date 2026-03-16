<?php

namespace App\Livewire\Winery\Oenologists;

use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Oenologist;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Edit extends Component
{
    use WithToastNotifications, WithRoleAwareRedirect;

    public Oenologist $oenologist;

    public string $name           = '';
    public string $surname        = '';
    public string $license_number = '';
    public string $email          = '';
    public string $phone          = '';
    public string $notes          = '';

    public function mount(Oenologist $oenologist): void
    {
        abort_if($oenologist->user_id !== Auth::id(), 403);

        $this->oenologist     = $oenologist;
        $this->name           = $oenologist->name;
        $this->surname        = $oenologist->surname ?? '';
        $this->license_number = $oenologist->license_number ?? '';
        $this->email          = $oenologist->email ?? '';
        $this->phone          = $oenologist->phone ?? '';
        $this->notes          = $oenologist->notes ?? '';
    }

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

    public function update()
    {
        $this->validate();

        $this->oenologist->update([
            'name'           => $this->name,
            'surname'        => $this->surname ?: null,
            'license_number' => $this->license_number ?: null,
            'email'          => $this->email ?: null,
            'phone'          => $this->phone ?: null,
            'notes'          => $this->notes ?: null,
        ]);

        $this->toastSuccess('Enólogo actualizado correctamente.');
        return $this->roleRedirect('oenologists.index');
    }

    public function render()
    {
        return view('livewire.winery.oenologists.edit')->layout('layouts.app');
    }
}
