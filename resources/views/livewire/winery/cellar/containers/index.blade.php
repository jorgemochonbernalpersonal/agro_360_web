<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Contenedores de Bodega"
        description="Gestiona tus depósitos, barricas y otros contenedores"
    >
        <x-slot:actions>
            <flux:button href="{{ route('winery.containers.create') }}" variant="primary" icon="plus">
                Nuevo Contenedor
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <x-agro.stat-card label="Contenedores activos" :value="$stats['total']" icon="cube" />
        <x-agro.stat-card
            label="Capacidad total"
            :value="number_format($stats['total_capacity'], 0) . ' kg'"
            icon="archive-box"
        />
        <x-agro.stat-card
            label="En uso"
            :value="number_format($stats['total_used'], 0) . ' kg'"
            icon="arrow-trending-up"
        />
        <x-agro.stat-card
            label="Disponible"
            :value="number_format($stats['total_available'], 0) . ' kg'"
            icon="arrow-trending-down"
        />
    </div>

    {{-- Ocupación global --}}
    @if($stats['total'] > 0)
        <x-agro.card>
            <x-agro.progress-bar
                label="Ocupación global de bodega"
                :percentage="$stats['fill_percent']"
                :currentValue="$stats['total_used']"
                :maxValue="$stats['total_capacity']"
                unit="kg"
            />
        </x-agro.card>
    @endif

    {{-- Filtros --}}
    <x-agro.filter-bar>
        <x-agro.filter-input
            wire:model.live.debounce.300ms="search"
            placeholder="Buscar contenedor..."
        />
        <flux:select wire:model.live="typeFilter" size="sm" class="w-40">
            <flux:select.option value="">Todos los tipos</flux:select.option>
            @foreach($types as $type)
                <flux:select.option value="{{ $type->id }}">{{ $type->name }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="statusFilter" size="sm" class="w-36">
            <flux:select.option value="active">Activos</flux:select.option>
            <flux:select.option value="archived">Archivados</flux:select.option>
            <flux:select.option value="all">Todos</flux:select.option>
        </flux:select>
        @if($search || $typeFilter || $statusFilter !== 'active')
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">
                Limpiar
            </flux:button>
        @endif
    </x-agro.filter-bar>

    {{-- Tabla --}}
    <x-agro.data-table
        :headers="['Contenedor', 'Tipo', 'Ocupación', 'Kg usados / capacidad', 'Estado', 'Acciones']"
        empty-message="No hay contenedores registrados"
        empty-description="Crea tu primer contenedor para empezar a asignar recepciones de uva"
    >
        @if($containers->count() > 0)
            @foreach($containers as $container)
                @php
                    $pct      = $container->getOccupancyPercentage();
                    $typeName = $typesById[$container->type_id]->name ?? '—';
                @endphp
                <x-agro.table-row>
                    <x-agro.table-cell>
                        <p class="font-semibold text-zinc-900 text-sm">{{ $container->name }}</p>
                        @if($container->serial_number)
                            <p class="text-xs text-zinc-400">S/N: {{ $container->serial_number }}</p>
                        @endif
                        @if($container->description)
                            <p class="text-xs text-zinc-400 truncate max-w-xs">{{ $container->description }}</p>
                        @endif
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <flux:badge color="zinc" size="sm">{{ $typeName }}</flux:badge>
                    </x-agro.table-cell>

                    <x-agro.table-cell class="min-w-[160px]">
                        <x-agro.progress-bar
                            :percentage="$pct"
                            :showValues="false"
                        />
                        <p class="text-xs text-zinc-500 mt-1">{{ number_format($pct, 0) }}%</p>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <span class="text-sm font-medium text-zinc-900">
                            {{ number_format($container->used_capacity, 0) }} kg
                        </span>
                        <span class="text-xs text-zinc-400">
                            / {{ number_format($container->capacity, 0) }} kg
                        </span>
                        @if($container->harvests_count > 0)
                            <p class="text-xs text-zinc-400">
                                {{ $container->harvests_count }}
                                {{ $container->harvests_count === 1 ? 'recepción' : 'recepciones' }}
                            </p>
                        @endif
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        @if($container->archived)
                            <flux:badge color="zinc" size="sm">Archivado</flux:badge>
                        @elseif($container->isFull())
                            <flux:badge color="red" size="sm">Lleno</flux:badge>
                        @elseif($container->isEmpty())
                            <flux:badge color="green" size="sm">Vacío</flux:badge>
                        @else
                            <flux:badge color="yellow" size="sm">En uso</flux:badge>
                        @endif
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <div class="flex items-center justify-end gap-1">
                            <flux:button
                                href="{{ route('winery.containers.edit', $container) }}"
                                variant="ghost"
                                size="xs"
                                icon="pencil"
                                title="Editar"
                            />
                            @if($container->archived)
                                <flux:button
                                    wire:click="unarchive({{ $container->id }})"
                                    variant="ghost"
                                    size="xs"
                                    icon="arrow-path"
                                    title="Reactivar"
                                />
                            @else
                                <flux:button
                                    wire:click="archive({{ $container->id }})"
                                    wire:confirm="¿Archivar este contenedor?"
                                    variant="ghost"
                                    size="xs"
                                    icon="archive-box-arrow-down"
                                    title="Archivar"
                                />
                            @endif
                            @if($container->harvests_count === 0)
                                <flux:button
                                    wire:click="delete({{ $container->id }})"
                                    wire:confirm="¿Eliminar este contenedor permanentemente?"
                                    variant="ghost"
                                    size="xs"
                                    icon="trash"
                                    class="text-red-500 hover:text-red-700"
                                    title="Eliminar"
                                />
                            @endif
                        </div>
                    </x-agro.table-cell>
                </x-agro.table-row>
            @endforeach

            <x-slot name="pagination">
                {{ $containers->links() }}
            </x-slot>
        @endif
    </x-agro.data-table>
</div>
