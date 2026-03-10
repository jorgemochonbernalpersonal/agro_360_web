<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Enólogos"
        description="Gestiona los técnicos enológicos de tu bodega"
    />

    {{-- Tabs --}}
    <x-agro.tabs
        :tabs="[
            'active'   => ['label' => 'Activos',   'count' => $stats['active']],
            'inactive' => ['label' => 'Inactivos', 'count' => $stats['inactive']],
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
                    placeholder="Buscar por nombre, email o nº colegiado..."
                    class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition"
                />
            </div>

            <flux:button href="{{ route('winery.oenologists.create') }}" wire:navigate variant="primary" icon="plus">
                Nuevo Enólogo
            </flux:button>

        </div>

        {{-- Active filter chips --}}
        @if($search)
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs text-zinc-400">Filtros activos:</span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="magnifying-glass" class="size-3" />
                    "{{ $search }}"
                    <button wire:click="$set('search', '')" class="hover:text-agro-900 ml-0.5">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
                <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 underline">
                    Limpiar todo
                </button>
            </div>
        @endif
    </div>

    {{-- Card grid --}}
    @if($oenologists->count() > 0)
        <div
            class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6"
            wire:loading.class="opacity-60 pointer-events-none"
            wire:target="search, clearFilters, switchTab"
        >
            @foreach($oenologists as $i => $oenologist)
                @php
                    $btnBase   = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors';
                    $btnDanger = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-500 hover:bg-red-50 transition-colors';
                    $btnGreen  = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-agro-600 hover:bg-agro-50 transition-colors';
                @endphp

                <x-agro.card
                    wire:key="oenologist-{{ $oenologist->id }}"
                    class="animate-fade-in-up hover:-translate-y-1 {{ !$oenologist->active ? 'opacity-70' : '' }}"
                    style="animation-delay: {{ min($i * 50, 400) }}ms"
                >
                    <x-slot:header>
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-violet-50 rounded-full flex items-center justify-center shrink-0">
                                <flux:icon icon="beaker" class="size-4 text-violet-500" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-zinc-900 text-sm truncate leading-tight">{{ $oenologist->full_name }}</p>
                                @if($oenologist->license_number)
                                    <p class="text-xs text-zinc-400 leading-tight mt-0.5">Colegiado: {{ $oenologist->license_number }}</p>
                                @endif
                            </div>
                            @if(!$oenologist->active)
                                <flux:badge color="zinc" size="sm" class="shrink-0">Inactivo</flux:badge>
                            @endif
                        </div>
                    </x-slot:header>

                    {{-- Contacto --}}
                    <div class="space-y-1.5 mb-3">
                        @if($oenologist->email)
                            <div class="flex items-center gap-2">
                                <flux:icon icon="envelope" class="size-3.5 text-zinc-400 shrink-0" />
                                <span class="text-xs text-zinc-600 truncate">{{ $oenologist->email }}</span>
                            </div>
                        @endif
                        @if($oenologist->phone)
                            <div class="flex items-center gap-2">
                                <flux:icon icon="phone" class="size-3.5 text-zinc-400 shrink-0" />
                                <span class="text-xs text-zinc-600">{{ $oenologist->phone }}</span>
                            </div>
                        @endif
                        @if(!$oenologist->email && !$oenologist->phone)
                            <p class="text-xs text-zinc-400 italic">Sin datos de contacto</p>
                        @endif
                    </div>

                    @if($oenologist->notes)
                        <p class="text-xs text-zinc-500 line-clamp-2">{{ $oenologist->notes }}</p>
                    @endif

                    <x-slot:footer>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1">
                                <a href="{{ route('winery.oenologists.edit', $oenologist->id) }}" wire:navigate class="{{ $btnBase }}" title="Editar">
                                    <flux:icon icon="pencil-square" class="size-4" />
                                </a>
                            </div>
                            <div class="flex items-center gap-1">
                                <button
                                    wire:click="toggleActive({{ $oenologist->id }})"
                                    wire:loading.attr="disabled"
                                    class="{{ $oenologist->active ? $btnDanger : $btnGreen }}"
                                    title="{{ $oenologist->active ? 'Desactivar' : 'Activar' }}"
                                >
                                    <flux:icon icon="{{ $oenologist->active ? 'no-symbol' : 'check-circle' }}" class="size-4" />
                                </button>
                                <button
                                    wire:click="delete({{ $oenologist->id }})"
                                    wire:loading.attr="disabled"
                                    wire:confirm="¿Seguro que quieres eliminar este enólogo?"
                                    class="{{ $btnDanger }}"
                                    title="Eliminar"
                                >
                                    <flux:icon icon="trash" class="size-4" />
                                </button>
                            </div>
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        @if($oenologists->hasPages())
            <div class="flex justify-center">{{ $oenologists->links() }}</div>
        @endif

    @else
        <x-agro.empty-state
            icon="beaker"
            message="{{ $currentTab === 'active' ? 'No hay enólogos activos' : 'No hay enólogos inactivos' }}"
            description="{{ $search ? 'Ningún enólogo coincide con la búsqueda.' : ($currentTab === 'active' ? 'Añade el técnico enológico responsable de tu bodega.' : 'Los enólogos desactivados aparecerán aquí.') }}"
        >
            @if($search)
                <x-slot:action>
                    <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">Limpiar búsqueda</flux:button>
                </x-slot:action>
            @elseif($currentTab === 'active')
                <x-slot:action>
                    <flux:button href="{{ route('winery.oenologists.create') }}" wire:navigate variant="primary" icon="plus">
                        Nuevo Enólogo
                    </flux:button>
                </x-slot:action>
            @endif
        </x-agro.empty-state>
    @endif

</div>
