<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Editar Contenedor"
        description="Modifica los datos del contenedor"
    >
        <x-slot:actions>
            <flux:button href="{{ route('winery.containers.index') }}" variant="ghost" icon="arrow-left">
                Volver
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Estado actual --}}
    @if($container->used_capacity > 0)
        <x-agro.card>
            <div class="space-y-2">
                <p class="text-sm font-medium text-zinc-700">Estado actual del contenedor</p>
                <x-agro.progress-bar
                    :percentage="$container->getOccupancyPercentage()"
                    :currentValue="$container->used_capacity"
                    :maxValue="$container->capacity"
                    unit="kg"
                />
            </div>
        </x-agro.card>
    @endif

    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-lg bg-agro-50">
                    <flux:icon icon="cube" class="size-4 text-agro-600" />
                </div>
                <span class="font-semibold text-zinc-900 text-sm">Datos del Contenedor</span>
            </div>
        </x-slot:header>

        <form wire:submit="save" class="space-y-5">
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
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <flux:field>
                    <flux:label>Capacidad (kg)</flux:label>
                    <flux:input wire:model="capacity" type="number" step="0.01" min="0.01" placeholder="Ej: 5000" />
                    @if($container->used_capacity > 0)
                        <flux:description>
                            Mínimo: {{ number_format($container->used_capacity, 0) }} kg (ya utilizado).
                        </flux:description>
                    @endif
                    <flux:error name="capacity" />
                </flux:field>

                <flux:field>
                    <flux:label>Número de serie</flux:label>
                    <flux:input wire:model="serial_number" placeholder="Opcional" />
                    <flux:error name="serial_number" />
                </flux:field>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
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
            </div>

            <flux:field>
                <flux:label>Descripción / Notas</flux:label>
                <flux:textarea wire:model="description" rows="3" placeholder="Observaciones sobre este contenedor..." />
                <flux:error name="description" />
            </flux:field>

            <div class="pt-2 flex justify-end gap-3">
                <flux:button href="{{ route('winery.containers.index') }}" variant="ghost">
                    Cancelar
                </flux:button>
                <flux:button type="submit" variant="primary" icon="check">
                    Guardar Cambios
                </flux:button>
            </div>
        </form>
    </x-agro.card>
</div>
