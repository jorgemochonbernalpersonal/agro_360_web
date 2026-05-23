<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Contenedores"
        description="Gestiona tus contenedores y asígnalos a cosechas"
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

    {{-- KPIs --}}
    <x-agro.stats-section key="notebook-containers-stats">
        <div class="grid grid-cols-2 gap-4">
            <x-agro.stat-card label="Capacidad Total" :value="number_format($stats['total_capacity'], 0) . ' kg'" icon="scale" color="green" />
            <x-agro.stat-card label="Capacidad Usada" :value="number_format($stats['total_used'], 0) . ' kg'" icon="archive-box" color="blue" />
        </div>
    </x-agro.stats-section>

    {{-- Toolbar --}}
    @php
        $filterCount = (int)(!empty($selectedCampaign))
                     + (int)(!empty($selectedHarvest))
                     + (int)(!empty($filterAvailability));
    @endphp

    <div class="flex items-center gap-3">

        <x-agro.search-input wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre, número de serie, parcela..." />

        <x-agro.filter-button modal="container-filters" :count="$filterCount" />

        <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

        <flux:button href="{{ roleRoute('viticulturist.containers.create') }}" variant="primary" icon="plus">
            Nuevo Contenedor
        </flux:button>

    </div>

    {{-- Chips de filtros activos --}}
    @if($search || $selectedCampaign || $selectedHarvest || $filterAvailability)
        <div class="flex flex-wrap items-center gap-2">
            @if($search)
                <x-agro.filter-chip icon="magnifying-glass" :label="'&quot;' . $search . '&quot;'" wireRemove="$set('search', '')" />
            @endif
            @if($filterAvailability)
                <x-agro.filter-chip :label="$filterAvailability === 'available' ? 'Disponibles' : 'Asignados'" wireRemove="$set('filterAvailability', '')" />
            @endif
            <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors">
                Limpiar todo
            </button>
        </div>
    @endif

    {{-- Loading skeleton --}}
    <x-agro.loading-grid target="switchTab, search, selectedCampaign, selectedHarvest, filterAvailability, clearFilters, nextPage, previousPage" />

    {{-- Card grid --}}
    <div wire:loading.remove wire:target="switchTab, search, selectedCampaign, selectedHarvest, filterAvailability, clearFilters, nextPage, previousPage">
        @if($containers->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($containers as $container)
                    @php
                        $delay = min($loop->index * 50, 300);
                        $currentHarvest = $container->harvests->first();
                        $pct = $container->getOccupancyPercentage();
                        if ($container->isEmpty())      { $fillColor = 'green'; $fillLabel = 'Vacío'; }
                        elseif ($container->isFull())   { $fillColor = 'blue';  $fillLabel = 'Lleno'; }
                        else                            { $fillColor = 'yellow'; $fillLabel = 'Parcial'; }
                    @endphp
                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="container-{{ $container->id }}"
                    >
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-agro-100 flex items-center justify-center shrink-0">
                                    <flux:icon icon="archive-box" class="size-5 text-agro-600" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-zinc-900 truncate">
                                        {{ $container->name }}
                                        @if($container->serial_number) <span class="text-zinc-400 font-normal">#{{ $container->serial_number }}</span> @endif
                                    </h3>
                                    <p class="text-xs text-zinc-500">Cantidad: {{ $container->quantity }}</p>
                                </div>
                                <flux:badge :color="$fillColor" size="sm" class="shrink-0">{{ $fillLabel }}</flux:badge>
                            </div>
                        </x-slot:header>

                        <div class="flex-1 space-y-4">
                            {{-- Ocupación --}}
                            <x-agro.progress-bar :percentage="$pct" label="Ocupación" :showValues="false" />

                            {{-- Peso --}}
                            <div class="grid grid-cols-2 gap-2">
                                <div class="bg-amber-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-amber-400 uppercase tracking-widest mb-0.5">Usado</p>
                                    <p class="text-lg font-bold text-amber-700 leading-none">
                                        {{ number_format($container->used_capacity, 0) }}
                                        <span class="text-[10px] font-normal text-amber-400">kg</span>
                                    </p>
                                </div>
                                <div class="bg-zinc-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-0.5">Capacidad</p>
                                    <p class="text-lg font-bold text-zinc-600 leading-none">
                                        {{ number_format($container->capacity, 0) }}
                                        <span class="text-[10px] font-normal text-zinc-400">kg</span>
                                    </p>
                                </div>
                            </div>

                            {{-- Cosecha asignada --}}
                            @if($currentHarvest)
                                <div class="space-y-2 text-sm">
                                    <div class="flex items-center justify-between">
                                        <span class="text-zinc-400">Parcela</span>
                                        <span class="text-zinc-700 font-medium truncate ml-2">{{ $currentHarvest->activity->plot->name ?? 'Sin parcela' }}</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-zinc-400">Variedad</span>
                                        <span class="text-zinc-700 font-medium truncate ml-2">{{ $currentHarvest->plotPlanting->grapeVariety->name ?? '—' }}</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-zinc-400">Fecha</span>
                                        <span class="text-zinc-500">{{ $currentHarvest->harvest_start_date->format('d/m/Y') }}</span>
                                    </div>
                                </div>
                            @else
                                <div class="flex items-center gap-2 text-sm text-green-600">
                                    <flux:icon icon="check-circle" class="size-4" />
                                    <span class="font-medium">Disponible</span>
                                </div>
                            @endif
                        </div>

                        <x-slot:footer>
                            <div class="flex items-center justify-end gap-0.5">
                                <x-agro.action-button
                                    variant="edit"
                                    href="{{ roleRoute('viticulturist.containers.edit', $container->id) }}"
                                    title="Editar"
                                />
                                @if($container->archived)
                                    <x-agro.action-button
                                        variant="activate"
                                        wire:click="toggleActive({{ $container->id }})"
                                        title="Activar"
                                    />
                                @else
                                    <x-agro.action-button
                                        variant="deactivate"
                                        wire:click="toggleActive({{ $container->id }})"
                                        title="Desactivar"
                                    />
                                @endif
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <x-agro-pagination :paginator="$containers" />
        @else
            <x-agro.empty-state
                icon="archive-box"
                title="No hay contenedores registrados"
                :description="($search || $selectedCampaign || $selectedHarvest || $filterAvailability) ? 'No se encontraron contenedores con los filtros seleccionados' : 'Los contenedores aparecerán aquí cuando se registren'"
            >
                @if(!$search && !$selectedCampaign && !$selectedHarvest && !$filterAvailability)
                    <x-slot:action>
                        <flux:button href="{{ roleRoute('viticulturist.containers.create') }}" variant="primary" icon="plus">
                            Nuevo Contenedor
                        </flux:button>
                    </x-slot:action>
                @endif
            </x-agro.empty-state>
        @endif
    </div>

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

        <div class="px-6 py-5 space-y-4">
            <x-agro.filter-select label="Disponibilidad" wire:model.live="filterAvailability" placeholder="Todos">
                <flux:select.option value="available">Disponibles</flux:select.option>
                <flux:select.option value="assigned">Asignados</flux:select.option>
            </x-agro.filter-select>
            <x-agro.filter-select label="Campaña" wire:model.live="selectedCampaign" placeholder="Todas las campañas">
                @foreach($campaigns as $campaign)
                    <flux:select.option value="{{ $campaign->id }}">Campaña {{ $campaign->year }}</flux:select.option>
                @endforeach
            </x-agro.filter-select>
            @if($selectedCampaign && $harvests->count() > 0)
                <x-agro.filter-select label="Cosecha" wire:model.live="selectedHarvest" placeholder="Todas las cosechas">
                    @foreach($harvests as $harvest)
                        <flux:select.option value="{{ $harvest->id }}">
                            {{ $harvest->activity->plot->name ?? 'Sin parcela' }} -
                            {{ $harvest->plotPlanting->grapeVariety->name ?? 'Sin variedad' }}
                            ({{ $harvest->harvest_start_date->format('d/m/Y') }})
                        </flux:select.option>
                    @endforeach
                </x-agro.filter-select>
            @endif
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

