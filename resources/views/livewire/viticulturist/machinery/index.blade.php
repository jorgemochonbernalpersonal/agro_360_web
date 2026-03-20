<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Maquinaria"
        description="Gestiona tu maquinaria y equipos agrícolas"
    />

    {{-- Stats --}}
    <div x-data="{
        open: localStorage.getItem('machinery-stats-open') !== 'false',
        toggle() {
            this.open = !this.open;
            localStorage.setItem('machinery-stats-open', String(this.open));
        }
    }">
        <button
            @click="toggle()"
            class="flex items-center gap-1.5 text-[11px] font-semibold text-zinc-400 uppercase tracking-widest hover:text-zinc-600 transition-colors mb-3"
        >
            <span>Estadísticas</span>
            <flux:icon icon="chevron-up" class="size-3.5 transition-transform duration-200" ::class="{ 'rotate-180': !open }" />
        </button>
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-1"
        >
            <div class="grid grid-cols-2 gap-4">
                <x-agro.stat-card
                    label="Total maquinaria"
                    :value="$stats['total']"
                    description="'Equipos registrados'"
                    icon="wrench-screwdriver"
                    color="agro"
                />
                <x-agro.stat-card
                    label="Activa"
                    :value="$stats['active']"
                    description="'En uso'"
                    icon="check-circle"
                    color="agro"
                />
                <x-agro.stat-card
                    label="Inactiva"
                    :value="$stats['inactive']"
                    description="'Archivada o fuera de uso'"
                    icon="archive-box"
                    color="zinc"
                />
                <x-agro.stat-card
                    label="Tipos distintos"
                    :value="$stats['types_count']"
                    description="'Categorías de maquinaria'"
                    icon="squares-2x2"
                    color="blue"
                />
            </div>
        </div>
    </div>
    {{-- Tabs --}}
    <x-agro.tabs
        :tabs="[
            'active'   => ['label' => 'Activas',   'count' => $stats['active']],
            'inactive' => ['label' => 'Inactivas',  'count' => $stats['inactive']],
        ]"
        :active="$currentTab"
        wireMethod="switchTab"
    />

    {{-- Toolbar --}}
    <div class="space-y-3">
        <div class="flex items-center gap-3">

            {{-- Search --}}
            <div class="flex-1 relative">
                <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                    <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400" />
                </div>
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Buscar por nombre, marca, modelo..."
                    class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition"
                />
            </div>

            {{-- Filtros --}}
            @php $filterCount = ($typeFilter !== '' ? 1 : 0); @endphp
            <button
                x-on:click="$dispatch('open-modal', 'machinery-filters')"
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

            {{-- Nueva Maquinaria --}}
            @can('create', \App\Models\Machinery::class)
                <flux:button href="{{ route('viticulturist.machinery.create') }}" variant="primary" icon="plus">
                    Nueva
                </flux:button>
            @endcan

        </div>

        {{-- Active filter chips --}}
        @if($search || $typeFilter !== '')
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

                @if($typeFilter !== '')
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                        Tipo: {{ $typeFilter }}
                        <button wire:click="$set('typeFilter', '')" class="hover:text-agro-900 ml-0.5">
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
    @if($machinery->count() > 0)
        <div
            class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6"
            wire:loading.class="opacity-60 pointer-events-none"
            wire:target="switchTab, search, typeFilter, clearFilters"
        >
            @foreach($machinery as $i => $item)
                @php
                    $btnBase    = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors';
                    $btnDanger  = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-500 hover:bg-red-50 transition-colors';
                    $btnSuccess = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-agro-600 hover:bg-agro-50 transition-colors';
                @endphp

                <x-agro.card
                    wire:key="machinery-{{ $item->id }}"
                    class="animate-fade-in-up hover:-translate-y-1 {{ !$item->active ? 'opacity-60' : '' }}"
                    style="animation-delay: {{ min($i * 50, 400) }}ms"
                >
                    <x-slot:header>
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-zinc-100 rounded-full flex items-center justify-center shrink-0">
                                <flux:icon icon="cog-6-tooth" class="size-4 text-zinc-500" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-zinc-900 text-sm truncate leading-tight">{{ $item->name }}</p>
                                @if($item->year)
                                    <p class="text-xs text-zinc-400 leading-tight mt-0.5">Año {{ $item->year }}</p>
                                @endif
                            </div>
                            <x-agro.status-badge :status="$item->active" />
                        </div>
                    </x-slot:header>

                    {{-- Tipo --}}
                    <div class="flex items-center gap-2 mb-3">
                        <flux:icon icon="tag" class="size-3.5 text-zinc-400 shrink-0" />
                        <span class="text-xs text-zinc-600 truncate">{{ $item->type ?? 'Sin tipo' }}</span>
                        @if($item->is_rented)
                            <span class="inline-flex items-center gap-1 text-xs text-blue-600 shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                                Alquilada
                            </span>
                        @endif
                    </div>

                    {{-- Métricas --}}
                    <div class="grid grid-cols-2 gap-2 mb-3">
                        <div class="bg-zinc-50 rounded-xl p-2.5">
                            <p class="text-[10px] text-zinc-500 font-medium uppercase tracking-wide mb-0.5">Marca/Modelo</p>
                            <p class="text-sm font-bold text-zinc-700 truncate">
                                {{ ($item->brand || $item->model) ? trim(($item->brand ?? '') . ' ' . ($item->model ?? '')) : '—' }}
                            </p>
                        </div>
                        <div class="bg-agro-50 rounded-xl p-2.5">
                            <p class="text-[10px] text-agro-600 font-medium uppercase tracking-wide mb-0.5">Actividades</p>
                            <p class="text-sm font-bold text-agro-700">{{ $item->activities_count }}</p>
                        </div>
                    </div>

                    {{-- ROMA --}}
                    @if($item->roma_registration)
                        <div class="flex items-center gap-2">
                            <flux:icon icon="document-text" class="size-3.5 text-zinc-400 shrink-0" />
                            <span class="text-xs text-zinc-600">ROMA: {{ $item->roma_registration }}</span>
                        </div>
                    @endif

                    <x-slot:footer>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1">
                                @can('view', $item)
                                    <a href="{{ route('viticulturist.machinery.show', $item) }}" class="{{ $btnBase }}" title="Ver maquinaria">
                                        <flux:icon icon="eye" class="size-4" />
                                    </a>
                                @endcan
                                @can('update', $item)
                                    <a href="{{ route('viticulturist.machinery.edit', $item) }}" class="{{ $btnBase }}" title="Editar">
                                        <flux:icon icon="pencil-square" class="size-4" />
                                    </a>
                                @endcan
                            </div>

                            @can('update', $item)
                                <button
                                    wire:click="toggleActive({{ $item->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="toggleActive({{ $item->id }})"
                                    class="{{ $item->active ? $btnDanger : $btnSuccess }}"
                                    title="{{ $item->active ? 'Desactivar' : 'Activar' }}"
                                >
                                    <span wire:loading.remove wire:target="toggleActive({{ $item->id }})">
                                        <flux:icon icon="{{ $item->active ? 'no-symbol' : 'check-circle' }}" class="size-4" />
                                    </span>
                                    <span wire:loading wire:target="toggleActive({{ $item->id }})">
                                        <svg class="animate-spin size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </span>
                                </button>
                            @endcan
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        @if($machinery->hasPages())
            <div class="flex justify-center">{{ $machinery->links() }}</div>
        @endif

    @else
        <x-agro.empty-state
            icon="cog-6-tooth"
            message="{{ $currentTab === 'active' ? 'No hay maquinaria activa' : 'No hay maquinaria inactiva' }}"
            description="{{ $search || $typeFilter !== '' ? 'Ninguna maquinaria coincide con los filtros aplicados.' : 'Comienza registrando tu primera máquina o equipo agrícola.' }}"
        >
            @if($search || $typeFilter !== '')
                <x-slot:action>
                    <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">Limpiar filtros</flux:button>
                </x-slot:action>
            @else
                @can('create', \App\Models\Machinery::class)
                    <x-slot:action>
                        <flux:button href="{{ route('viticulturist.machinery.create') }}" variant="primary" icon="plus">
                            Nueva Maquinaria
                        </flux:button>
                    </x-slot:action>
                @endcan
            @endif
        </x-agro.empty-state>
    @endif

    {{-- Modal Filtros --}}
    <x-agro.modal name="machinery-filters" maxWidth="sm">
        <div class="px-6 py-4 border-b border-zinc-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-agro-100 rounded-lg flex items-center justify-center">
                        <flux:icon icon="adjustments-horizontal" class="size-4 text-agro-600" />
                    </div>
                    <h3 class="text-base font-semibold text-zinc-900">Filtros</h3>
                </div>
                <flux:button x-on:click="$dispatch('close-modal', 'machinery-filters')" variant="ghost" size="sm" icon="x-mark" />
            </div>
        </div>

        <div class="px-6 py-5 space-y-5">
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1.5">Tipo de maquinaria</label>
                <select wire:model.live="typeFilter"
                        class="w-full px-3 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-agro-400 focus:border-transparent">
                    <option value="">Todos los tipos</option>
                    @foreach($types as $type)
                        <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="px-6 py-4 bg-zinc-50 border-t border-zinc-200 flex items-center justify-between rounded-b-2xl">
            <button wire:click="clearFilters" x-on:click="$dispatch('close-modal', 'machinery-filters')"
                    class="text-sm text-zinc-500 hover:text-zinc-700 transition-colors">
                Limpiar filtros
            </button>
            <flux:button x-on:click="$dispatch('close-modal', 'machinery-filters')" variant="primary" size="sm">
                Aplicar
            </flux:button>
        </div>
    </x-agro.modal>

</div>
