<x-agro.form-card
    title="Editar Almacen"
    description="Modifica la informacion del almacen"
    :back-url="route('viticulturist.almacen.index', ['tab' => 'almacenes'])"
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

                <div class="flex items-center gap-3">
                    <input
                        wire:model="active"
                        type="checkbox"
                        id="active"
                        class="w-4 h-4 text-agro-700 border-zinc-300 rounded focus:ring-agro-700"
                    >
                    <label for="active" class="text-sm font-semibold text-zinc-700">Almacen activo</label>
                    <p class="text-xs text-zinc-500">Los almacenes inactivos no apareceran en los filtros al registrar stock</p>
                </div>
            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="route('viticulturist.almacen.index', ['tab' => 'almacenes'])"
            submit-label="Guardar Cambios"
        />
    </form>
</x-agro.form-card>
