<x-agro.form-card
    :title="__('Editar Contenedor')"
    :description="__('Modifica los datos del contenedor')"
    :back-url="roleRoute('viticulturist.containers.index')"
>
    @if($container->used_capacity > 0)
        <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-lg mb-6">
            <div class="flex items-start gap-3">
                <flux:icon icon="exclamation-triangle" variant="solid" class="size-5 text-amber-500 flex-shrink-0 mt-0.5" />
                <p class="text-sm text-amber-700">
                    <strong>{{ __('Atención') }}:</strong> {{ __('Este contenedor tiene :liters L ocupados. Ten cuidado al modificar la capacidad.', ['liters' => number_format($container->used_capacity, 0)]) }}
                </p>
            </div>
        </div>
    @endif

    <form wire:submit="save" class="space-y-8">
        <x-agro.form-section :title="__('Información Básica')">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <flux:field>
                    <flux:label>{{ __('Nombre') }} *</flux:label>
                    <flux:input wire:model="name" type="text" id="name" required />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Número de Serie') }}</flux:label>
                    <flux:input wire:model="serial_number" type="text" id="serial_number" />
                    <flux:error name="serial_number" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Cantidad') }}</flux:label>
                    <flux:input wire:model="quantity" type="number" min="1" id="quantity" />
                    <flux:error name="quantity" />
                </flux:field>
            </div>

            <div class="mt-6">
                <flux:field>
                    <flux:label>{{ __('Descripción') }}</flux:label>
                    <flux:textarea wire:model="description" id="description" rows="3" />
                    <flux:error name="description" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section :title="__('Clasificación')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label>{{ __('Tipo de Contenedor') }} *</flux:label>
                    <flux:select wire:model.live="type_id" id="type_id" required>
                        <option value="">{{ __('Seleccionar...') }}</option>
                        @foreach($containerTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="type_id" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Material') }} *</flux:label>
                    <flux:select wire:model.live="material_id" id="material_id" required>
                        <option value="">{{ __('Seleccionar...') }}</option>
                        @foreach($containerMaterials as $material)
                            <option value="{{ $material->id }}">{{ $material->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="material_id" />
                </flux:field>

                @if(in_array($material_id, [1, 2, 3]))
                    <flux:field>
                        <flux:label>{{ __('Tipo de Roble') }}</flux:label>
                        <flux:input wire:model="oak_type" type="text" id="oak_type" :placeholder="__('Ej: Quercus Petraea')" />
                        <flux:error name="oak_type" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Tipo de Tostado') }}</flux:label>
                        <flux:select wire:model="toast_type" id="toast_type">
                            <option value="">{{ __('Seleccionar...') }}</option>
                            <option value="light">{{ __('Ligero') }}</option>
                            <option value="medium">{{ __('Medio') }}</option>
                            <option value="medium_plus">{{ __('Medio Plus') }}</option>
                            <option value="heavy">{{ __('Fuerte') }}</option>
                        </flux:select>
                        <flux:error name="toast_type" />
                    </flux:field>
                @endif
            </div>
        </x-agro.form-section>

        <x-agro.form-section :title="__('Capacidad')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label>{{ __('Capacidad Total') }} *</flux:label>
                    <flux:input wire:model="capacity" type="number" step="0.01" id="capacity" required />
                    <flux:description>{{ __('Ocupado actualmente: :liters L', ['liters' => number_format($container->used_capacity, 0)]) }}</flux:description>
                    <flux:error name="capacity" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Unidad de Medida') }} *</flux:label>
                    <flux:select wire:model="unit_of_measurement_id" id="unit_of_measurement_id" required>
                        <option value="">{{ __('Seleccionar...') }}</option>
                        @foreach($unitsOfMeasurement as $unit)
                            <option value="{{ $unit->id }}">{{ __($unit->name) }} ({{ $unit->symbol }})</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="unit_of_measurement_id" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section :title="__('Ubicación y Gestión')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @if($containerRooms->count() > 0)
                    <flux:field>
                        <flux:label>{{ __('Sala/Bodega') }}</flux:label>
                        <flux:select wire:model="container_room_id" id="container_room_id">
                            <option value="">{{ __('Sin asignar') }}</option>
                            @foreach($containerRooms as $room)
                                <option value="{{ $room->id }}">{{ $room->name }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="container_room_id" />
                    </flux:field>
                @endif

                <flux:field>
                    <flux:label>{{ __('Proveedor') }}</flux:label>
                    <flux:input wire:model="supplier_name" type="text" id="supplier_name" :placeholder="__('Ej: Tonelería Radoux')" />
                    <flux:error name="supplier_name" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Fecha de Compra') }}</flux:label>
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
            :cancel-url="roleRoute('viticulturist.containers.show', $container->id)"
            :submit-label="__('Guardar Cambios')"
        />
    </form>
</x-agro.form-card>
