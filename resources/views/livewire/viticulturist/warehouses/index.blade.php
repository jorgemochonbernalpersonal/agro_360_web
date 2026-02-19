<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Almacenes"
        description="Organiza tus productos fitosanitarios por ubicacion fisica"
    >
        <x-slot:actions>
            <flux:button href="{{ route('viticulturist.warehouses.create') }}" variant="primary" icon="plus">
                Nuevo Almacen
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-agro.stat-card label="Total Almacenes" :value="$stats['total']" color="blue" icon="building-office" />
        <x-agro.stat-card label="Activos" :value="$stats['active']" color="green" icon="check-circle" />
        <x-agro.stat-card label="Inactivos" :value="$stats['inactive']" color="red" icon="x-circle" />
    </div>

    {{-- Filtros --}}
    <x-agro.filter-bar>
        <x-agro.filter-input wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre o ubicacion..." />
    </x-agro.filter-bar>

    {{-- Tabla --}}
    <x-agro.data-table :headers="['Nombre', 'Ubicacion', 'Productos en Stock', 'Estado', 'Acciones']" empty-message="No hay almacenes registrados" empty-description="Comienza creando tu primer almacen para organizar el stock de productos">
        @if($warehouses->count() > 0)
            @foreach($warehouses as $warehouse)
                <x-agro.table-row>
                    <x-agro.table-cell>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-agro-50 flex items-center justify-center">
                                <flux:icon icon="building-office" class="size-5 text-agro-600" />
                            </div>
                            <div>
                                <div class="text-sm font-bold text-zinc-900">{{ $warehouse->name }}</div>
                                @if($warehouse->description)
                                    <div class="text-xs text-zinc-500 mt-1">{{ Str::limit($warehouse->description, 50) }}</div>
                                @endif
                            </div>
                        </div>
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        <span class="text-sm text-zinc-700">{{ $warehouse->location ?? '-' }}</span>
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        <flux:badge color="blue">{{ $warehouse->stocks_count }} productos</flux:badge>
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        <x-agro.status-badge :active="$warehouse->active" />
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        <div class="flex items-center justify-end gap-2">
                            <x-agro.action-button
                                variant="edit"
                                href="{{ route('viticulturist.warehouses.edit', $warehouse->id) }}"
                            />
                            <x-agro.action-button
                                :variant="$warehouse->active ? 'deactivate' : 'activate'"
                                wireClick="toggleActive({{ $warehouse->id }})"
                            />
                            @if($warehouse->stocks_count === 0)
                                <x-agro.action-button
                                    variant="delete"
                                    wireClick="delete({{ $warehouse->id }})"
                                    wireConfirm="¿Estas seguro de eliminar este almacen?"
                                />
                            @endif
                        </div>
                    </x-agro.table-cell>
                </x-agro.table-row>
            @endforeach
            <x-slot name="pagination">
                {{ $warehouses->links() }}
            </x-slot>
        @else
            <x-slot name="emptyAction">
                <flux:button href="{{ route('viticulturist.warehouses.create') }}" variant="primary" icon="plus">
                    Crear Primer Almacen
                </flux:button>
            </x-slot>
        @endif
    </x-agro.data-table>
</div>
