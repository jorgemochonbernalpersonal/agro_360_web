<x-agro.form-card
    title="Nuevo Contenedor"
    description="Registra un depósito, barrica u otro contenedor de tu bodega"
    :back-url="roleRoute('containers.index')"
>
    <form wire:submit="save" class="space-y-8">
        <x-agro.form-section title="Datos del Contenedor">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <flux:field>
                    <flux:label>Nombre</flux:label>
                    <flux:input wire:model="name" placeholder="Ej: Depósito 1, Barrica A-01" />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Tipo</flux:label>
                    <flux:select wire:model="type_id">
                        <flux:select.option value="">Selecciona un tipo...</flux:select.option>
                        @foreach($types as $type)
                            <flux:select.option value="{{ $type->id }}">{{ $type->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="type_id" />
                </flux:field>

                <flux:field>
                    <flux:label>Unidad de capacidad</flux:label>
                    <flux:select wire:model.live="unit">
                        <flux:select.option value="kg">Kilogramos (kg) — uva / mosto</flux:select.option>
                        <flux:select.option value="litros">Litros (L) — vino elaborado</flux:select.option>
                    </flux:select>
                    <flux:description>Elige kg para depósitos de vendimia, litros para crianza y embotellado.</flux:description>
                    <flux:error name="unit" />
                </flux:field>

                <flux:field>
                    <flux:label>Capacidad ({{ $unit === 'litros' ? 'L' : 'kg' }})</flux:label>
                    <flux:input wire:model="capacity" type="number" step="0.01" min="1" placeholder="Ej: 5000" />
                    <flux:description>Capacidad total en {{ $unit === 'litros' ? 'litros' : 'kilogramos' }}.</flux:description>
                    <flux:error name="capacity" />
                </flux:field>

                <flux:field>
                    <flux:label>Sala de bodega</flux:label>
                    <flux:select wire:model="container_room_id">
                        <flux:select.option value="">Sin sala asignada</flux:select.option>
                        @foreach($rooms as $room)
                            <flux:select.option value="{{ $room->id }}">{{ $room->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:description>Opcional. Agrupa el contenedor en una zona de tu bodega.</flux:description>
                    <flux:error name="container_room_id" />
                </flux:field>

                <flux:field>
                    <flux:label>Número de serie</flux:label>
                    <flux:input wire:model="serial_number" placeholder="Opcional" />
                    <flux:error name="serial_number" />
                </flux:field>

                <flux:field>
                    <flux:label>Fecha de compra</flux:label>
                    <flux:input wire:model="purchase_date" type="date" />
                    <flux:error name="purchase_date" />
                </flux:field>

                <flux:field>
                    <flux:label>Proveedor</flux:label>
                    <flux:input wire:model="supplier_name" placeholder="Nombre del proveedor (opcional)" />
                    <flux:error name="supplier_name" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>Descripción / Notas</flux:label>
                    <flux:textarea wire:model="description" rows="3" placeholder="Observaciones sobre este contenedor..." />
                    <flux:error name="description" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('containers.index')"
            submit-label="Crear Contenedor"
        />
    </form>
</x-agro.form-card>
