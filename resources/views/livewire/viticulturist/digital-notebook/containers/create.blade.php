<x-agro.form-card
    :title="__('Crear Contenedor')"
    :description="__('Registra un nuevo contenedor para una cosecha')"
    :back-url="roleRoute('viticulturist.containers.index')"
>
    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg mb-6">
        <div class="flex items-start gap-3">
            <flux:icon icon="information-circle" variant="solid" class="size-5 text-blue-600 flex-shrink-0 mt-0.5" />
            <div>
                <h4 class="text-sm font-semibold text-blue-900">{{ __('Contenedor Independiente') }}</h4>
                <p class="text-xs text-blue-800 mt-1">
                    {{ __('Este contenedor se creará sin asignar a ninguna cosecha. Podrás asignarlo cuando crees o edites una cosecha.') }}
                </p>
            </div>
        </div>
    </div>

    <form wire:submit="save" class="space-y-8">
        <x-agro.form-section :title="__('Información del Contenedor')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label>{{ __('Nombre del Contenedor') }} *</flux:label>
                    <flux:input wire:model="name" type="text" id="name" :placeholder="__('Ej: Contenedor Principal, Cuba 1, Depósito A')" required />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Número de Serie/Identificador') }}</flux:label>
                    <flux:input wire:model="serial_number" type="text" id="serial_number" :placeholder="__('Ej: CONT-001, SER-12345')" />
                    <flux:error name="serial_number" />
                </flux:field>

                <div class="md:col-span-2">
                    <flux:field>
                        <flux:label>{{ __('Descripción') }}</flux:label>
                        <flux:textarea wire:model="description" id="description" rows="3" :placeholder="__('Descripción adicional del contenedor...')" />
                        <flux:error name="description" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>{{ __('Cantidad') }} *</flux:label>
                    <flux:input wire:model="quantity" type="number" min="1" step="1" id="quantity" placeholder="1" required />
                    <flux:error name="quantity" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Capacidad Total (kg)') }} *</flux:label>
                    <flux:input wire:model="capacity" type="number" step="0.01" min="0.01" id="capacity" placeholder="0.00" required />
                    <flux:description>{{ __('Capacidad máxima que puede almacenar el contenedor') }}</flux:description>
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
            :submit-label="__('Guardar Contenedor')"
        />
    </form>
</x-agro.form-card>
