<?php

namespace App\Livewire\Winery\Cellar\ContainerRooms;

use App\Livewire\Winery\AbstractEdit;
use App\Models\ContainerRoom;

class Edit extends AbstractEdit
{
    public ContainerRoom $room;

    public string $name = '';

    public string $description = '';

    public string $capacity = '';

    public string $temperature = '';

    public string $humidity = '';

    public function mount(ContainerRoom $room): void
    {
        $this->authorize('update', $room);

        $this->room = $room;
        $this->name = $room->name;
        $this->description = $room->description ?? '';
        $this->capacity = (string) ($room->capacity ?? '');
        $this->temperature = (string) ($room->temperature ?? '');
        $this->humidity = (string) ($room->humidity ?? '');
    }

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

    protected function performUpdate(): void
    {
        $this->room->update([
            'name' => $this->name,
            'description' => $this->description ?: null,
            'capacity' => $this->capacity ?: null,
            'temperature' => $this->temperature ?: null,
            'humidity' => $this->humidity ?: null,
        ]);
    }

    protected function successMessage(): string
    {
        return __('Sala actualizada correctamente.');
    }

    protected function indexRoute(): string
    {
        return 'winery.container-rooms.index';
    }
}
