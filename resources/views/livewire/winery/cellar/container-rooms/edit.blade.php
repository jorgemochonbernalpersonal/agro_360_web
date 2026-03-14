<x-agro.form-card
    title="Editar Sala — {{ $room->name }}"
    description="Modifica los datos de la sala de bodega."
    icon="building-office"
    icon-color="from-teal-500 to-teal-700"
    :back-url="route('winery.container-rooms.index')"
>
    <form wire:submit="save" class="space-y-8">
        <x-agro.form-section title="Datos de la sala" color="teal">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field class="md:col-span-2">
                    <flux:label required>Nombre</flux:label>
                    <flux:input wire:model="name" type="text" required />
                    <flux:error name="name" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>Descripción</flux:label>
                    <flux:input wire:model="description" type="text" />
                    <flux:error name="description" />
                </flux:field>

                <flux:field>
                    <flux:label>Capacidad (número de depósitos)</flux:label>
                    <flux:input wire:model="capacity" type="number" min="1" step="1" />
                    <flux:error name="capacity" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Condiciones ambientales" color="blue">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label>Temperatura objetivo (°C)</flux:label>
                    <flux:input wire:model="temperature" type="number" step="0.1" min="-20" max="50" />
                    <flux:error name="temperature" />
                </flux:field>

                <flux:field>
                    <flux:label>Humedad relativa objetivo (%)</flux:label>
                    <flux:input wire:model="humidity" type="number" step="0.1" min="0" max="100" />
                    <flux:error name="humidity" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-actions :back-url="route('winery.container-rooms.index')" submit-label="Guardar cambios" />
    </form>
</x-agro.form-card>
