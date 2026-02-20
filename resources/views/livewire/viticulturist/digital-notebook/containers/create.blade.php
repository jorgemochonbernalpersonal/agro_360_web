<x-agro.form-card
    title="Crear Contenedor"
    description="Registra un nuevo contenedor para una cosecha"
    :back-url="route('viticulturist.containers.index')"
>
    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg mb-6">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <div>
                <h4 class="text-sm font-semibold text-blue-900">Contenedor Independiente</h4>
                <p class="text-xs text-blue-800 mt-1">
                    Este contenedor se creara sin asignar a ninguna cosecha. Podras asignarlo cuando crees o edites una cosecha.
                </p>
            </div>
        </div>
    </div>

    <form wire:submit="save" class="space-y-8">
        <x-agro.form-section title="Informacion del Contenedor">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label>Nombre del Contenedor *</flux:label>
                    <flux:input wire:model="name" type="text" id="name" placeholder="Ej: Contenedor Principal, Cuba 1, Deposito A" required />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Numero de Serie/Identificador</flux:label>
                    <flux:input wire:model="serial_number" type="text" id="serial_number" placeholder="Ej: CONT-001, SER-12345" />
                    <flux:error name="serial_number" />
                </flux:field>

                <div class="md:col-span-2">
                    <flux:field>
                        <flux:label>Descripcion</flux:label>
                        <flux:textarea wire:model="description" id="description" rows="3" placeholder="Descripcion adicional del contenedor..." />
                        <flux:error name="description" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Cantidad *</flux:label>
                    <flux:input wire:model="quantity" type="number" min="1" step="1" id="quantity" placeholder="1" required />
                    <flux:error name="quantity" />
                </flux:field>

                <flux:field>
                    <flux:label>Capacidad Total (kg) *</flux:label>
                    <flux:input wire:model="capacity" type="number" step="0.01" min="0.01" id="capacity" placeholder="0.00" required />
                    <flux:description>Capacidad maxima que puede almacenar el contenedor</flux:description>
                    <flux:error name="capacity" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Fechas">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label>Fecha de Compra/Adquisicion</flux:label>
                    <flux:input wire:model="purchase_date" type="date" id="purchase_date" />
                    <flux:error name="purchase_date" />
                </flux:field>

                <flux:field>
                    <flux:label>Proximo Mantenimiento</flux:label>
                    <flux:input wire:model="next_maintenance_date" type="date" id="next_maintenance_date" />
                    <flux:error name="next_maintenance_date" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="route('viticulturist.containers.index')"
            submit-label="Guardar Contenedor"
        />
    </form>
</x-agro.form-card>
