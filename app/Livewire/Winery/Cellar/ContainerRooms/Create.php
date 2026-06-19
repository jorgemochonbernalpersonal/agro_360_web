<?php

namespace App\Livewire\Winery\Cellar\ContainerRooms;

use App\Livewire\Winery\AbstractCreate;
use App\Models\ContainerRoom;

class Create extends AbstractCreate
{
    public string $name = '';

    public string $description = '';

    public string $capacity = '';

    public string $temperature = '';

    public string $humidity = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'temperature' => ['nullable', 'numeric', 'min:-20', 'max:50'],
            'humidity' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    protected function performCreate(): void
    {
        ContainerRoom::create([
            'user_id' => $this->ownerId(),
            'name' => $this->name,
            'description' => $this->description ?: null,
            'capacity' => $this->capacity ?: null,
            'temperature' => $this->temperature ?: null,
            'humidity' => $this->humidity ?: null,
        ]);
    }

    protected function successMessage(): string
    {
        return __('Sala de bodega creada correctamente.');
    }

    protected function indexRoute(): string
    {
        return 'winery.container-rooms.index';
    }
}
