<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Contenedores"
        description="Gestiona tus barricas, depósitos y tanques de bodega"
    />

    {{-- Tabs --}}
    <x-agro.tabs
        :tabs="[
            'active'   => ['label' => 'Activos',     'count' => $stats['active']],
            'archived' => ['label' => 'Archivados',  'count' => $stats['archived']],
        ]"
        :active="$currentTab"
        wireMethod="switchTab"
    />

    {{-- Toolbar --}}
    <div class="space-y-3">
        <div class="flex items-center gap-3">

            <x-agro.search-input wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre o número de serie..." />

            @php $filterCount = ($filterStatus !== '' ? 1 : 0); @endphp
            <x-agro.filter-button modal="container-filters" :count="$filterCount" />

            <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

            <flux:button href="{{ roleRoute('viticulturist.containers.create') }}" variant="primary" icon="plus">
                Nuevo
            </flux:button>

        </div>

        {{-- Active filter chips --}}
        @if($search || $filterStatus !== '')
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs text-zinc-400">Filtros activos:</span>

                @if($search)
                    <x-agro.filter-chip icon="magnifying-glass" :label="'\"' . $search . '\"'" wireRemove="$set('search', '')" />
                @endif

                @if($filterStatus !== '')
                    @php
                        $statusLabels = ['empty' => 'Vacíos', 'available' => 'Disponibles', 'full' => 'Llenos'];
                    @endphp
                    <x-agro.filter-chip :label="'Estado: ' . ($statusLabels[$filterStatus] ?? $filterStatus)" wireRemove="$set('filterStatus', '')" />
                @endif

                <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 underline">
                    Limpiar todo
                </button>
            </div>
        @endif
    </div>

    {{-- Card grid --}}
    @if($containers->count() > 0)
        <div
            class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6"
            wire:loading.class="opacity-60 pointer-events-none"
            wire:target="switchTab, search, filterStatus, clearFilters"
        >
            @foreach($containers as $i => $container)
                @php
                    $occupancy = $container->getOccupancyPercentage();
                    [$statusLabel, $statusDot] = match(true) {
                        $container->isEmpty() => ['Vacío',      'bg-blue-400'],
                        $container->isFull()  => ['Lleno',      'bg-red-400'],
                        default               => ['Disponible', 'bg-agro-400'],
                    };
                @endphp

                <x-agro.card
                    wire:key="container-{{ $container->id }}"
                    class="animate-fade-in-up hover:-translate-y-1 {{ $container->archived ? 'opacity-60' : '' }}"
                    style="animation-delay: {{ min($i * 50, 400) }}ms"
                >
                    <x-slot:header>
                        <x-agro.card-item-header
                            icon="beaker"
                            :title="$container->name"
                            :subtitle="$container->serial_number ? 'SN: ' . $container->serial_number : null"
                        >
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-zinc-600">
                                <span class="w-2 h-2 rounded-full {{ $statusDot }}"></span>
                                {{ $statusLabel }}
                            </span>
                        </x-agro.card-item-header>
                    </x-slot:header>

                    {{-- Ocupación --}}
                    <div class="mb-3">
                        <div class="flex justify-between mb-1.5">
                            <span class="text-xs text-zinc-500">Ocupación</span>
                            <span class="text-xs font-semibold text-zinc-700">{{ $occupancy }}%</span>
                        </div>
                        <div class="w-full bg-zinc-100 rounded-full h-2">
                            <div
                                class="h-2 rounded-full transition-all {{ $occupancy >= 90 ? 'bg-red-400' : ($occupancy >= 50 ? 'bg-amber-400' : 'bg-agro-400') }}"
                                style="width: {{ $occupancy }}%"
                            ></div>
                        </div>
                    </div>

                    {{-- Capacidad --}}
                    <div class="grid grid-cols-2 gap-2">
                        <x-agro.metric-cell label="Capacidad" :value="number_format($container->capacity, 0) . ' L'" color="agro" />
                        <x-agro.metric-cell label="Disponible" :value="number_format($container->getAvailableCapacity(), 0) . ' L'" color="zinc" />
                    </div>

                    <x-slot:footer>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1">
                                <x-agro.action-button
                                    variant="view"
                                    href="{{ roleRoute('viticulturist.containers.show', $container->id) }}"
                                    title="Ver contenedor"
                                />
                                <x-agro.action-button
                                    variant="edit"
                                    href="{{ roleRoute('viticulturist.containers.edit', $container->id) }}"
                                    title="Editar"
                                />
                            </div>
                            <div class="flex items-center gap-1">
                                @if(!$container->archived)
                                    <x-agro.action-button
                                        variant="archive"
                                        wire:click="archive({{ $container->id }})"
                                        title="Archivar"
                                    />
                                @else
                                    <x-agro.action-button
                                        variant="restore"
                                        icon="arrow-uturn-left"
                                        wire:click="unarchive({{ $container->id }})"
                                        title="Restaurar"
                                    />
                                    @if($container->isEmpty())
                                        <x-agro.action-button
                                            variant="delete"
                                            wire:click="delete({{ $container->id }})"
                                            wire:confirm="¿Seguro que deseas eliminar este contenedor?"
                                            title="Eliminar"
                                        />
                                    @endif
                                @endif
                            </div>
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        <x-agro-pagination :paginator="$containers" />

    @else
        <x-agro.empty-state
            icon="beaker"
            message="{{ $currentTab === 'active' ? 'No hay contenedores activos' : 'No hay contenedores archivados' }}"
            description="{{ $search || $filterStatus !== '' ? 'Ningún contenedor coincide con los filtros aplicados.' : 'Crea tu primer contenedor para gestionar tu bodega.' }}"
        >
            @if($search || $filterStatus !== '')
                <x-slot:action>
                    <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">Limpiar filtros</flux:button>
                </x-slot:action>
            @elseif($currentTab === 'active')
                <x-slot:action>
                    <flux:button href="{{ roleRoute('viticulturist.containers.create') }}" variant="primary" icon="plus">
                        Nuevo Contenedor
                    </flux:button>
                </x-slot:action>
            @endif
        </x-agro.empty-state>
    @endif

    {{-- Modal Filtros --}}
    <x-agro.modal name="container-filters" maxWidth="sm">
        <div class="px-6 py-4 border-b border-zinc-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-agro-100 rounded-lg flex items-center justify-center">
                        <flux:icon icon="adjustments-horizontal" class="size-4 text-agro-600" />
                    </div>
                    <h3 class="text-base font-semibold text-zinc-900">Filtros</h3>
                </div>
                <flux:button x-on:click="$dispatch('close-modal', 'container-filters')" variant="ghost" size="sm" icon="x-mark" />
            </div>
        </div>

        <div class="px-6 py-5">
            <x-agro.filter-select label="Estado de ocupación" wire:model.live="filterStatus" placeholder="Todos">
                <flux:select.option value="empty">Vacíos</flux:select.option>
                <flux:select.option value="available">Disponibles</flux:select.option>
                <flux:select.option value="full">Llenos</flux:select.option>
            </x-agro.filter-select>
        </div>

        <div class="px-6 py-4 bg-zinc-50 border-t border-zinc-200 flex items-center justify-between rounded-b-2xl">
            <button wire:click="clearFilters" x-on:click="$dispatch('close-modal', 'container-filters')"
                    class="text-sm text-zinc-500 hover:text-zinc-700 transition-colors">
                Limpiar filtros
            </button>
            <flux:button x-on:click="$dispatch('close-modal', 'container-filters')" variant="primary" size="sm">
                Aplicar
            </flux:button>
        </div>
    </x-agro.modal>

</div>
