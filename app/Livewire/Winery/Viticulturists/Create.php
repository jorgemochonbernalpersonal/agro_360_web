<?php

namespace App\Livewire\Winery\Viticulturists;

use App\Livewire\Winery\AbstractCreate;
use App\Services\ViticulturistOnboardingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class Create extends AbstractCreate
{
    public string $name = '';

    public string $dni = '';

    public string $email = '';

    public string $phone = '';

    public string $notes = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'dni' => ['nullable', 'string', 'max:20', Rule::unique('users', 'dni')->where('can_login', true)],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => __('El nombre es obligatorio.'),
            'dni.unique' => __('Ya existe un usuario con este DNI.'),
            'email.unique' => __('Ya existe un usuario con este email.'),
        ];
    }

    protected function performCreate(): void
    {
        app(ViticulturistOnboardingService::class)->create($this->wineryId(), Auth::user(), [
            'name' => $this->name,
            'email' => $this->email,
            'dni' => $this->dni,
            'phone' => $this->phone,
            'notes' => $this->notes,
        ]);
    }

    protected function successMessage(): string
    {
        return $this->email
            ? __('Viticultor creado e invitación enviada correctamente.')
            : __('Viticultor creado. Recuerda enviarle una invitación desde su perfil cuando tengas su email.');
    }

    protected function indexRoute(): string
    {
        return 'winery.viticulturists.index';
    }
}
