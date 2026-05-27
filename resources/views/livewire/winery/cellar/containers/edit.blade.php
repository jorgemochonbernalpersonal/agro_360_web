<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="{{ __('Editar Contenedor') }}"
        :description="__('Modifica los datos del contenedor')"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('containers.index') }}" variant="ghost" icon="arrow-left">
                Volver
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    @if($container->getTotalUsed() > 0)
        <x-agro.card>
            <div class="space-y-2">
                <p class="text-sm font-medium text-zinc-700">{{ __('Estado actual del contenedor') }}</p>
                <x-agro.progress-bar
                    :percentage="$container->getOccupancyPercentage()"
                    :currentValue="$container->getTotalUsed()"
                    :maxValue="$container->capacity"
                    :unit="$container->unit ?? 'kg'"
                />
            </div>
        </x-agro.card>
    @endif

    <form wire:submit="save" class="space-y-8">
        <x-agro.form-section title="{{ __('Datos del Contenedor') }}">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <flux:field>
                    <flux:label>{{ __('Nombre') }}</flux:label>
                    <flux:input wire:model="name" :placeholder="__('Ej: Depósito 1, Barrica A-01')" />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Tipo') }}</flux:label>
                    <flux:select wire:model="type_id">
                        <flux:select.option value="">{{ __('Selecciona un tipo...') }}</flux:select.option>
                        @foreach($types as $type)
                            <flux:select.option value="{{ $type->id }}">{{ $type->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="type_id" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Unidad de capacidad') }}</flux:label>
                    @if($hasStock)
                        <flux:input value="{{ $unit === 'litros' ? 'Litros (L)' : 'Kilogramos (kg)' }}" disabled />
                        <flux:description>{{ __('No se puede cambiar la unidad mientras el contenedor tenga contenido.') }}</flux:description>
                        {{-- campo oculto para que Livewire mantenga el valor --}}
                        <input type="hidden" wire:model="unit" />
                    @else
                        <flux:select wire:model.live="unit">
                            <flux:select.option value="kg">{{ __('Kilogramos (kg) — uva / mosto') }}</flux:select.option>
                            <flux:select.option value="litros">{{ __('Litros (L) — vino elaborado') }}</flux:select.option>
                        </flux:select>
                    @endif
                    <flux:error name="unit" />
                </flux:field>

                <flux:field>
                    <flux:label>Capacidad ({{ $unit === 'litros' ? 'L' : 'kg' }})</flux:label>
                    <flux:input wire:model="capacity" type="number" step="0.01" min="0.01" :placeholder="__('Ej: 5000')" />
                    @if($hasStock)
                        <flux:description>
                            Mínimo: {{ number_format($container->getTotalUsed(), 0) }} {{ $container->unit ?? 'kg' }} (ya utilizado).
                        </flux:description>
                    @endif
                    <flux:error name="capacity" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Sala de bodega') }}</flux:label>
                    <flux:select wire:model="container_room_id">
                        <flux:select.option value="">{{ __('Sin sala asignada') }}</flux:select.option>
                        @foreach($rooms as $room)
                            <flux:select.option value="{{ $room->id }}">{{ $room->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:description>{{ __('Opcional. Agrupa el contenedor en una zona de tu bodega.') }}</flux:description>
                    <flux:error name="container_room_id" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Número de serie') }}</flux:label>
                    <flux:input wire:model="serial_number" :placeholder="__('Opcional')" />
                    <flux:error name="serial_number" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Fecha de compra') }}</flux:label>
                    <flux:input wire:model="purchase_date" type="date" />
                    <flux:error name="purchase_date" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Proveedor') }}</flux:label>
                    <flux:input wire:model="supplier_name" :placeholder="__('Nombre del proveedor (opcional)')" />
                    <flux:error name="supplier_name" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>{{ __('Descripción / Notas') }}</flux:label>
                    <flux:textarea wire:model="description" rows="3" :placeholder="__('Observaciones sobre este contenedor...')" />
                    <flux:error name="description" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('containers.index')"
            submit-:label="__('Guardar Cambios')"
        />
    </form>
</div>
