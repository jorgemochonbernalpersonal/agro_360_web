<x-agro.form-card
    title="Nueva Sala de Bodega"
    description="Define una zona o sala para organizar tus depósitos."
    icon="building-office"
    icon-color="from-teal-500 to-teal-700"
    :back-url="roleRoute('container-rooms.index')"
>
    <form wire:submit="save" class="space-y-8">
        <x-agro.form-section title="Datos de la sala" color="teal">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field class="md:col-span-2">
                    <flux:label required>Nombre</flux:label>
                    <flux:input wire:model="name" type="text" placeholder="Ej. Nave de fermentación, Sala de crianza..." required />
                    <flux:error name="name" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>Descripción</flux:label>
                    <flux:input wire:model="description" type="text" placeholder="Descripción breve..." />
                    <flux:error name="description" />
                </flux:field>

                <flux:field>
                    <flux:label>Capacidad (número de depósitos)</flux:label>
                    <flux:input wire:model="capacity" type="number" min="1" step="1" placeholder="0" />
                    <flux:description>Informativo. El sistema no limita los depósitos asignados a esta sala.</flux:description>
                    <flux:error name="capacity" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Condiciones ambientales" color="blue">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label>Temperatura objetivo (°C)</flux:label>
                    <flux:input wire:model="temperature" type="number" step="0.1" min="-20" max="50" placeholder="Ej. 14.5" />
                    <flux:error name="temperature" />
                </flux:field>

                <flux:field>
                    <flux:label>Humedad relativa objetivo (%)</flux:label>
                    <flux:input wire:model="humidity" type="number" step="0.1" min="0" max="100" placeholder="Ej. 75" />
                    <flux:error name="humidity" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-actions :back-url="roleRoute('container-rooms.index')" submit-label="Crear sala" />
    </form>
</x-agro.form-card>
