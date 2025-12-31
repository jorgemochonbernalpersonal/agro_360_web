<div class="space-y-6 animate-fade-in">
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>';
    @endphp
    
    <x-page-header 
        :icon="$icon"
        title="Contenedores"
        description="Gestiona tus barricas, depósitos y tanques de bodega"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    >
        <x-slot name="actionButton">
            <a href="{{ route('viticulturist.digital-notebook.containers.create') }}" 
               class="inline-flex items-center px-4 py-2 bg-[var(--color-agro-green)] hover:bg-[var(--color-agro-green-dark)] text-white font-medium rounded-lg transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nuevo Contenedor
            </a>
        </x-slot>
    </x-page-header>

    {{-- Tabs --}}
    @php
        $activeCount = $stats['total_containers'] - \App\Models\Container::where('user_id', auth()->id())->where('archived', true)->count();
        $inactiveCount = \App\Models\Container::where('user_id', auth()->id())->where('archived', true)->count();
        $currentTabValue = $viewMode === 'stats' ? 'statistics' : ($filterStatus === 'archived' ? 'inactive' : 'active');
    @endphp
    
    <x-resource-view-tabs 
        :activeCount="$activeCount"
        :inactiveCount="$inactiveCount"
        :currentTab="$currentTabValue"
        onSwitch="switchTab"
    />

    {{-- Estadísticas Globales --}}
    @php
        $statsData = [
            [
                'label' => 'Total',
                'value' => $stats['total_containers'],
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>',
                'color' => 'blue'
            ],
            [
                'label' => 'Capacidad',
                'value' => number_format($stats['total_capacity'], 0) . ' L',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                'color' => 'green'
            ],
            [
                'label' => 'Ocupación',
                'value' => $stats['occupancy_percentage'] . '%',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>',
                'color' => 'yellow'
            ],
            [
                'label' => 'Disponibles',
                'value' => $stats['available_containers'],
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>',
                'color' => 'purple'
            ]
        ];
    @endphp
    
    <x-stats-grid :stats="$statsData" />

    {{-- Vista de Estadísticas --}}
    @if($viewMode === 'stats')
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {{-- Capacidad Total --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 Capacidad Total</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total instalada:</span>
                        <span class="font-bold text-gray-900">{{ number_format($stats['total_capacity'], 0) }} L</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Ocupada:</span>
                        <span class="font-bold text-blue-600">{{ number_format($stats['used_capacity'], 0) }} L</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Disponible:</span>
                        <span class="font-bold text-green-600">{{ number_format($stats['available_capacity'], 0) }} L</span>
                    </div>
                    <div class="pt-3 border-t">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Ocupación:</span>
                            <span class="font-bold text-lg text-gray-900">{{ $stats['occupancy_percentage'] }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3 mt-2">
                            <div class="bg-gradient-to-r from-green-500 to-blue-500 h-3 rounded-full transition-all"
                                 style="width: {{ $stats['occupancy_percentage'] }}%">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Distribución por Estado --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">📦 Distribución por Estado</h3>
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm text-gray-600">Vacíos</span>
                            <span class="text-sm font-semibold text-blue-600">{{ $stats['empty_containers'] }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-500 h-2 rounded-full" 
                                 style="width: {{ $stats['total_containers'] > 0 ? ($stats['empty_containers'] / $stats['total_containers'] * 100) : 0 }}%">
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm text-gray-600">Disponibles</span>
                            <span class="text-sm font-semibold text-green-600">{{ $stats['available_containers'] }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full" 
                                 style="width: {{ $stats['total_containers'] > 0 ? ($stats['available_containers'] / $stats['total_containers'] * 100) : 0 }}%">
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm text-gray-600">Llenos</span>
                            <span class="text-sm font-semibold text-red-600">{{ $stats['full_containers'] }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-red-500 h-2 rounded-full" 
                                 style="width: {{ $stats['total_containers'] > 0 ? ($stats['full_containers'] / $stats['total_containers'] * 100) : 0 }}%">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Resumen General --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">📈 Resumen General</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                        <span class="text-sm text-gray-700">Total Contenedores</span>
                        <span class="text-2xl font-bold text-blue-600">{{ $stats['total_containers'] }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                        <span class="text-sm text-gray-700">Capacidad Media</span>
                        <span class="text-lg font-bold text-green-600">
                            {{ $stats['total_containers'] > 0 ? number_format($stats['total_capacity'] / $stats['total_containers'], 0) : 0 }} L
                        </span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg">
                        <span class="text-sm text-gray-700">Eficiencia de Uso</span>
                        <span class="text-lg font-bold text-purple-600">{{ $stats['occupancy_percentage'] }}%</span>
                    </div>
                </div>
            </div>
        </div>
    @else
        {{-- Filtros --}}
        <x-filter-section title="Filtros de Búsqueda" color="green">
        <x-filter-input 
            wire:model.live.debounce.300ms="search" 
            placeholder="Buscar por nombre o número de serie..."
        />
        <x-filter-select wire:model.live="filterStatus">
            <option value="">Todos los estados</option>
            <option value="empty">Vacíos</option>
            <option value="available">Disponibles</option>
            <option value="full">Llenos</option>
            <option value="archived">Archivados</option>
        </x-filter-select>
        <div class="flex items-center">
            <button wire:click="resetFilters" 
                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm font-medium transition">
                Limpiar Filtros
            </button>
        </div>
    </x-filter-section>

    {{-- Grid de Contenedores --}}
    @php
        $containerIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>';
    @endphp
    
    <x-resource-grid 
        :items="$containers" 
        emptyMessage="No hay contenedores"
        emptyDescription="Comienza creando tu primer contenedor para gestionar tu bodega."
        :emptyIcon="$containerIcon"
    >
            @foreach($containers as $container)
                <x-resource-card 
                    :title="$container->name"
                    :subtitle="$container->serial_number ? 'SN: ' . $container->serial_number : null"
                    :badge="$container->isEmpty() ? 'Vacío' : ($container->isFull() ? 'Lleno' : 'Disponible')"
                    :badgeColor="$container->isEmpty() ? 'blue' : ($container->isFull() ? 'red' : 'green')"
                    hoverBorderColor="[var(--color-agro-green-light)]"
                >
                    <x-slot:content>
                        <x-progress-bar 
                            :percentage="$container->getOccupancyPercentage()"
                            label="Ocupación"
                            :currentValue="$container->used_capacity"
                            :maxValue="$container->capacity"
                            unit="L"
                        />
                    </x-slot:content>

                    <x-slot:actions>
                        <div class="flex gap-2 justify-center">
                            <x-action-button 
                                variant="view" 
                                href="{{ route('viticulturist.digital-notebook.containers.show', $container->id) }}" 
                            />
                            
                            <x-action-button 
                                variant="edit" 
                                href="{{ route('viticulturist.digital-notebook.containers.edit', $container->id) }}" 
                            />
                            
                            @if(!$container->archived)
                                <x-action-button 
                                    variant="archive" 
                                    wireClick="archive({{ $container->id }})" 
                                />
                            @else
                                <x-action-button 
                                    variant="activate" 
                                    wireClick="unarchive({{ $container->id }})" 
                                />
                            @endif
                        </div>
                    </x-slot:actions>
                </x-resource-card>
            @endforeach
        
        <x-slot:pagination>
            {{ $containers->links() }}
        </x-slot:pagination>
        
        <x-slot:emptyAction>
            <a href="{{ route('viticulturist.digital-notebook.containers.create') }}" 
               class="inline-flex items-center px-4 py-2 bg-[var(--color-agro-green)] hover:bg-[var(--color-agro-green-dark)] text-white font-medium rounded-lg transition">
                Crear Contenedor
            </a>
        </x-slot:emptyAction>
    </x-resource-grid>
    @endif
</div>
