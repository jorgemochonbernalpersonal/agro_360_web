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

    <!-- Estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                    <p class="text-sm font-medium text-gray-600">Peso Total</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($stats['total_weight'], 2) }} kg</p>
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
                    <p class="text-sm font-medium text-gray-600">Entregados</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($stats['delivered']) }}</p>
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
        <x-filter-select wire:model.live="filterStatus">
            <option value="">Todos los estados</option>
            <option value="filled">Llenado</option>
            <option value="in_transit">En tránsito</option>
            <option value="delivered">Entregado</option>
            <option value="stored">Almacenado</option>
            <option value="empty">Vacío</option>
        </x-filter-select>
        <x-filter-select wire:model.live="filterAvailability">
            <option value="">Todos</option>
            <option value="available">Disponibles</option>
            <option value="assigned">Asignados</option>
        </x-filter-select>
        <x-filter-select wire:model.live="filterType">
            <option value="">Todos los tipos</option>
            <option value="caja">Caja</option>
            <option value="pallet">Pallet</option>
            <option value="contenedor">Contenedor</option>
            <option value="saco">Saco</option>
            <option value="cuba">Cuba</option>
            <option value="other">Otro</option>
        </x-filter-select>
        <x-slot:actions>
            @if($search || $selectedCampaign || $selectedHarvest || $filterStatus || $filterType)
                <x-button wire:click="$set('search', ''); $set('selectedCampaign', ''); $set('selectedHarvest', ''); $set('filterStatus', ''); $set('filterType', '')" variant="ghost" size="sm">
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
        empty-description="{{ ($search || $selectedCampaign || $filterStatus || $filterType) ? 'No se encontraron contenedores con los filtros seleccionados' : 'Los contenedores aparecerán aquí cuando se registren en las cosechas' }}"
        color="green"
    >
        @if($containers->count() > 0)
            @foreach($containers as $container)
                <x-table-row>
                    <x-table-cell>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-[var(--color-agro-green-light)] to-[var(--color-agro-green)] flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform duration-200">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-gray-900">
                                    {{ ucfirst($container->container_type) }}
                                    @if($container->container_number)
                                        #{{ $container->container_number }}
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    Cantidad: {{ $container->quantity }}
                                </div>
                            </div>
                        </div>
                    </x-table-cell>
                    <x-table-cell>
                        @if($container->harvest)
                            <div>
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $container->harvest->activity->plot->name ?? 'Sin parcela' }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ $container->harvest->plotPlanting->grapeVariety->name ?? 'Sin variedad' }}
                                </div>
                                <div class="text-xs text-gray-400 mt-1">
                                    {{ $container->harvest->harvest_start_date->format('d/m/Y') }}
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
                                {{ number_format($container->weight, 2) }} kg
                            </div>
                            @if($container->weight_per_unit)
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ number_format($container->weight_per_unit, 2) }} kg/unidad
                                </div>
                            @endif
                        </div>
                    </x-table-cell>
                    <x-table-cell>
                        @if($container->location)
                            <span class="text-sm text-gray-700">{{ $container->location }}</span>
                        @else
                            <span class="text-sm text-gray-400">-</span>
                        @endif
                    </x-table-cell>
                    <x-table-cell>
                        @php
                            $statusColors = [
                                'filled' => 'bg-blue-100 text-blue-800',
                                'in_transit' => 'bg-yellow-100 text-yellow-800',
                                'delivered' => 'bg-green-100 text-green-800',
                                'stored' => 'bg-purple-100 text-purple-800',
                                'empty' => 'bg-gray-100 text-gray-800',
                            ];
                            $statusLabels = [
                                'filled' => 'Llenado',
                                'in_transit' => 'En tránsito',
                                'delivered' => 'Entregado',
                                'stored' => 'Almacenado',
                                'empty' => 'Vacío',
                            ];
                            $color = $statusColors[$container->status] ?? 'bg-gray-100 text-gray-800';
                            $label = $statusLabels[$container->status] ?? ucfirst($container->status);
                        @endphp
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $color }}">
                            {{ $label }}
                        </span>
                    </x-table-cell>
                    <x-table-cell>
                        <div class="text-xs text-gray-600">
                            @if($container->filled_date)
                                <div>Llenado: {{ $container->filled_date->format('d/m/Y') }}</div>
                            @endif
                            @if($container->delivery_date)
                                <div class="mt-1">Entrega: {{ $container->delivery_date->format('d/m/Y') }}</div>
                            @endif
                        </div>
                    </x-table-cell>
                    <x-table-cell>
                        <div class="flex items-center gap-2">
                            <a 
                                href="{{ route('viticulturist.digital-notebook.containers.edit', $container->id) }}"
                                class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-blue-100 text-blue-700 hover:bg-blue-200 transition"
                            >
                                Editar
                            </a>
                        </div>
                    </x-table-cell>
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
</div>

