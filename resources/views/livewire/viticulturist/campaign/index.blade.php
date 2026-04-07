<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Gestión de Campañas"
        description="Administra y visualiza todas tus campañas vitícolas"
    />

    {{-- Stats --}}
    <div x-data="{
        open: localStorage.getItem('campaigns-stats-open') !== 'false',
        toggle() {
            this.open = !this.open;
            localStorage.setItem('campaigns-stats-open', String(this.open));
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
                    label="Total campañas"
                    :value="$stats['total']"
                    description="'Historial completo'"
                    icon="calendar-days"
                    color="agro"
                />
                <x-agro.stat-card
                    label="Activas"
                    :value="$stats['active']"
                    description="'Campañas en curso'"
                    icon="check-circle"
                    color="agro"
                />
                <x-agro.stat-card
                    label="Inactivas"
                    :value="$stats['inactive']"
                    description="$stats['inactive'] > 0 ? 'Campañas cerradas' : 'Todas activas'"
                    icon="archive-box"
                    color="zinc"
                />
                <x-agro.stat-card
                    label="Año actual"
                    :value="(string) now()->year"
                    description="'Campaña en progreso'"
                    icon="sun"
                    color="orange"
                />
            </div>
        </div>
    </div>
    {{-- Stats --}}
    <div x-data="{
        open: localStorage.getItem('campaigns-stats-open') !== 'false',
        toggle() {
            this.open = !this.open;
            localStorage.setItem('campaigns-stats-open', String(this.open));
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
                    label="Total campañas"
                    :value="$stats['total']"
                    description="'Historial completo'"
                    icon="calendar-days"
                    color="agro"
                />
                <x-agro.stat-card
                    label="Activas"
                    :value="$stats['active']"
                    description="'Campañas en curso'"
                    icon="check-circle"
                    color="agro"
                />
                <x-agro.stat-card
                    label="Inactivas"
                    :value="$stats['inactive']"
                    description="$stats['inactive'] > 0 ? 'Campañas cerradas' : 'Todas activas'"
                    icon="archive-box"
                    color="zinc"
                />
                <x-agro.stat-card
                    label="Año actual"
                    :value="(string) date('Y')"
                    description="'Campaña en progreso'"
                    icon="sun"
                    color="orange"
                />
            </div>
        </div>
    </div>
    {{-- Tabs --}}
    <x-agro.tabs
        :tabs="[
            'active'   => ['label' => 'Activas',   'count' => $stats['active']],
            'inactive' => ['label' => 'Inactivas', 'count' => $stats['inactive']],
        ]"
        :active="$currentTab"
        wireMethod="switchTab"
    />

    {{-- Toolbar --}}
    @php $filterCount = (int)(!empty($yearFilter)); @endphp

    <div class="flex items-center gap-3">

        <div class="flex-1 relative">
            <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400" />
            </div>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Buscar por nombre o descripción..."
                class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition"
            />
        </div>

        <button
            x-on:click="$dispatch('open-modal', 'campaign-filters')"
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

        @can('create', \App\Models\Campaign::class)
            <flux:button href="{{ roleRoute('viticulturist.campaign.create') }}" variant="primary" icon="plus">
                Nueva Campaña
            </flux:button>
        @endcan

    </div>

    {{-- Chips de filtros activos --}}
    @if($search || $yearFilter)
        <div class="flex flex-wrap items-center gap-2">
            @if($search)
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="magnifying-glass" class="size-3" />
                    "{{ $search }}"
                    <button wire:click="$set('search', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            @if($yearFilter)
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="calendar" class="size-3" />
                    Año {{ $yearFilter }}
                    <button wire:click="$set('yearFilter', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors">
                Limpiar todo
            </button>
        </div>
    @endif

    {{-- Card grid --}}
    @if($campaigns->count() > 0)
        <div
            class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6"
            wire:loading.class="opacity-60 pointer-events-none"
            wire:target="search, yearFilter, clearFilters, switchTab"
        >
            @foreach($campaigns as $i => $campaign)
                @php
                    $btnBase    = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors';
                    $btnDanger  = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-500 hover:bg-red-50 transition-colors';
                    $btnSuccess = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-agro-600 hover:bg-agro-50 transition-colors';
                    $btnDisabled = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-300 cursor-not-allowed';
                @endphp

                <x-agro.card
                    wire:key="campaign-{{ $campaign->id }}"
                    class="animate-fade-in-up hover:-translate-y-1 {{ !$campaign->active ? 'opacity-70' : '' }}"
                    style="animation-delay: {{ min($i * 50, 400) }}ms"
                >
                    <x-slot:header>
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-agro-50 rounded-full flex items-center justify-center shrink-0">
                                <flux:icon icon="calendar" class="size-4 text-agro-600" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-zinc-900 text-sm truncate leading-tight" title="{{ $campaign->name }}">{{ $campaign->name }}</p>
                                @if($campaign->description)
                                    <p class="text-xs text-zinc-400 leading-tight mt-0.5 truncate" title="{{ $campaign->description }}">{{ $campaign->description }}</p>
                                @endif
                            </div>
                            <flux:badge color="{{ $campaign->active ? 'green' : null }}" size="sm" class="shrink-0">
                                {{ $campaign->active ? 'Activa' : 'Inactiva' }}
                            </flux:badge>
                        </div>
                    </x-slot:header>

                    {{-- Año + Período --}}
                    <div class="grid grid-cols-2 gap-2 mb-3">
                        <div class="bg-agro-50 rounded-xl p-2.5">
                            <p class="text-[10px] text-agro-600 font-medium uppercase tracking-wide mb-0.5">Año</p>
                            <p class="text-sm font-bold text-agro-700">{{ $campaign->year }}</p>
                        </div>
                        <div class="bg-zinc-50 rounded-xl p-2.5">
                            <p class="text-[10px] text-zinc-500 font-medium uppercase tracking-wide mb-0.5">Actividades</p>
                            <p class="text-sm font-bold text-zinc-700">{{ $campaign->activities_count }}</p>
                        </div>
                    </div>

                    {{-- Período --}}
                    @if($campaign->start_date && $campaign->end_date)
                        <div class="flex items-center gap-2">
                            <flux:icon icon="calendar-days" class="size-3.5 text-zinc-400 shrink-0" />
                            <span class="text-xs text-zinc-600">
                                {{ $campaign->start_date->format('d/m/Y') }} — {{ $campaign->end_date->format('d/m/Y') }}
                            </span>
                        </div>
                    @else
                        <p class="text-xs text-zinc-400 italic">Sin período definido</p>
                    @endif

                    <x-slot:footer>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1">
                                @can('view', $campaign)
                                    <a href="{{ roleRoute('viticulturist.campaign.show', $campaign) }}" class="{{ $btnBase }}" title="Ver campaña">
                                        <flux:icon icon="eye" class="size-4" />
                                    </a>
                                @endcan
                                @can('update', $campaign)
                                    <a href="{{ roleRoute('viticulturist.campaign.edit', $campaign) }}" class="{{ $btnBase }}" title="Editar">
                                        <flux:icon icon="pencil-square" class="size-4" />
                                    </a>
                                @endcan
                            </div>
                            <div class="flex items-center gap-1">
                                @can('update', $campaign)
                                    <button
                                        wire:click="toggleActive({{ $campaign->id }})"
                                        class="{{ $campaign->active ? $btnDanger : $btnSuccess }}"
                                        title="{{ $campaign->active ? 'Desactivar' : 'Activar' }}"
                                    >
                                        <flux:icon icon="{{ $campaign->active ? 'no-symbol' : 'check-circle' }}" class="size-4" />
                                    </button>
                                @endcan
                                @can('delete', $campaign)
                                    @if($campaign->activities_count === 0)
                                        <button
                                            wire:click="delete({{ $campaign->id }})"
                                            wire:confirm="¿Estás seguro de eliminar esta campaña?"
                                            class="{{ $btnDanger }}"
                                            title="Eliminar"
                                        >
                                            <flux:icon icon="trash" class="size-4" />
                                        </button>
                                    @else
                                        <span class="{{ $btnDisabled }}" title="No se puede eliminar: tiene actividades">
                                            <flux:icon icon="trash" class="size-4" />
                                        </span>
                                    @endif
                                @endcan
                            </div>
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        @if($campaigns->hasPages())
            <div class="flex justify-center">{{ $campaigns->links() }}</div>
        @endif

    @else
        <x-agro.empty-state
            icon="calendar"
            message="{{ $currentTab === 'active' ? 'No hay campañas activas' : 'No hay campañas inactivas' }}"
            description="{{ $search || $yearFilter ? 'Ninguna campaña coincide con los filtros aplicados.' : ($currentTab === 'active' ? 'Crea tu primera campaña para empezar a gestionar tus temporadas.' : 'Las campañas desactivadas aparecerán aquí.') }}"
        >
            @if($search || $yearFilter)
                <x-slot:action>
                    <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">Limpiar filtros</flux:button>
                </x-slot:action>
            @elseif($currentTab === 'active')
                <x-slot:action>
                    @can('create', \App\Models\Campaign::class)
                        <flux:button href="{{ roleRoute('viticulturist.campaign.create') }}" variant="primary" icon="plus">
                            Nueva Campaña
                        </flux:button>
                    @endcan
                </x-slot:action>
            @endif
        </x-agro.empty-state>
    @endif

    {{-- Modal Filtros --}}
    <x-agro.modal name="campaign-filters" maxWidth="sm">
        <div class="px-6 py-4 border-b border-zinc-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-agro-100 rounded-lg flex items-center justify-center">
                        <flux:icon icon="adjustments-horizontal" class="size-4 text-agro-600" />
                    </div>
                    <h3 class="text-base font-semibold text-zinc-900">Filtros</h3>
                </div>
                <flux:button x-on:click="$dispatch('close-modal', 'campaign-filters')" variant="ghost" size="sm" icon="x-mark" />
            </div>
        </div>

        <div class="px-6 py-5">
            <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Año</label>
            <select wire:model.live="yearFilter"
                    class="w-full px-3 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-agro-400 focus:border-transparent">
                <option value="">Todos los años</option>
                @foreach($years as $year)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endforeach
            </select>
        </div>

        <div class="px-6 py-4 bg-zinc-50 border-t border-zinc-200 flex items-center justify-between rounded-b-2xl">
            <button wire:click="clearFilters" x-on:click="$dispatch('close-modal', 'campaign-filters')"
                    class="text-sm text-zinc-500 hover:text-zinc-700 transition-colors">
                Limpiar filtros
            </button>
            <flux:button x-on:click="$dispatch('close-modal', 'campaign-filters')" variant="primary" size="sm">
                Aplicar
            </flux:button>
        </div>
    </x-agro.modal>

</div>
