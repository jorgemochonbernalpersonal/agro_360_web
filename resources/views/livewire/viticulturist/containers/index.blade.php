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

            <div class="flex-1 relative">
                <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                    <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400" />
                </div>
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Buscar por nombre o número de serie..."
                    class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition"
                />
            </div>

            @php $filterCount = ($filterStatus !== '' ? 1 : 0); @endphp
            <button
                x-on:click="$dispatch('open-modal', 'container-filters')"
                class="relative inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm font-medium text-zinc-700 hover:bg-zinc-50 shadow-sm transition-colors"
            >
                <flux:icon icon="adjustments-horizontal" class="size-4 text-zinc-500" />
                Filtros
                @if($filterCount > 0)
                    <span class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-agro-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none">
                        {{ $filterCount }}
                    </span>
                @endif
            </button>

            <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

            <flux:button href="{{ route('viticulturist.containers.create') }}" variant="primary" icon="plus">
                Nuevo
            </flux:button>

        </div>

        {{-- Active filter chips --}}
        @if($search || $filterStatus !== '')
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs text-zinc-400">Filtros activos:</span>

                @if($search)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                        <flux:icon icon="magnifying-glass" class="size-3" />
                        "{{ $search }}"
                        <button wire:click="$set('search', '')" class="hover:text-agro-900 ml-0.5">
                            <flux:icon icon="x-mark" class="size-3" />
                        </button>
                    </span>
                @endif

                @if($filterStatus !== '')
                    @php
                        $statusLabels = ['empty' => 'Vacíos', 'available' => 'Disponibles', 'full' => 'Llenos'];
                    @endphp
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                        Estado: {{ $statusLabels[$filterStatus] ?? $filterStatus }}
                        <button wire:click="$set('filterStatus', '')" class="hover:text-agro-900 ml-0.5">
                            <flux:icon icon="x-mark" class="size-3" />
                        </button>
                    </span>
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
                    $btnBase    = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors';
                    $btnDanger  = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-500 hover:bg-red-50 transition-colors';
                    $btnSuccess = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-agro-600 hover:bg-agro-50 transition-colors';

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
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-zinc-100 rounded-full flex items-center justify-center shrink-0">
                                <flux:icon icon="beaker" class="size-4 text-zinc-500" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-zinc-900 text-sm truncate leading-tight">{{ $container->name }}</p>
                                @if($container->serial_number)
                                    <p class="text-xs text-zinc-400 leading-tight mt-0.5">SN: {{ $container->serial_number }}</p>
                                @endif
                            </div>
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-zinc-600 shrink-0">
                                <span class="w-2 h-2 rounded-full {{ $statusDot }}"></span>
                                {{ $statusLabel }}
                            </span>
                        </div>
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
                        <div class="bg-agro-50 rounded-xl p-2.5">
                            <p class="text-[10px] text-agro-600 font-medium uppercase tracking-wide mb-0.5">Capacidad</p>
                            <p class="text-sm font-bold text-agro-700">{{ number_format($container->capacity, 0) }} L</p>
                        </div>
                        <div class="bg-zinc-50 rounded-xl p-2.5">
                            <p class="text-[10px] text-zinc-500 font-medium uppercase tracking-wide mb-0.5">Disponible</p>
                            <p class="text-sm font-bold text-zinc-700">{{ number_format($container->getAvailableCapacity(), 0) }} L</p>
                        </div>
                    </div>

                    <x-slot:footer>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1">
                                <a href="{{ route('viticulturist.containers.show', $container->id) }}" class="{{ $btnBase }}" title="Ver contenedor">
                                    <flux:icon icon="eye" class="size-4" />
                                </a>
                                <a href="{{ route('viticulturist.containers.edit', $container->id) }}" class="{{ $btnBase }}" title="Editar">
                                    <flux:icon icon="pencil-square" class="size-4" />
                                </a>
                            </div>
                            <div class="flex items-center gap-1">
                                @if(!$container->archived)
                                    <button wire:click="archive({{ $container->id }})" class="{{ $btnDanger }}" title="Archivar">
                                        <flux:icon icon="archive-box" class="size-4" />
                                    </button>
                                @else
                                    <button wire:click="unarchive({{ $container->id }})" class="{{ $btnSuccess }}" title="Restaurar">
                                        <flux:icon icon="arrow-uturn-left" class="size-4" />
                                    </button>
                                    @if($container->isEmpty())
                                        <button
                                            wire:click="delete({{ $container->id }})"
                                            wire:confirm="¿Seguro que deseas eliminar este contenedor?"
                                            class="{{ $btnDanger }}"
                                            title="Eliminar"
                                        >
                                            <flux:icon icon="trash" class="size-4" />
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        @if($containers->hasPages())
            <div class="flex justify-center">{{ $containers->links() }}</div>
        @endif

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
                    <flux:button href="{{ route('viticulturist.containers.create') }}" variant="primary" icon="plus">
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
            <label class="block text-sm font-medium text-zinc-700 mb-1.5">Estado de ocupación</label>
            <select wire:model.live="filterStatus"
                    class="w-full px-3 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-agro-400 focus:border-transparent">
                <option value="">Todos</option>
                <option value="empty">Vacíos</option>
                <option value="available">Disponibles</option>
                <option value="full">Llenos</option>
            </select>
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
