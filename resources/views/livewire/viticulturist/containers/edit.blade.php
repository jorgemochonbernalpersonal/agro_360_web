@php
    $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>';
@endphp

<x-form-card
    title="Editar Contenedor"
    description="Modifica los datos del contenedor"
    :icon="$icon"
    icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    :back-url="route('viticulturist.digital-notebook.containers.index')"
>
    {{-- Alerta si tiene contenido --}}
    @if($container->used_capacity > 0)
        <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-lg mb-6">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-amber-700">
                        <strong>Atención:</strong> Este contenedor tiene {{ number_format($container->used_capacity, 0) }} L ocupados. 
                        Ten cuidado al modificar la capacidad.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <form wire:submit="save" class="space-y-8">
        <x-form-section title="Información Básica" color="green">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Nombre --}}
                <div>
                    <x-label for="name" required>Nombre</x-label>
                    <x-input 
                        wire:model="name" 
                        type="text" 
                        id="name"
                        :error="$errors->first('name')"
                        required
                    />
                </div>

                {{-- Número de Serie --}}
                <div>
                    <x-label for="serial_number">Número de Serie</x-label>
                    <x-input 
                        wire:model="serial_number" 
                        type="text" 
                        id="serial_number"
                        :error="$errors->first('serial_number')"
                    />
                </div>

                {{-- Cantidad --}}
                <div>
                    <x-label for="quantity">Cantidad</x-label>
                    <x-input 
                        wire:model="quantity" 
                        type="number" 
                        min="1"
                        id="quantity"
                        :error="$errors->first('quantity')"
                    />
                </div>
            </div>

            {{-- Descripción --}}
            <div class="mt-6">
                <x-label for="description">Descripción</x-label>
                <x-textarea 
                    wire:model="description" 
                    id="description"
                    rows="3"
                    :error="$errors->first('description')"
                />
            </div>
        </x-form-section>

        <x-form-section title="Clasificación" color="green">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Tipo --}}
                <div>
                    <x-label for="type_id" required>Tipo de Contenedor</x-label>
                    <x-select 
                        wire:model.live="type_id" 
                        id="type_id"
                        :error="$errors->first('type_id')"
                        required
                    >
                        <option value="">Seleccionar...</option>
                        <option value="1">Barrica</option>
                        <option value="2">Depósito</option>
                        <option value="3">Tanque</option>
                        <option value="4">Tina</option>
                        <option value="5">Ánfora</option>
                    </x-select>
                </div>

                {{-- Material --}}
                <div>
                    <x-label for="material_id" required>Material</x-label>
                    <x-select 
                        wire:model.live="material_id" 
                        id="material_id"
                        :error="$errors->first('material_id')"
                        required
                    >
                        <option value="">Seleccionar...</option>
                        <option value="1">Roble Francés</option>
                        <option value="2">Roble Americano</option>
                        <option value="3">Roble Húngaro</option>
                        <option value="4">Acero Inoxidable</option>
                        <option value="5">Hormigón</option>
                        <option value="6">Cerámica</option>
                        <option value="7">Fibra de Vidrio</option>
                    </x-select>
                </div>

                {{-- Tipo de Roble (solo si material es roble) --}}
                @if(in_array($material_id, [1, 2, 3]))
                    <div>
                        <x-label for="oak_type">Tipo de Roble</x-label>
                        <x-input 
                            wire:model="oak_type" 
                            type="text" 
                            id="oak_type"
                            placeholder="Ej: Quercus Petraea"
                            :error="$errors->first('oak_type')"
                        />
                    </div>

                    {{-- Tipo de Tostado --}}
                    <div>
                        <x-label for="toast_type">Tipo de Tostado</x-label>
                        <x-select 
                            wire:model="toast_type" 
                            id="toast_type"
                            :error="$errors->first('toast_type')"
                        >
                            <option value="">Seleccionar...</option>
                            <option value="light">Ligero</option>
                            <option value="medium">Medio</option>
                            <option value="medium_plus">Medio Plus</option>
                            <option value="heavy">Fuerte</option>
                        </x-select>
                    </div>
                @endif
            </div>
        </x-form-section>

        <x-form-section title="Capacidad" color="green">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Capacidad Total --}}
                <div>
                    <x-label for="capacity" required>Capacidad Total</x-label>
                    <x-input 
                        wire:model="capacity" 
                        type="number" 
                        step="0.01"
                        id="capacity"
                        :error="$errors->first('capacity')"
                        required
                    />
                    <p class="text-xs text-gray-500 mt-1">Ocupado actualmente: {{ number_format($container->used_capacity, 0) }} L</p>
                </div>

                {{-- Unidad de Medida --}}
                <div>
                    <x-label for="unit_of_measurement_id" required>Unidad de Medida</x-label>
                    <x-select 
                        wire:model="unit_of_measurement_id" 
                        id="unit_of_measurement_id"
                        :error="$errors->first('unit_of_measurement_id')"
                        required
                    >
                        <option value="">Seleccionar...</option>
                        @foreach($unitsOfMeasurement as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->symbol }})</option>
                        @endforeach
                    </x-select>
                </div>
            </div>
        </x-form-section>

        <x-form-section title="Ubicación y Gestión" color="green">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Sala/Bodega (solo si hay salas creadas) --}}
                @if($containerRooms->count() > 0)
                    <div>
                        <x-label for="container_room_id">Sala/Bodega</x-label>
                        <x-select 
                            wire:model="container_room_id" 
                            id="container_room_id"
                            :error="$errors->first('container_room_id')"
                        >
                            <option value="">Sin asignar</option>
                            @foreach($containerRooms as $room)
                                <option value="{{ $room->id }}">{{ $room->name }}</option>
                            @endforeach
                        </x-select>
                    </div>
                @endif

                {{-- Proveedor --}}
                <div>
                    <x-label for="supplier_name">Proveedor</x-label>
                    <x-input 
                        wire:model="supplier_name" 
                        type="text" 
                        id="supplier_name"
                        placeholder="Ej: Tonelería Radoux"
                        :error="$errors->first('supplier_name')"
                    />
                </div>

                {{-- Fecha de Compra --}}
                <div>
                    <x-label for="purchase_date">Fecha de Compra</x-label>
                    <x-input 
                        wire:model="purchase_date" 
                        type="date" 
                        id="purchase_date"
                        :error="$errors->first('purchase_date')"
                    />
                </div>

                {{-- Próximo Mantenimiento --}}
                <div>
                    <x-label for="next_maintenance_date">Próximo Mantenimiento</x-label>
                    <x-input 
                        wire:model="next_maintenance_date" 
                        type="date" 
                        id="next_maintenance_date"
                        :error="$errors->first('next_maintenance_date')"
                    />
                </div>
            </div>
        </x-form-section>

        <x-form-actions 
            :cancel-url="route('viticulturist.digital-notebook.containers.show', $container->id)"
            submit-label="Guardar Cambios"
        />
    </form>
</x-form-card>
