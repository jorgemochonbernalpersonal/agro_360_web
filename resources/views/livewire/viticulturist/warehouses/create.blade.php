<x-agro.form-card
    title="Crear Almacen"
    description="Registra un nuevo almacen o ubicacion para organizar tus productos fitosanitarios"
    :back-url="route('viticulturist.warehouses.index')"
>
    <form wire:submit="save" class="space-y-8">
        <x-agro.form-section title="Informacion del Almacen">
            <div class="grid grid-cols-1 gap-6">
                <flux:field>
                    <flux:label>Nombre del Almacen *</flux:label>
                    <flux:input
                        wire:model="name"
                        type="text"
                        id="name"
                        placeholder="Ej: Almacen Principal, Cobertizo Norte..."
                        required
                    />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Ubicacion</flux:label>
                    <flux:input
                        wire:model="location"
                        type="text"
                        id="location"
                        placeholder="Ej: Edificio A, Planta Baja, Sala 3..."
                    />
                    <flux:error name="location" />
                </flux:field>

                <flux:field>
                    <flux:label>Descripcion</flux:label>
                    <flux:textarea
                        wire:model="description"
                        id="description"
                        rows="3"
                        placeholder="Informacion adicional sobre este almacen..."
                    />
                    <flux:error name="description" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="route('viticulturist.warehouses.index')"
            submit-label="Crear Almacen"
        />
    </form>
</x-agro.form-card>
