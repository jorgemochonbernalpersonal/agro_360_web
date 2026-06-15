<x-agro.form-card
    :title="__('Editar Contenedor')"
    :description="__('Modifica la información del contenedor')"
    :back-url="roleRoute('viticulturist.containers.index')"
>
    @if($container && $container->getCurrentHarvest())
        @php $currentHarvest = $container->getCurrentHarvest(); @endphp
        <div class="mb-6 p-4 bg-blue-50 border-l-4 border-blue-500 rounded-r-lg">
            <div class="flex items-start gap-3">
                <flux:icon icon="information-circle" variant="solid" class="size-5 text-blue-600 flex-shrink-0 mt-0.5" />
                <div>
                    <h4 class="text-sm font-semibold text-blue-900">{{ __('Contenedor Asignado a Cosecha') }}</h4>
                    <p class="text-xs text-blue-800 mt-1">
                        {{ __('Parcela:') }} <strong>{{ $currentHarvest->activity->plot->name ?? __('Sin parcela') }}</strong> |
                        {{ __('Variedad:') }} <strong>{{ $currentHarvest->plotPlanting->grapeVariety->name ?? __('Sin variedad') }}</strong> |
                        {{ __('Fecha:') }} <strong>{{ $currentHarvest->harvest_start_date ? $currentHarvest->harvest_start_date->format('d/m/Y') : __('Sin fecha') }}</strong>
                    </p>
                </div>
            </div>
        </div>
    @elseif($container)
        <div class="mb-6 p-4 bg-agro-50 border-l-4 border-agro-500 rounded-r-lg">
            <p class="text-sm text-agro-800">{{ __('Este contenedor no está asignado a ninguna cosecha. Puedes asignarlo cuando crees o edites una cosecha.') }}</p>
        </div>
    @endif

    <form wire:submit="update" class="space-y-8">
        <x-agro.form-section :title="__('Información del Contenedor')">
            @if($container->used_capacity > 0)
                <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="text-sm text-yellow-800">
                        <strong>{{ __('Capacidad usada:') }}</strong> {{ number_format($container->used_capacity, 2) }} kg
                        ({{ number_format($container->getOccupancyPercentage(), 1) }}% {{ __('ocupado') }}).
                        {{ __('La capacidad no puede ser menor que la usada.') }}
                    </p>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label>{{ __('Nombre del Contenedor') }} *</flux:label>
                    <flux:input wire:model="name" type="text" id="name" :placeholder="__('Ej: Contenedor Principal')" required />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Número de Serie/Identificador') }}</flux:label>
                    <flux:input wire:model="serial_number" type="text" id="serial_number" :placeholder="__('Ej: CONT-001')" />
                    <flux:error name="serial_number" />
                </flux:field>

                <div class="md:col-span-2">
                    <flux:field>
                        <flux:label>{{ __('Descripción') }}</flux:label>
                        <flux:textarea wire:model="description" id="description" rows="3" />
                        <flux:error name="description" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>{{ __('Cantidad') }} *</flux:label>
                    <flux:input wire:model="quantity" type="number" min="1" step="1" id="quantity" required />
                    <flux:error name="quantity" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Capacidad Total (kg)') }} *</flux:label>
                    <flux:input wire:model="capacity" type="number" step="0.01" min="0.01" id="capacity" required />
                    <flux:description>{{ __('Capacidad máxima del contenedor') }}</flux:description>
                    <flux:error name="capacity" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section :title="__('Fechas')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label>{{ __('Fecha de Compra/Adquisición') }}</flux:label>
                    <flux:input wire:model="purchase_date" type="date" id="purchase_date" />
                    <flux:error name="purchase_date" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Próximo Mantenimiento') }}</flux:label>
                    <flux:input wire:model="next_maintenance_date" type="date" id="next_maintenance_date" />
                    <flux:error name="next_maintenance_date" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('viticulturist.containers.index')"
            :submit-label="__('Guardar Cambios')"
        />
    </form>
</x-agro.form-card>
