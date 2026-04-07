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
    <div x-data="{
        open: localStorage.getItem('notebook-containers-stats-open') !== 'false',
        toggle() {
            this.open = !this.open;
            localStorage.setItem('notebook-containers-stats-open', String(this.open));
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
            <x-agro.stat-card label="Capacidad Total" :value="number_format($stats['total_capacity'], 0) . ' kg'" icon="scale" color="green" />
            <x-agro.stat-card label="Capacidad Usada" :value="number_format($stats['total_used'], 0) . ' kg'" icon="archive-box" color="blue" />
        </div>
        </div>
    </div>

    {{-- Toolbar --}}
    @php
        $filterCount = (int)(!empty($selectedCampaign))
                     + (int)(!empty($selectedHarvest))
                     + (int)(!empty($filterAvailability));
    @endphp

    <div class="flex items-center gap-3">

        <div class="flex-1 relative">
            <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400" />
            </div>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Buscar por nombre, número de serie, parcela..."
                class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition"
            />
        </div>

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

        <flux:button href="{{ roleRoute('viticulturist.containers.create') }}" variant="primary" icon="plus">
            Nuevo Contenedor
        </flux:button>

    </div>

    {{-- Chips de filtros activos --}}
    @if($search || $selectedCampaign || $selectedHarvest || $filterAvailability)
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
            @if($filterAvailability)
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    {{ $filterAvailability === 'available' ? 'Disponibles' : 'Asignados' }}
                    <button wire:click="$set('filterAvailability', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors">
                Limpiar todo
            </button>
        </div>
    @endif

    {{-- Tabla --}}
    @php
        $headers = ['Contenedor', 'Cosecha', 'Peso', 'Estado', 'Acciones'];
    @endphp

    <x-agro.data-table
        :headers="$headers"
        empty-message="No hay contenedores registrados"
        empty-description="{{ ($search || $selectedCampaign || $selectedHarvest || $filterAvailability) ? 'No se encontraron contenedores con los filtros seleccionados' : 'Los contenedores aparecerán aquí cuando se registren' }}"
    >
        @if($containers->count() > 0)
            @foreach($containers as $container)
                <x-agro.table-row wire:key="container-{{ $container->id }}">
                    <x-agro.table-cell>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-agro-50 flex items-center justify-center shrink-0">
                                <flux:icon icon="archive-box" class="size-5 text-agro-600" />
                            </div>
                            <div>
                                <div class="text-sm font-bold text-zinc-900">
                                    {{ $container->name }}
                                    @if($container->serial_number) #{{ $container->serial_number }} @endif
                                </div>
                                <div class="text-xs text-zinc-500 mt-0.5">Cantidad: {{ $container->quantity }}</div>
                            </div>
                        </div>
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        @php $currentHarvest = $container->harvests->first(); @endphp
                        @if($currentHarvest)
                            <div>
                                <div class="text-sm font-medium text-zinc-900">{{ $currentHarvest->activity->plot->name ?? 'Sin parcela' }}</div>
                                <div class="text-xs text-zinc-500 mt-0.5">{{ $currentHarvest->plotPlanting->grapeVariety->name ?? 'Sin variedad' }}</div>
                                <div class="text-xs text-zinc-400 mt-0.5">{{ $currentHarvest->harvest_start_date->format('d/m/Y') }}</div>
                            </div>
                        @else
                            <flux:badge color="green" size="sm">Disponible</flux:badge>
                        @endif
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        <div class="text-sm font-bold text-zinc-900">
                            {{ number_format($container->used_capacity, 2) }} / {{ number_format($container->capacity, 2) }} kg
                        </div>
                        <div class="text-xs text-zinc-500 mt-0.5">{{ number_format($container->getOccupancyPercentage(), 1) }}% ocupado</div>
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        <div class="space-y-1">
                            <x-agro.status-badge :active="!$container->archived" />
                            @php
                                if ($container->isEmpty())      { $fillColor = 'green'; $fillLabel = 'Vacío'; }
                                elseif ($container->isFull())   { $fillColor = 'blue';  $fillLabel = 'Lleno'; }
                                else                            { $fillColor = 'yellow'; $fillLabel = 'Parcial'; }
                            @endphp
                            <flux:badge :color="$fillColor" size="sm">{{ $fillLabel }}</flux:badge>
                        </div>
                    </x-agro.table-cell>
                    <x-agro.table-cell align="right">
                        <div class="flex items-center justify-end gap-1">
                            <x-agro.action-button variant="edit" href="{{ roleRoute('viticulturist.containers.edit', $container->id) }}" />
                            <x-agro.action-button
                                :variant="$container->archived ? 'activate' : 'deactivate'"
                                wireClick="toggleActive({{ $container->id }})"
                            />
                        </div>
                    </x-agro.table-cell>
                </x-agro.table-row>
            @endforeach
            <x-slot name="pagination">{{ $containers->links() }}</x-slot>
        @else
            <x-slot name="emptyAction">
                <flux:button href="{{ roleRoute('viticulturist.containers.create') }}" variant="primary" icon="plus">
                    Nuevo Contenedor
                </flux:button>
            </x-slot>
        @endif
    </x-agro.data-table>

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
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Disponibilidad</label>
                <select wire:model.live="filterAvailability"
                        class="w-full px-3 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-agro-400 focus:border-transparent">
                    <option value="">Todos</option>
                    <option value="available">Disponibles</option>
                    <option value="assigned">Asignados</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Campaña</label>
                <select wire:model.live="selectedCampaign"
                        class="w-full px-3 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-agro-400 focus:border-transparent">
                    <option value="">Todas las campañas</option>
                    @foreach($campaigns as $campaign)
                        <option value="{{ $campaign->id }}">Campaña {{ $campaign->year }}</option>
                    @endforeach
                </select>
            </div>
            @if($selectedCampaign && $harvests->count() > 0)
                <div>
                    <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Cosecha</label>
                    <select wire:model.live="selectedHarvest"
                            class="w-full px-3 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-agro-400 focus:border-transparent">
                        <option value="">Todas las cosechas</option>
                        @foreach($harvests as $harvest)
                            <option value="{{ $harvest->id }}">
                                {{ $harvest->activity->plot->name ?? 'Sin parcela' }} -
                                {{ $harvest->plotPlanting->grapeVariety->name ?? 'Sin variedad' }}
                                ({{ $harvest->harvest_start_date->format('d/m/Y') }})
                            </option>
                        @endforeach
                    </select>
                </div>
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

