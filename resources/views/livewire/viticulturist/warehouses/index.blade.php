<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Almacenes"
        description="Organiza tus productos fitosanitarios por ubicación física"
    />

    {{-- Tabs --}}
    <x-agro.tabs
        :tabs="[
            'active'   => ['label' => 'Activos',   'count' => $stats['active']],
            'inactive' => ['label' => 'Inactivos',  'count' => $stats['inactive']],
        ]"
        :active="$currentTab"
        wireMethod="switchTab"
    />

    {{-- Toolbar --}}
    <div class="flex items-center gap-3">

        <div class="flex-1 relative">
            <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400" />
            </div>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Buscar por nombre o ubicación..."
                class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition"
            />
        </div>

        <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

        <flux:button href="{{ route('viticulturist.warehouses.create') }}" variant="primary" icon="plus">
            Nuevo
        </flux:button>

    </div>

    {{-- Card grid --}}
    @if($warehouses->count() > 0)
        <div
            class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6"
            wire:loading.class="opacity-60 pointer-events-none"
            wire:target="switchTab, search, clearFilters"
        >
            @foreach($warehouses as $i => $warehouse)
                @php
                    $btnBase    = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors';
                    $btnDanger  = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-500 hover:bg-red-50 transition-colors';
                    $btnSuccess = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-agro-600 hover:bg-agro-50 transition-colors';
                @endphp

                <x-agro.card
                    wire:key="warehouse-{{ $warehouse->id }}"
                    class="animate-fade-in-up hover:-translate-y-1 {{ !$warehouse->active ? 'opacity-60' : '' }}"
                    style="animation-delay: {{ min($i * 50, 400) }}ms"
                >
                    <x-slot:header>
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-zinc-100 rounded-full flex items-center justify-center shrink-0">
                                <flux:icon icon="building-office" class="size-4 text-zinc-500" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-zinc-900 text-sm truncate leading-tight">{{ $warehouse->name }}</p>
                                @if($warehouse->location)
                                    <p class="text-xs text-zinc-400 leading-tight mt-0.5 truncate">{{ $warehouse->location }}</p>
                                @endif
                            </div>
                            <x-agro.status-badge :status="$warehouse->active" />
                        </div>
                    </x-slot:header>

                    {{-- Descripción --}}
                    @if($warehouse->description)
                        <div class="flex items-start gap-2 mb-3">
                            <flux:icon icon="information-circle" class="size-3.5 text-zinc-400 shrink-0 mt-0.5" />
                            <span class="text-xs text-zinc-500 line-clamp-2">{{ $warehouse->description }}</span>
                        </div>
                    @endif

                    {{-- Productos en stock --}}
                    <div class="bg-agro-50 rounded-xl p-2.5">
                        <p class="text-[10px] text-agro-600 font-medium uppercase tracking-wide mb-0.5">Productos en stock</p>
                        <p class="text-sm font-bold text-agro-700">{{ $warehouse->stocks_count }}</p>
                    </div>

                    <x-slot:footer>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1">
                                <a href="{{ route('viticulturist.warehouses.edit', $warehouse->id) }}" class="{{ $btnBase }}" title="Editar">
                                    <flux:icon icon="pencil-square" class="size-4" />
                                </a>
                            </div>
                            <div class="flex items-center gap-1">
                                <button
                                    wire:click="toggleActive({{ $warehouse->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="toggleActive({{ $warehouse->id }})"
                                    class="{{ $warehouse->active ? $btnDanger : $btnSuccess }}"
                                    title="{{ $warehouse->active ? 'Desactivar' : 'Activar' }}"
                                >
                                    <span wire:loading.remove wire:target="toggleActive({{ $warehouse->id }})">
                                        <flux:icon icon="{{ $warehouse->active ? 'no-symbol' : 'check-circle' }}" class="size-4" />
                                    </span>
                                    <span wire:loading wire:target="toggleActive({{ $warehouse->id }})">
                                        <svg class="animate-spin size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </span>
                                </button>
                                @if($warehouse->stocks_count === 0)
                                    <button
                                        wire:click="delete({{ $warehouse->id }})"
                                        wire:confirm="¿Seguro que deseas eliminar este almacén?"
                                        wire:loading.attr="disabled"
                                        class="{{ $btnDanger }}"
                                        title="Eliminar"
                                    >
                                        <flux:icon icon="trash" class="size-4" />
                                    </button>
                                @endif
                            </div>
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        @if($warehouses->hasPages())
            <div class="flex justify-center">{{ $warehouses->links() }}</div>
        @endif

    @else
        <x-agro.empty-state
            icon="building-office"
            message="{{ $currentTab === 'active' ? 'No hay almacenes activos' : 'No hay almacenes inactivos' }}"
            description="{{ $search ? 'Ningún almacén coincide con la búsqueda.' : 'Crea tu primer almacén para organizar el stock de productos.' }}"
        >
            @if($search)
                <x-slot:action>
                    <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">Limpiar búsqueda</flux:button>
                </x-slot:action>
            @else
                <x-slot:action>
                    <flux:button href="{{ route('viticulturist.warehouses.create') }}" variant="primary" icon="plus">
                        Nuevo Almacén
                    </flux:button>
                </x-slot:action>
            @endif
        </x-agro.empty-state>
    @endif

</div>
