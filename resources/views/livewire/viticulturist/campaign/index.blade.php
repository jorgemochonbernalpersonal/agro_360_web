<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Gestión de Campañas"
        description="Administra y visualiza todas tus campañas vitícolas"
    />

    {{-- Stats --}}
    <x-agro.stats-section key="campaigns">
        <x-agro.stat-card
            label="Total campañas"
            :value="$stats['total']"
            description="Historial completo"
            icon="calendar-days"
            color="agro"
        />
        <x-agro.stat-card
            label="Activas"
            :value="$stats['active']"
            description="Campañas en curso"
            icon="check-circle"
            color="agro"
        />
        <x-agro.stat-card
            label="Inactivas"
            :value="$stats['inactive']"
            :description="$stats['inactive'] > 0 ? 'Campañas cerradas' : 'Todas activas'"
            icon="archive-box"
            color="zinc"
        />
        <x-agro.stat-card
            label="Año actual"
            :value="(string) now()->year"
            description="Campaña en progreso"
            icon="sun"
            color="orange"
        />
    </x-agro.stats-section>
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

        <x-agro.search-input wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre o descripción..." />

        <x-agro.filter-button modal="campaign-filters" :count="$filterCount" />

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
                <x-agro.filter-chip icon="magnifying-glass" :label="'&quot;' . $search . '&quot;'" wireRemove="$set('search', '')" />
            @endif
            @if($yearFilter)
                <x-agro.filter-chip icon="calendar" :label="'Año ' . $yearFilter" wireRemove="$set('yearFilter', '')" />
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

                <x-agro.card
                    wire:key="campaign-{{ $campaign->id }}"
                    class="animate-fade-in-up hover:-translate-y-1 {{ !$campaign->active ? 'opacity-70' : '' }}"
                    style="animation-delay: {{ min($i * 50, 400) }}ms"
                >
                    <x-slot:header>
                        <x-agro.card-item-header
                            icon="calendar"
                            :title="$campaign->name"
                            :subtitle="$campaign->description ?? null"
                            iconBg="bg-agro-50"
                            iconColor="text-agro-600"
                        >
                            <flux:badge color="{{ $campaign->active ? 'green' : null }}" size="sm">
                                {{ $campaign->active ? 'Activa' : 'Inactiva' }}
                            </flux:badge>
                        </x-agro.card-item-header>
                    </x-slot:header>

                    {{-- Año + Período --}}
                    <div class="grid grid-cols-2 gap-2 mb-3">
                        <x-agro.metric-cell label="Año" :value="$campaign->year" color="agro" />
                        <x-agro.metric-cell label="Actividades" :value="$campaign->activities_count" color="zinc" />
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
                                    <x-agro.action-button
                                        variant="view"
                                        href="{{ roleRoute('viticulturist.campaign.show', $campaign) }}"
                                        title="Ver campaña"
                                    />
                                @endcan
                                @can('update', $campaign)
                                    <x-agro.action-button
                                        variant="edit"
                                        href="{{ roleRoute('viticulturist.campaign.edit', $campaign) }}"
                                        title="Editar"
                                    />
                                @endcan
                            </div>
                            <div class="flex items-center gap-1">
                                @can('update', $campaign)
                                    <x-agro.action-button
                                        variant="{{ $campaign->active ? 'deactivate' : 'activate' }}"
                                        wire:click="toggleActive({{ $campaign->id }})"
                                        title="{{ $campaign->active ? 'Desactivar' : 'Activar' }}"
                                    />
                                @endcan
                                @can('delete', $campaign)
                                    @if($campaign->activities_count === 0)
                                        <x-agro.action-button
                                            variant="delete"
                                            wire:click="delete({{ $campaign->id }})"
                                            wire:confirm="¿Estás seguro de eliminar esta campaña?"
                                            title="Eliminar"
                                        />
                                    @else
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-300 cursor-not-allowed" title="No se puede eliminar: tiene actividades">
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

        <x-agro-pagination :paginator="$campaigns" />

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
            <x-agro.filter-select label="Año" wire:model.live="yearFilter" placeholder="Todos los años">
                @foreach($years as $year)
                    <flux:select.option value="{{ $year }}">{{ $year }}</flux:select.option>
                @endforeach
            </x-agro.filter-select>
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
