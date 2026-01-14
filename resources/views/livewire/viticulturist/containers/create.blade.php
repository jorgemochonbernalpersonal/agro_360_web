@php
    $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>';
@endphp

<div>
<x-form-card
    title="Nuevo Contenedor"
    description="Registra un nuevo contenedor para tu bodega"
    :icon="$icon"
    icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    :back-url="route('viticulturist.digital-notebook.containers.index')"
>
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
                        placeholder="Ej: Barrica #1"
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
                        placeholder="Ej: BR-2024-001"
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
                        placeholder="1"
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
                    placeholder="Descripción opcional del contenedor"
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
                        @foreach($containerTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
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
                        @foreach($containerMaterials as $material)
                            <option value="{{ $material->id }}">{{ $material->name }}</option>
                        @endforeach
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
                        placeholder="225"
                        :error="$errors->first('capacity')"
                        required
                    />
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
                @else
                    {{-- Botón para crear sala --}}
                    <div>
                        <x-label>Sala/Bodega</x-label>
                        <button type="button" 
                                onclick="document.getElementById('createRoomModal').classList.remove('hidden')"
                                class="w-full px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg transition flex items-center justify-center gap-2">
                            <span>➕</span>
                            <span>Crear Primera Sala/Bodega</span>
                        </button>
                        <p class="text-xs text-gray-500 mt-1">Crea una sala para organizar tus contenedores</p>
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
            :cancel-url="route('viticulturist.digital-notebook.containers.index')"
            submit-label="Crear Contenedor"
        />
    </form>
</x-form-card>

{{-- Modal para crear sala/bodega --}}
<div id="createRoomModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Nueva Sala/Bodega</h3>
            <button type="button" 
                    onclick="document.getElementById('createRoomModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <form wire:submit.prevent="createRoom" class="space-y-4">
            <div>
                <x-label for="room_name" required>Nombre de la Sala</x-label>
                <x-input 
                    wire:model="room_name" 
                    type="text" 
                    id="room_name"
                    placeholder="Ej: Bodega Principal"
                    required
                />
            </div>
            
            <div>
                <x-label for="room_description">Descripción</x-label>
                <x-textarea 
                    wire:model="room_description" 
                    id="room_description"
                    rows="2"
                    placeholder="Descripción opcional"
                />
            </div>
            
            <div class="flex gap-3 pt-4">
                <button type="button" 
                        onclick="document.getElementById('createRoomModal').classList.add('hidden')"
                        class="flex-1 px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition">
                    Cancelar
                </button>
                <button type="submit" 
                        class="flex-1 px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg transition">
                    Crear Sala
                </button>
            </div>
        </form>
    </div>
</div>

@script
<script>
    $wire.on('close-modal', () => {
        document.getElementById('createRoomModal').classList.add('hidden');
    });
</script>
@endscript
</div>