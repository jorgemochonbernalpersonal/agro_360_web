<x-agro.form-card
    :title="__('Editar Almacén')"
    :description="__('Actualiza los datos de este almacén')"
    :back-url="roleRoute('viticulturist.warehouse.index', ['tab' => 'almacenes'])"
>
    <form wire:submit="save" class="space-y-6">

        <x-agro.form-section :title="__('Información del Almacén')">
            <div class="grid grid-cols-1 gap-6">
                <flux:field>
                    <flux:label required>{{ __('Nombre') }}</flux:label>
                    <flux:input wire:model="name" type="text" required />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Ubicación') }}</flux:label>
                    <flux:input wire:model="location" type="text" :placeholder="__('Ej: Nave A, Planta Baja, Sala 3...')" />
                    <flux:error name="location" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Descripción') }}</flux:label>
                    <flux:textarea wire:model="description" rows="3" :placeholder="__('Información adicional sobre este almacén...')" />
                    <flux:error name="description" />
                </flux:field>

                <div class="flex items-center gap-3">
                    <input wire:model="active" type="checkbox" id="active"
                        class="w-4 h-4 text-agro-700 border-zinc-300 rounded focus:ring-agro-700">
                    <label for="active" class="text-sm font-semibold text-zinc-700">{{ __('Almacén activo') }}</label>
                    <p class="text-xs text-zinc-500">{{ __('Los almacenes inactivos no aparecerán en los filtros al registrar stock') }}</p>
                </div>
            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('viticulturist.warehouse.index', ['tab' => 'almacenes'])"
            :submit-label="__('Guardar Cambios')"
        />
    </form>
</x-agro.form-card>
