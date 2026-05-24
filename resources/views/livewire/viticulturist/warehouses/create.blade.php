<x-agro.form-card
    :title="__('Nuevo Almacén')"
    :description="__('Registra un nuevo almacén o ubicación de almacenamiento')"
    :back-url="roleRoute('viticulturist.warehouse.index', ['tab' => 'almacenes'])"
>
    <form wire:submit="save" class="space-y-6">

        <x-agro.form-section :title="__('Información del Almacén')">
            <div class="grid grid-cols-1 gap-6">
                <flux:field>
                    <flux:label required>{{ __('Nombre') }}</flux:label>
                    <flux:input wire:model="name" type="text" :placeholder="__('Ej: Almacén Principal, Nave Norte...')" required />
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
            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('viticulturist.warehouse.index', ['tab' => 'almacenes'])"
            :submit-label="__('Crear Almacén')"
        />
    </form>
</x-agro.form-card>
