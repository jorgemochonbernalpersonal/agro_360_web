<x-agro.form-card
    title="Editar Contenedor"
    description="Modifica la informacion del contenedor"
    :back-url="route('viticulturist.containers.index')"
>
    @if($container && $container->getCurrentHarvest())
        @php $currentHarvest = $container->getCurrentHarvest(); @endphp
        <div class="mb-6 p-4 bg-blue-50 border-l-4 border-blue-500 rounded-r-lg">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <h4 class="text-sm font-semibold text-blue-900">Contenedor Asignado a Cosecha</h4>
                    <p class="text-xs text-blue-800 mt-1">
                        Parcela: <strong>{{ $currentHarvest->activity->plot->name ?? 'Sin parcela' }}</strong> |
                        Variedad: <strong>{{ $currentHarvest->plotPlanting->grapeVariety->name ?? 'Sin variedad' }}</strong> |
                        Fecha: <strong>{{ $currentHarvest->harvest_start_date ? $currentHarvest->harvest_start_date->format('d/m/Y') : 'Sin fecha' }}</strong>
                    </p>
                </div>
            </div>
        </div>
    @elseif($container)
        <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-r-lg">
            <p class="text-sm text-green-800">Este contenedor no esta asignado a ninguna cosecha. Puedes asignarlo cuando crees o edites una cosecha.</p>
        </div>
    @endif

    <form wire:submit="update" class="space-y-8">
        <x-agro.form-section title="Informacion del Contenedor">
            @if($container->used_capacity > 0)
                <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="text-sm text-yellow-800">
                        <strong>Capacidad usada:</strong> {{ number_format($container->used_capacity, 2) }} kg
                        ({{ number_format($container->getOccupancyPercentage(), 1) }}% ocupado).
                        La capacidad no puede ser menor que la usada.
                    </p>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label>Nombre del Contenedor *</flux:label>
                    <flux:input wire:model="name" type="text" id="name" placeholder="Ej: Contenedor Principal" required />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Numero de Serie/Identificador</flux:label>
                    <flux:input wire:model="serial_number" type="text" id="serial_number" placeholder="Ej: CONT-001" />
                    <flux:error name="serial_number" />
                </flux:field>

                <div class="md:col-span-2">
                    <flux:field>
                        <flux:label>Descripcion</flux:label>
                        <flux:textarea wire:model="description" id="description" rows="3" />
                        <flux:error name="description" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Cantidad *</flux:label>
                    <flux:input wire:model="quantity" type="number" min="1" step="1" id="quantity" required />
                    <flux:error name="quantity" />
                </flux:field>

                <flux:field>
                    <flux:label>Capacidad Total (kg) *</flux:label>
                    <flux:input wire:model="capacity" type="number" step="0.01" min="0.01" id="capacity" required />
                    <flux:description>Capacidad maxima del contenedor</flux:description>
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
            submit-label="Guardar Cambios"
        />
    </form>
</x-agro.form-card>
