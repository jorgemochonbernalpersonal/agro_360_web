<div class="space-y-6 animate-fade-in">
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>';
    @endphp
    <x-page-header
        :icon="$icon"
        title="Contenedores"
        description="Gestiona tus contenedores. Puedes crearlos independientemente y asignarlos a cosechas."
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    >
        <x-slot:actionButton>
            <x-button href="{{ route('viticulturist.digital-notebook.containers.create') }}" variant="primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nuevo Contenedor
            </x-button>
        </x-slot:actionButton>
    </x-page-header>

    <!-- Tabs Navigation -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px">
                <button 
                    wire:click="switchTab('active')"
                    class="group inline-flex items-center gap-2 px-6 py-4 border-b-2 font-medium text-sm transition-colors
                        {{ $currentTab === 'active' ? 'border-[var(--color-agro-green-dark)] text-[var(--color-agro-green-dark)]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Activos</span>
                    @if($stats['active'] > 0)
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $currentTab === 'active' ? 'bg-[var(--color-agro-green-dark)] text-white' : 'bg-gray-200 text-gray-700' }}">
                            {{ $stats['active'] }}
                        </span>
                    @endif
                </button>
                
                <button 
                    wire:click="switchTab('inactive')"
                    class="group inline-flex items-center gap-2 px-6 py-4 border-b-2 font-medium text-sm transition-colors
                        {{ $currentTab === 'inactive' ? 'border-[var(--color-agro-green-dark)] text-[var(--color-agro-green-dark)]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Inactivos</span>
                    @if($stats['inactive'] > 0)
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $currentTab === 'inactive' ? 'bg-[var(--color-agro-green-dark)] text-white' : 'bg-gray-200 text-gray-700' }}">
                            {{ $stats['inactive'] }}
                        </span>
                    @endif
                </button>

                <button 
                    wire:click="switchTab('statistics')"
                    class="group inline-flex items-center gap-2 px-6 py-4 border-b-2 font-medium text-sm transition-colors
                        {{ $currentTab === 'statistics' ? 'border-[var(--color-agro-green-dark)] text-[var(--color-agro-green-dark)]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span>Estadísticas</span>
                </button>
            </nav>
        </div>

        <div class="p-6">
            {{-- ACTIVE/INACTIVE TABS --}}
            @if($currentTab === 'active' || $currentTab === 'inactive')
            <!-- Estadísticas -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Contenedores</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($stats['total']) }}</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Capacidad Total</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($stats['total_capacity'], 2) }} kg</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Capacidad Usada</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($stats['total_used'], 2) }} kg</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            </div>
            </div>

            <!-- Filtros -->
    <x-filter-section title="Filtros de Búsqueda" color="green">
        <x-filter-input 
            wire:model.live.debounce.300ms="search" 
            placeholder="Buscar por número, ubicación, parcela..."
        />
        <x-filter-select wire:model.live="selectedCampaign">
            <option value="">Todas las campañas</option>
            @foreach($campaigns as $campaign)
                <option value="{{ $campaign->id }}">Campaña {{ $campaign->year }}</option>
            @endforeach
        </x-filter-select>
        @if($selectedCampaign)
            <x-filter-select wire:model.live="selectedHarvest">
                <option value="">Todas las cosechas</option>
                @foreach($harvests as $harvest)
                    <option value="{{ $harvest->id }}">
                        {{ $harvest->activity->plot->name ?? 'Sin parcela' }} - 
                        {{ $harvest->plotPlanting->grapeVariety->name ?? 'Sin variedad' }} 
                        ({{ $harvest->harvest_start_date->format('d/m/Y') }})
                    </option>
                @endforeach
            </x-filter-select>
        @endif
        <x-filter-select wire:model.live="filterAvailability">
            <option value="">Todos</option>
            <option value="available">Disponibles</option>
            <option value="assigned">Asignados</option>
        </x-filter-select>
        {{-- Filtro por tipo deshabilitado temporalmente hasta que se implemente la tabla de tipos --}}
        <x-slot:actions>
            @if($search || $selectedCampaign || $selectedHarvest || $filterType)
                <x-button wire:click="$set('search', ''); $set('selectedCampaign', ''); $set('selectedHarvest', ''); $set('filterType', '')" variant="ghost" size="sm">
                    Limpiar Filtros
                </x-button>
            @endif
        </x-slot:actions>
    </x-filter-section>

    <!-- Tabla de Contenedores -->
    @php
        $headers = [
            ['label' => 'Contenedor', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>'],
            ['label' => 'Cosecha', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'],
            ['label' => 'Peso', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>'],
            ['label' => 'Ubicación', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'],
            ['label' => 'Estado', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'],
            ['label' => 'Fechas', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'],
            'Acciones',
        ];
    @endphp

    <x-data-table 
        :headers="$headers" 
        empty-message="No hay contenedores registrados" 
        empty-description="{{ ($search || $selectedCampaign || $selectedHarvest || $filterType || $filterAvailability) ? 'No se encontraron contenedores con los filtros seleccionados' : 'Los contenedores aparecerán aquí cuando se registren en las cosechas' }}"
        color="green"
    >
        @if($containers->count() > 0)
            @foreach($containers as $container)
                <x-table-row wire:key="container-{{ $container->id }}">
                    <x-table-cell>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-[var(--color-agro-green-light)] to-[var(--color-agro-green)] flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform duration-200">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-gray-900">
                                    {{ $container->name }}
                                    @if($container->serial_number)
                                        #{{ $container->serial_number }}
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    Cantidad: {{ $container->quantity }}
                                </div>
                            </div>
                        </div>
                    </x-table-cell>
                    <x-table-cell>
                        @php
                            $currentHarvest = $container->harvests->first();
                        @endphp
                        @if($currentHarvest)
                            <div>
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $currentHarvest->activity->plot->name ?? 'Sin parcela' }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ $currentHarvest->plotPlanting->grapeVariety->name ?? 'Sin variedad' }}
                                </div>
                                <div class="text-xs text-gray-400 mt-1">
                                    {{ $currentHarvest->harvest_start_date->format('d/m/Y') }}
                                </div>
                            </div>
                        @else
                            <div class="flex items-center gap-2">
                                <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                    Disponible
                                </span>
                            </div>
                        @endif
                    </x-table-cell>
                    <x-table-cell>
                        <div>
                            <div class="text-sm font-bold text-gray-900">
                                {{ number_format($container->used_capacity, 2) }} / {{ number_format($container->capacity, 2) }} kg
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ number_format($container->getOccupancyPercentage(), 1) }}% ocupado
                            </div>
                        </div>
                    </x-table-cell>
                    <x-table-cell>
                        @if($container->currentState && $container->currentState->location)
                            <span class="text-sm text-gray-700">{{ $container->currentState->location }}</span>
                        @else
                            <span class="text-sm text-gray-400">-</span>
                        @endif
                    </x-table-cell>
                    <x-table-cell>
                        <div class="space-y-1">
                            {{-- Estado activo/inactivo --}}
                            <x-status-badge :active="!$container->archived" />
                            {{-- Estado de capacidad --}}
                            @php
                                if ($container->isEmpty()) {
                                    $color = 'bg-green-100 text-green-800';
                                    $label = 'Vacío';
                                } elseif ($container->isFull()) {
                                    $color = 'bg-blue-100 text-blue-800';
                                    $label = 'Lleno';
                                } else {
                                    $color = 'bg-yellow-100 text-yellow-800';
                                    $label = 'Parcial';
                                }
                            @endphp
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $color }}">
                                {{ $label }}
                            </span>
                        </div>
                    </x-table-cell>
                    <x-table-cell>
                        <div class="text-xs text-gray-600">
                            @if($container->purchase_date)
                                <div>Compra: {{ $container->purchase_date->format('d/m/Y') }}</div>
                            @endif
                            @if($container->next_maintenance_date)
                                <div class="mt-1">Mantenimiento: {{ $container->next_maintenance_date->format('d/m/Y') }}</div>
                            @endif
                        </div>
                    </x-table-cell>
                    <x-table-actions align="right">
                        <x-action-button variant="edit" href="{{ route('viticulturist.digital-notebook.containers.edit', $container->id) }}" />
                        <button 
                            wire:click="toggleActive({{ $container->id }})"
                            wire:loading.attr="disabled"
                            wire:target="toggleActive({{ $container->id }})"
                            class="p-2 rounded-lg transition-all duration-200 group/btn {{ $container->archived ? 'text-green-600 hover:bg-green-50' : 'text-orange-600 hover:bg-orange-50' }} disabled:opacity-50 disabled:cursor-not-allowed"
                            title="{{ $container->archived ? 'Activar contenedor' : 'Desactivar contenedor' }}"
                        >
                            <span wire:loading.remove wire:target="toggleActive({{ $container->id }})">
                                @if($container->archived)
                                    <svg class="w-5 h-5 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @endif
                            </span>
                            <span wire:loading wire:target="toggleActive({{ $container->id }})" class="inline-block">
                                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                        </button>
                    </x-table-actions>
                </x-table-row>
            @endforeach
        @endif
    </x-data-table>

    <!-- Paginación -->
    @if($containers->hasPages())
        <div class="mt-4">
            {{ $containers->links() }}
        </div>
    @endif
            @endif

            {{-- STATISTICS TAB --}}
            @if($currentTab === 'statistics')
                <div class="space-y-6">
                    {{-- Filtro de Año --}}
                    <div class="flex justify-end">
                        <select wire:model.live="yearFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[var(--color-agro-green)] focus:border-transparent">
                            @for($year = now()->year; $year >= now()->year - 5; $year--)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endfor
                        </select>
                    </div>

                    {{-- KPIs --}}
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
                            <p class="text-sm font-medium text-green-700">Capacidad Total</p>
                            <p class="text-3xl font-bold text-green-900 mt-1">{{ number_format($advancedStats['totalCapacity'] ?? 0, 2) }} kg</p>
                            <p class="text-xs text-green-600 mt-2">Todos los contenedores</p>
                        </div>
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200">
                            <p class="text-sm font-medium text-blue-700">Capacidad Usada</p>
                            <p class="text-3xl font-bold text-blue-900 mt-1">{{ number_format($advancedStats['totalUsed'] ?? 0, 2) }} kg</p>
                            <p class="text-xs text-blue-600 mt-2">{{ number_format($advancedStats['occupancyPercentage'] ?? 0, 1) }}% ocupación</p>
                        </div>
                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 border border-purple-200">
                            <p class="text-sm font-medium text-purple-700">Contenedores Activos</p>
                            <p class="text-3xl font-bold text-purple-900 mt-1">{{ $stats['active'] }}</p>
                            <p class="text-xs text-purple-600 mt-2">De {{ $stats['total'] }} totales</p>
                        </div>
                        <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl p-6 border border-orange-200">
                            <p class="text-sm font-medium text-orange-700">Capacidad Media</p>
                            <p class="text-3xl font-bold text-orange-900 mt-1">{{ number_format($advancedStats['avgCapacityPerContainer'] ?? 0, 2) }} kg</p>
                            <p class="text-xs text-orange-600 mt-2">Por contenedor</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Distribución por Estado --}}
                        <div class="bg-white rounded-xl p-6 border border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">📊 Distribución por Estado</h3>
                            <div class="space-y-4">
                                @php
                                    $total = ($advancedStats['emptyContainers'] ?? 0) + ($advancedStats['partialContainers'] ?? 0) + ($advancedStats['fullContainers'] ?? 0);
                                    $emptyPct = $total > 0 ? (($advancedStats['emptyContainers'] ?? 0) / $total) * 100 : 0;
                                    $partialPct = $total > 0 ? (($advancedStats['partialContainers'] ?? 0) / $total) * 100 : 0;
                                    $fullPct = $total > 0 ? (($advancedStats['fullContainers'] ?? 0) / $total) * 100 : 0;
                                @endphp
                                
                                <div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700">Vacíos</span>
                                        <span class="text-sm font-bold text-gray-900">{{ $advancedStats['emptyContainers'] ?? 0 }} ({{ number_format($emptyPct, 1) }}%)</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3">
                                        <div class="bg-green-500 h-3 rounded-full" style="width: {{ $emptyPct }}%"></div>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700">Parciales</span>
                                        <span class="text-sm font-bold text-gray-900">{{ $advancedStats['partialContainers'] ?? 0 }} ({{ number_format($partialPct, 1) }}%)</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3">
                                        <div class="bg-yellow-500 h-3 rounded-full" style="width: {{ $partialPct }}%"></div>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700">Llenos</span>
                                        <span class="text-sm font-bold text-gray-900">{{ $advancedStats['fullContainers'] ?? 0 }} ({{ number_format($fullPct, 1) }}%)</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3">
                                        <div class="bg-blue-500 h-3 rounded-full" style="width: {{ $fullPct }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Disponibilidad --}}
                        <div class="bg-white rounded-xl p-6 border border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">🔄 Disponibilidad</h3>
                            <div class="space-y-4">
                                <div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700">Disponibles</span>
                                        <span class="text-sm font-bold text-gray-900">{{ $advancedStats['availableContainers'] ?? 0 }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3">
                                        @php
                                            $availablePct = $stats['active'] > 0 ? (($advancedStats['availableContainers'] ?? 0) / $stats['active']) * 100 : 0;
                                        @endphp
                                        <div class="bg-green-500 h-3 rounded-full" style="width: {{ $availablePct }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700">Asignados</span>
                                        <span class="text-sm font-bold text-gray-900">{{ $advancedStats['assignedContainers'] ?? 0 }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3">
                                        @php
                                            $assignedPct = $stats['active'] > 0 ? (($advancedStats['assignedContainers'] ?? 0) / $stats['active']) * 100 : 0;
                                        @endphp
                                        <div class="bg-blue-500 h-3 rounded-full" style="width: {{ $assignedPct }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700">Activos</span>
                                        <span class="text-sm font-bold text-gray-900">{{ $advancedStats['activeContainers'] ?? 0 }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3">
                                        @php
                                            $activePct = $stats['total'] > 0 ? (($advancedStats['activeContainers'] ?? 0) / $stats['total']) * 100 : 0;
                                        @endphp
                                        <div class="bg-purple-500 h-3 rounded-full" style="width: {{ $activePct }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700">Inactivos</span>
                                        <span class="text-sm font-bold text-gray-900">{{ $advancedStats['inactiveContainers'] ?? 0 }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3">
                                        @php
                                            $inactivePct = $stats['total'] > 0 ? (($advancedStats['inactiveContainers'] ?? 0) / $stats['total']) * 100 : 0;
                                        @endphp
                                        <div class="bg-gray-500 h-3 rounded-full" style="width: {{ $inactivePct }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Top 10 Contenedores --}}
                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">🏆 Top 10 Contenedores por Capacidad</h3>
                        <div class="space-y-3">
                            @forelse(($advancedStats['topContainers'] ?? []) as $index => $container)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <span class="flex-shrink-0 w-8 h-8 rounded-full bg-[var(--color-agro-green)] text-white flex items-center justify-center font-bold text-sm">
                                            {{ $index + 1 }}
                                        </span>
                                        <div>
                                            <p class="font-semibold text-gray-900">{{ $container['name'] }}</p>
                                            <p class="text-xs text-gray-500">{{ number_format($container['used'], 2) }} / {{ number_format($container['capacity'], 2) }} kg ({{ number_format($container['percentage'], 1) }}%)</p>
                                        </div>
                                    </div>
                                    <span class="font-bold text-[var(--color-agro-green-dark)]">{{ number_format($container['capacity'], 2) }} kg</span>
                                </div>
                            @empty
                                <p class="text-gray-500 text-center py-4">No hay contenedores registrados</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- Distribución por Campaña --}}
                    @if(($advancedStats['campaignStats'] ?? [])->isNotEmpty())
                        <div class="bg-white rounded-xl p-6 border border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">📅 Distribución por Campaña</h3>
                            <div class="space-y-3">
                                @foreach($advancedStats['campaignStats'] as $index => $campaign)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                        <div class="flex items-center gap-3">
                                            <span class="flex-shrink-0 w-8 h-8 rounded-full bg-[var(--color-agro-green)] text-white flex items-center justify-center font-bold text-sm">
                                                {{ $index + 1 }}
                                            </span>
                                            <div>
                                                <p class="font-semibold text-gray-900">Campaña {{ $campaign['campaign_year'] }}</p>
                                            </div>
                                        </div>
                                        <span class="font-bold text-[var(--color-agro-green-dark)]">{{ $campaign['count'] }} contenedores</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Nuevos Contenedores --}}
                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">📈 Nuevos Contenedores (Últimos 12 meses)</h3>
                        <div class="h-64 flex items-end justify-between gap-2">
                            @foreach(($advancedStats['newContainersByMonth'] ?? []) as $month)
                                <div class="flex-1 flex flex-col items-center">
                                    <div class="w-full bg-[var(--color-agro-green)] rounded-t-lg transition-all hover:bg-[var(--color-agro-green-dark)]" 
                                        style="height: {{ $month['count'] > 0 ? ($month['count'] / max(collect($advancedStats['newContainersByMonth'] ?? [])->pluck('count')->max(), 1)) * 100 : 5 }}%"
                                        title="{{ $month['count'] }} contenedores"></div>
                                    <span class="text-xs text-gray-600 mt-2">{{ $month['month'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

