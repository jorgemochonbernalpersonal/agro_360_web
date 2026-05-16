<div>
<x-agro.form-card
    title="Nuevo Contenedor"
    description="Registra un nuevo contenedor para tu bodega"
    :back-url="roleRoute('viticulturist.containers.index')"
>
    <form wire:submit="save" class="space-y-8">
        <x-agro.form-section title="Informacion Basica">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <flux:field>
                    <flux:label>Nombre *</flux:label>
                    <flux:input wire:model="name" type="text" id="name" placeholder="Ej: Barrica #1" required />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Numero de Serie</flux:label>
                    <flux:input wire:model="serial_number" type="text" id="serial_number" placeholder="Ej: BR-2024-001" />
                    <flux:error name="serial_number" />
                </flux:field>

                <flux:field>
                    <flux:label>Cantidad</flux:label>
                    <flux:input wire:model="quantity" type="number" min="1" id="quantity" placeholder="1" />
                    <flux:error name="quantity" />
                </flux:field>
            </div>

            <div class="mt-6">
                <flux:field>
                    <flux:label>Descripcion</flux:label>
                    <flux:textarea wire:model="description" id="description" rows="3" placeholder="Descripcion opcional del contenedor" />
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
                    <flux:input wire:model="capacity" type="number" step="0.01" id="capacity" placeholder="225" required />
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
                @else
                    <div>
                        <p class="text-sm font-semibold text-zinc-700 mb-2">Sala/Bodega</p>
                        <flux:button
                            type="button"
                            variant="ghost"
                            icon="plus"
                            onclick="document.getElementById('createRoomModal').classList.remove('hidden')"
                        >
                            Crear Primera Sala/Bodega
                        </flux:button>
                        <p class="text-xs text-zinc-500 mt-1">Crea una sala para organizar tus contenedores</p>
                    </div>
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
            :cancel-url="roleRoute('viticulturist.containers.index')"
            submit-label="Crear Contenedor"
        />
    </form>
</x-agro.form-card>

{{-- Modal para crear sala/bodega --}}
<div id="createRoomModal" class="hidden fixed inset-0 bg-zinc-900 bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-zinc-900">Nueva Sala/Bodega</h3>
            <button type="button"
                    onclick="document.getElementById('createRoomModal').classList.add('hidden')"
                    class="text-zinc-400 hover:text-zinc-600">
                <flux:icon icon="x-mark" class="size-6" />
            </button>
        </div>
        <form wire:submit.prevent="createRoom" class="space-y-4">
            <flux:field>
                <flux:label>Nombre de la Sala *</flux:label>
                <flux:input wire:model="room_name" type="text" id="room_name" placeholder="Ej: Bodega Principal" required />
            </flux:field>
            <flux:field>
                <flux:label>Descripcion</flux:label>
                <flux:textarea wire:model="room_description" id="room_description" rows="2" placeholder="Descripcion opcional" />
            </flux:field>
            <div class="flex gap-3 pt-4">
                <flux:button type="button" variant="ghost" class="flex-1"
                        onclick="document.getElementById('createRoomModal').classList.add('hidden')">
                    Cancelar
                </flux:button>
                <flux:button type="submit" variant="primary" class="flex-1">
                    Crear Sala
                </flux:button>
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
