<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Maquinaria"
        description="Gestiona tu maquinaria y equipos agrícolas"
    />

    {{-- Stats --}}
    <x-agro.stats-section key="machinery">
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
    </x-agro.stats-section>
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
            <x-agro.search-input wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre, marca, modelo..." />

            {{-- Filtros --}}
            @php $filterCount = ($typeFilter !== '' ? 1 : 0); @endphp
            <x-agro.filter-button modal="machinery-filters" :count="$filterCount" />

            <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

            {{-- Nueva Maquinaria --}}
            @can('create', \App\Models\Machinery::class)
                <flux:button href="{{ roleRoute('viticulturist.machinery.create') }}" variant="primary" icon="plus">
                    Nueva
                </flux:button>
            @endcan

        </div>

        {{-- Active filter chips --}}
        @if($search || $typeFilter !== '')
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs text-zinc-400">Filtros activos:</span>

                @if($search)
                    <x-agro.filter-chip icon="magnifying-glass" :label="'&quot;' . $search . '&quot;'" wireRemove="$set('search', '')" />
                @endif

                @if($typeFilter !== '')
                    <x-agro.filter-chip :label="'Tipo: ' . $typeFilter" wireRemove="$set('typeFilter', '')" />
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
                        <x-agro.card-item-header
                            icon="cog-6-tooth"
                            :title="$item->name"
                            :subtitle="$item->year ? 'Año ' . $item->year : null"
                        >
                            <x-agro.status-badge :status="$item->active" />
                        </x-agro.card-item-header>
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
                        <x-agro.metric-cell
                            label="Marca/Modelo"
                            :value="($item->brand || $item->model) ? trim(($item->brand ?? '') . ' ' . ($item->model ?? '')) : '—'"
                            color="zinc"
                        />
                        <x-agro.metric-cell
                            label="Actividades"
                            :value="$item->activities_count"
                            color="agro"
                        />
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
                                    <a href="{{ roleRoute('viticulturist.machinery.show', $item) }}" class="{{ $btnBase }}" title="Ver maquinaria">
                                        <flux:icon icon="eye" class="size-4" />
                                    </a>
                                @endcan
                                @can('update', $item)
                                    <a href="{{ roleRoute('viticulturist.machinery.edit', $item) }}" class="{{ $btnBase }}" title="Editar">
                                        <flux:icon icon="pencil-square" class="size-4" />
                                    </a>
                                @endcan
                                @can('delete', $item)
                                    @if($item->activities_count === 0)
                                        <button
                                            wire:click="delete({{ $item->id }})"
                                            wire:confirm="¿Eliminar esta maquinaria? Esta acción no se puede deshacer."
                                            class="{{ $btnDanger }}"
                                            title="Eliminar maquinaria"
                                        >
                                            <flux:icon icon="trash" class="size-4" />
                                        </button>
                                    @endif
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
                                        <flux:icon icon="arrow-path" class="animate-spin size-4" />
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
                        <flux:button href="{{ roleRoute('viticulturist.machinery.create') }}" variant="primary" icon="plus">
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
                <x-agro.filter-select label="Tipo de maquinaria" wire:model.live="typeFilter" placeholder="Todos los tipos">
                    @foreach($types as $type)
                        <flux:select.option value="{{ $type }}">{{ $type }}</flux:select.option>
                    @endforeach
                </x-agro.filter-select>
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
