<?php

namespace App\Livewire\Viticulturist\Viticulturists;

use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Edit extends Component
{
    use WithRoleAwareRedirect, WithToastNotifications;

    public User $viticulturist;

    public string $name  = '';
    public string $email = '';

    public function mount(User $viticulturist): void
    {
        // Authorization: the target must have been created by the current user
        $owned = WineryViticulturist::where('viticulturist_id', $viticulturist->id)
            ->where('parent_viticulturist_id', Auth::id())
            ->exists();

        abort_unless($owned, 403);

        $this->viticulturist = $viticulturist;
        $this->name          = $viticulturist->name;
        $this->email         = $viticulturist->email ?? '';
    }

    protected function rules(): array
    {
        return [
            'name'  => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($this->viticulturist->id),
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required'  => 'El nombre es obligatorio.',
            'email.required' => 'El email es obligatorio.',
            'email.email'    => 'El email debe ser una dirección válida.',
            'email.unique'   => 'Este email ya está en uso por otra cuenta.',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $this->viticulturist->update([
            'name'  => $this->name,
            'email' => $this->email,
        ]);

        $this->toastSuccess('Viticultor actualizado correctamente.');
        $this->viticulturistRoleRedirect('personal.index', ['viewMode' => 'personal']);
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.viticulturist.viticulturists.edit');
    }
}
