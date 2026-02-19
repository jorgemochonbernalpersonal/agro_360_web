<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Contenedores"
        description="Gestiona tus contenedores. Puedes crearlos independientemente y asignarlos a cosechas."
    >
        <x-slot:actions>
            <flux:button href="{{ route('viticulturist.digital-notebook.containers.create') }}" variant="primary" icon="plus">
                Nuevo Contenedor
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <x-agro.card :padding="false">
        <div class="border-b border-zinc-200">
            <nav class="flex -mb-px">
                <button wire:click="switchTab('active')"
                    class="group inline-flex items-center gap-2 px-6 py-4 border-b-2 font-medium text-sm transition-colors
                        {{ $currentTab === 'active' ? 'border-agro-700 text-agro-700' : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Activos</span>
                    @if($stats['active'] > 0)
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $currentTab === 'active' ? 'bg-agro-700 text-white' : 'bg-zinc-200 text-zinc-700' }}">{{ $stats['active'] }}</span>
                    @endif
                </button>
                <button wire:click="switchTab('inactive')"
                    class="group inline-flex items-center gap-2 px-6 py-4 border-b-2 font-medium text-sm transition-colors
                        {{ $currentTab === 'inactive' ? 'border-agro-700 text-agro-700' : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Inactivos</span>
                    @if($stats['inactive'] > 0)
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $currentTab === 'inactive' ? 'bg-agro-700 text-white' : 'bg-zinc-200 text-zinc-700' }}">{{ $stats['inactive'] }}</span>
                    @endif
                </button>
                <button wire:click="switchTab('statistics')"
                    class="group inline-flex items-center gap-2 px-6 py-4 border-b-2 font-medium text-sm transition-colors
                        {{ $currentTab === 'statistics' ? 'border-agro-700 text-agro-700' : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span>Estadisticas</span>
                </button>
            </nav>
        </div>

        <div class="p-6">
            @if($currentTab === 'active' || $currentTab === 'inactive')
                {{-- Stats --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <x-agro.stat-card label="Total Contenedores" :value="number_format($stats['total'])" color="blue"
                        icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>' />
                    <x-agro.stat-card label="Capacidad Total" :value="number_format($stats['total_capacity'], 2) . ' kg'" color="green"
                        icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>' />
                    <x-agro.stat-card label="Capacidad Usada" :value="number_format($stats['total_used'], 2) . ' kg'" color="purple"
                        icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>' />
                </div>

                {{-- Filtros --}}
                <div class="flex flex-wrap items-center gap-3 mb-6">
                    <x-agro.filter-input wire:model.live.debounce.300ms="search" placeholder="Buscar por numero, ubicacion, parcela..." />
                    <x-agro.filter-select wire:model.live="selectedCampaign">
                        <option value="">Todas las campanas</option>
                        @foreach($campaigns as $campaign)
                            <option value="{{ $campaign->id }}">Campana {{ $campaign->year }}</option>
                        @endforeach
                    </x-agro.filter-select>
                    @if($selectedCampaign)
                        <x-agro.filter-select wire:model.live="selectedHarvest">
                            <option value="">Todas las cosechas</option>
                            @foreach($harvests as $harvest)
                                <option value="{{ $harvest->id }}">
                                    {{ $harvest->activity->plot->name ?? 'Sin parcela' }} -
                                    {{ $harvest->plotPlanting->grapeVariety->name ?? 'Sin variedad' }}
                                    ({{ $harvest->harvest_start_date->format('d/m/Y') }})
                                </option>
                            @endforeach
                        </x-agro.filter-select>
                    @endif
                    <x-agro.filter-select wire:model.live="filterAvailability">
                        <option value="">Todos</option>
                        <option value="available">Disponibles</option>
                        <option value="assigned">Asignados</option>
                    </x-agro.filter-select>
                    @if($search || $selectedCampaign || $selectedHarvest || $filterType)
                        <flux:button wire:click="$set('search', ''); $set('selectedCampaign', ''); $set('selectedHarvest', ''); $set('filterType', '')" variant="ghost" size="sm">
                            Limpiar Filtros
                        </flux:button>
                    @endif
                </div>

                @php
                    $headers = [
                        ['label' => 'Contenedor', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>'],
                        ['label' => 'Cosecha', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'],
                        ['label' => 'Peso', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>'],
                        ['label' => 'Estado', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'],
                        'Acciones',
                    ];
                @endphp

                <x-agro.data-table
                    :headers="$headers"
                    empty-message="No hay contenedores registrados"
                    :empty-description="($search || $selectedCampaign || $selectedHarvest || $filterType || $filterAvailability) ? 'No se encontraron contenedores con los filtros seleccionados' : 'Los contenedores apareceran aqui cuando se registren'"
                >
                    @if($containers->count() > 0)
                        @foreach($containers as $container)
                            <x-agro.table-row wire:key="container-{{ $container->id }}">
                                <x-agro.table-cell>
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-agro-400 to-agro-500 flex items-center justify-center shadow-sm">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-zinc-900">
                                                {{ $container->name }}
                                                @if($container->serial_number)
                                                    #{{ $container->serial_number }}
                                                @endif
                                            </div>
                                            <div class="text-xs text-zinc-500 mt-1">Cantidad: {{ $container->quantity }}</div>
                                        </div>
                                    </div>
                                </x-agro.table-cell>
                                <x-agro.table-cell>
                                    @php $currentHarvest = $container->harvests->first(); @endphp
                                    @if($currentHarvest)
                                        <div>
                                            <div class="text-sm font-medium text-zinc-900">{{ $currentHarvest->activity->plot->name ?? 'Sin parcela' }}</div>
                                            <div class="text-xs text-zinc-500 mt-1">{{ $currentHarvest->plotPlanting->grapeVariety->name ?? 'Sin variedad' }}</div>
                                            <div class="text-xs text-zinc-400 mt-1">{{ $currentHarvest->harvest_start_date->format('d/m/Y') }}</div>
                                        </div>
                                    @else
                                        <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">Disponible</span>
                                    @endif
                                </x-agro.table-cell>
                                <x-agro.table-cell>
                                    <div class="text-sm font-bold text-zinc-900">
                                        {{ number_format($container->used_capacity, 2) }} / {{ number_format($container->capacity, 2) }} kg
                                    </div>
                                    <div class="text-xs text-zinc-500 mt-1">{{ number_format($container->getOccupancyPercentage(), 1) }}% ocupado</div>
                                </x-agro.table-cell>
                                <x-agro.table-cell>
                                    <div class="space-y-1">
                                        <x-agro.status-badge :active="!$container->archived" />
                                        @php
                                            if ($container->isEmpty()) { $color = 'bg-green-100 text-green-800'; $label = 'Vacio'; }
                                            elseif ($container->isFull()) { $color = 'bg-blue-100 text-blue-800'; $label = 'Lleno'; }
                                            else { $color = 'bg-yellow-100 text-yellow-800'; $label = 'Parcial'; }
                                        @endphp
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $color }}">{{ $label }}</span>
                                    </div>
                                </x-agro.table-cell>
                                <x-agro.table-cell>
                                    <div class="flex items-center justify-end gap-2">
                                        <x-agro.action-button variant="edit" href="{{ route('viticulturist.digital-notebook.containers.edit', $container->id) }}" />
                                        <x-agro.action-button
                                            :variant="$container->archived ? 'activate' : 'deactivate'"
                                            wireClick="toggleActive({{ $container->id }})"
                                            wireTarget="toggleActive({{ $container->id }})"
                                        />
                                    </div>
                                </x-agro.table-cell>
                            </x-agro.table-row>
                        @endforeach
                        <x-slot name="pagination">{{ $containers->links() }}</x-slot>
                    @else
                        <x-slot name="emptyAction">
                            <flux:button href="{{ route('viticulturist.digital-notebook.containers.create') }}" variant="primary" icon="plus">
                                Crear Primer Contenedor
                            </flux:button>
                        </x-slot>
                    @endif
                </x-agro.data-table>
            @endif

            @if($currentTab === 'statistics')
                <div class="space-y-6">
                    <div class="flex justify-end">
                        <flux:select wire:model.live="yearFilter" class="w-auto">
                            @for($year = now()->year; $year >= now()->year - 5; $year--)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endfor
                        </flux:select>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <x-agro.stat-card label="Capacidad Total" :value="number_format($advancedStats['totalCapacity'] ?? 0, 2) . ' kg'" color="green" icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>' />
                        <x-agro.stat-card label="Capacidad Usada" :value="number_format($advancedStats['totalUsed'] ?? 0, 2) . ' kg'" color="blue" icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>' />
                        <x-agro.stat-card label="Activos" :value="$stats['active']" color="purple" icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>' />
                        <x-agro.stat-card label="Capacidad Media" :value="number_format($advancedStats['avgCapacityPerContainer'] ?? 0, 2) . ' kg'" color="orange" icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10"/>' />
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <x-agro.card>
                            <h3 class="text-lg font-bold text-zinc-900 mb-4">Distribucion por Estado</h3>
                            @php
                                $total = ($advancedStats['emptyContainers'] ?? 0) + ($advancedStats['partialContainers'] ?? 0) + ($advancedStats['fullContainers'] ?? 0);
                            @endphp
                            <div class="space-y-4">
                                @foreach([
                                    ['label' => 'Vacios', 'key' => 'emptyContainers', 'color' => 'bg-green-500'],
                                    ['label' => 'Parciales', 'key' => 'partialContainers', 'color' => 'bg-yellow-500'],
                                    ['label' => 'Llenos', 'key' => 'fullContainers', 'color' => 'bg-blue-500'],
                                ] as $item)
                                    @php $pct = $total > 0 ? (($advancedStats[$item['key']] ?? 0) / $total) * 100 : 0; @endphp
                                    <div>
                                        <div class="flex justify-between mb-2">
                                            <span class="text-sm font-medium text-zinc-700">{{ $item['label'] }}</span>
                                            <span class="text-sm font-bold text-zinc-900">{{ $advancedStats[$item['key']] ?? 0 }} ({{ number_format($pct, 1) }}%)</span>
                                        </div>
                                        <div class="w-full bg-zinc-200 rounded-full h-3">
                                            <div class="{{ $item['color'] }} h-3 rounded-full" style="width: {{ $pct }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </x-agro.card>

                        <x-agro.card>
                            <h3 class="text-lg font-bold text-zinc-900 mb-4">Top 10 por Capacidad</h3>
                            <div class="space-y-3">
                                @forelse(($advancedStats['topContainers'] ?? []) as $index => $container)
                                    <div class="flex items-center justify-between p-3 bg-zinc-50 rounded-lg">
                                        <div class="flex items-center gap-3">
                                            <span class="w-7 h-7 rounded-full bg-agro-500 text-white flex items-center justify-center font-bold text-sm">{{ $index + 1 }}</span>
                                            <div>
                                                <p class="font-semibold text-zinc-900">{{ $container['name'] }}</p>
                                                <p class="text-xs text-zinc-500">{{ number_format($container['used'], 2) }}/{{ number_format($container['capacity'], 2) }} kg</p>
                                            </div>
                                        </div>
                                        <span class="font-bold text-agro-700">{{ number_format($container['capacity'], 2) }} kg</span>
                                    </div>
                                @empty
                                    <p class="text-zinc-500 text-center py-4">No hay contenedores</p>
                                @endforelse
                            </div>
                        </x-agro.card>
                    </div>
                </div>
            @endif
        </div>
    </x-agro.card>
</div>
