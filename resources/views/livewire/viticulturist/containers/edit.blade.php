<x-agro.form-card
    title="Editar Contenedor"
    description="Modifica los datos del contenedor"
    :back-url="roleRoute('viticulturist.containers.index')"
>
    @if($container->used_capacity > 0)
        <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-lg mb-6">
            <div class="flex items-start gap-3">
                <flux:icon icon="exclamation-triangle" variant="solid" class="size-5 text-amber-500 flex-shrink-0 mt-0.5" />
                <p class="text-sm text-amber-700">
                    <strong>Atencion:</strong> Este contenedor tiene {{ number_format($container->used_capacity, 0) }} L ocupados.
                    Ten cuidado al modificar la capacidad.
                </p>
            </div>
        </div>
    @endif

    <form wire:submit="save" class="space-y-8">
        <x-agro.form-section title="Informacion Basica">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <flux:field>
                    <flux:label>Nombre *</flux:label>
                    <flux:input wire:model="name" type="text" id="name" required />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Numero de Serie</flux:label>
                    <flux:input wire:model="serial_number" type="text" id="serial_number" />
                    <flux:error name="serial_number" />
                </flux:field>

                <flux:field>
                    <flux:label>Cantidad</flux:label>
                    <flux:input wire:model="quantity" type="number" min="1" id="quantity" />
                    <flux:error name="quantity" />
                </flux:field>
            </div>

            <div class="mt-6">
                <flux:field>
                    <flux:label>Descripcion</flux:label>
                    <flux:textarea wire:model="description" id="description" rows="3" />
                    <flux:error name="description" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Clasificacion">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label>Tipo de Contenedor *</flux:label>
                    <flux:select wire:model.live="type_id" id="type_id" required>
                        <option value="">Seleccionar...</option>
                        @foreach($containerTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="type_id" />
                </flux:field>

                <flux:field>
                    <flux:label>Material *</flux:label>
                    <flux:select wire:model.live="material_id" id="material_id" required>
                        <option value="">Seleccionar...</option>
                        @foreach($containerMaterials as $material)
                            <option value="{{ $material->id }}">{{ $material->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="material_id" />
                </flux:field>

                @if(in_array($material_id, [1, 2, 3]))
                    <flux:field>
                        <flux:label>Tipo de Roble</flux:label>
                        <flux:input wire:model="oak_type" type="text" id="oak_type" placeholder="Ej: Quercus Petraea" />
                        <flux:error name="oak_type" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Tipo de Tostado</flux:label>
                        <flux:select wire:model="toast_type" id="toast_type">
                            <option value="">Seleccionar...</option>
                            <option value="light">Ligero</option>
                            <option value="medium">Medio</option>
                            <option value="medium_plus">Medio Plus</option>
                            <option value="heavy">Fuerte</option>
                        </flux:select>
                        <flux:error name="toast_type" />
                    </flux:field>
                @endif
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Capacidad">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label>Capacidad Total *</flux:label>
                    <flux:input wire:model="capacity" type="number" step="0.01" id="capacity" required />
                    <flux:description>Ocupado actualmente: {{ number_format($container->used_capacity, 0) }} L</flux:description>
                    <flux:error name="capacity" />
                </flux:field>

                <flux:field>
                    <flux:label>Unidad de Medida *</flux:label>
                    <flux:select wire:model="unit_of_measurement_id" id="unit_of_measurement_id" required>
                        <option value="">Seleccionar...</option>
                        @foreach($unitsOfMeasurement as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->symbol }})</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="unit_of_measurement_id" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Ubicacion y Gestion">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @if($containerRooms->count() > 0)
                    <flux:field>
                        <flux:label>Sala/Bodega</flux:label>
                        <flux:select wire:model="container_room_id" id="container_room_id">
                            <option value="">Sin asignar</option>
                            @foreach($containerRooms as $room)
                                <option value="{{ $room->id }}">{{ $room->name }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="container_room_id" />
                    </flux:field>
                @endif

                <flux:field>
                    <flux:label>Proveedor</flux:label>
                    <flux:input wire:model="supplier_name" type="text" id="supplier_name" placeholder="Ej: Toneleria Radoux" />
                    <flux:error name="supplier_name" />
                </flux:field>

                <flux:field>
                    <flux:label>Fecha de Compra</flux:label>
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
            :cancel-url="roleRoute('viticulturist.containers.show', $container->id)"
            submit-label="Guardar Cambios"
        />
    </form>
</x-agro.form-card>
