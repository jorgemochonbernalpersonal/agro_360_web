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
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-1 inline-flex gap-1 mb-6">
        <button wire:click="showActive" 
                class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $filterStatus === '' && $viewMode !== 'stats' ? 'bg-[var(--color-agro-green)] text-white' : 'text-gray-600 hover:bg-gray-100' }}">
            Activos 
            <span class="ml-1 {{ $filterStatus === '' && $viewMode !== 'stats' ? 'text-white/80' : 'text-gray-500' }}">({{ $stats['total_containers'] - \App\Models\Container::where('user_id', auth()->id())->where('archived', true)->count() }})</span>
        </button>
        <button wire:click="showInactive" 
                class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $filterStatus === 'archived' && $viewMode !== 'stats' ? 'bg-[var(--color-agro-green)] text-white' : 'text-gray-600 hover:bg-gray-100' }}">
            Inactivos 
            <span class="ml-1 {{ $filterStatus === 'archived' && $viewMode !== 'stats' ? 'text-white/80' : 'text-gray-500' }}">({{ \App\Models\Container::where('user_id', auth()->id())->where('archived', true)->count() }})</span>
        </button>
        <button wire:click="showStats" 
                class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $viewMode === 'stats' ? 'bg-[var(--color-agro-green)] text-white' : 'text-gray-600 hover:bg-gray-100' }}">
            Estadísticas
        </button>
    </div>

    {{-- Estadísticas Globales --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_containers'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Capacidad</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_capacity'], 0) }} L</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Ocupación</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['occupancy_percentage'] }}%</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Disponibles</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['available_containers'] }}</p>
                </div>
            </div>
        </div>
    </div>

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
    @if($containers->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($containers as $container)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md hover:border-[var(--color-agro-green-light)] transition-all p-6">
                    
                    {{-- Header --}}
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 text-lg">{{ $container->name }}</h3>
                            @if($container->serial_number)
                                <p class="text-xs text-gray-500 mt-1">SN: {{ $container->serial_number }}</p>
                            @endif
                        </div>
                        
                        <span class="text-xs font-medium px-2.5 py-1 rounded-full flex-shrink-0
                            @if($container->isEmpty()) bg-blue-100 text-blue-800
                            @elseif($container->isFull()) bg-red-100 text-red-800
                            @else bg-green-100 text-green-800
                            @endif">
                            @if($container->isEmpty()) Vacío
                            @elseif($container->isFull()) Lleno
                            @else Disponible
                            @endif
                        </span>
                    </div>

                    {{-- Barra de Ocupación --}}
                    <div class="mb-4">
                        <div class="flex justify-between text-sm text-gray-600 mb-1">
                            <span>Ocupación</span>
                            <span class="font-semibold">{{ $container->getOccupancyPercentage() }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="h-2 rounded-full transition-all
                                @if($container->getOccupancyPercentage() >= 90) bg-red-500
                                @elseif($container->getOccupancyPercentage() >= 70) bg-orange-500
                                @elseif($container->getOccupancyPercentage() >= 50) bg-yellow-500
                                @else bg-green-500
                                @endif"
                                style="width: {{ $container->getOccupancyPercentage() }}%">
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ number_format($container->used_capacity, 0) }} / {{ number_format($container->capacity, 0) }} L
                        </p>
                    </div>

                    {{-- Footer con Botones de Acción --}}
                    <div class="pt-3 border-t border-gray-100">
                        <div class="flex gap-2">
                            {{-- Ver Detalles --}}
                            <a href="{{ route('viticulturist.digital-notebook.containers.show', $container->id) }}" 
                               class="flex-1 px-3 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg transition flex items-center justify-center"
                               title="Ver Detalles">
                                <span class="text-lg">👁️</span>
                            </a>
                            
                            {{-- Editar --}}
                            <a href="{{ route('viticulturist.digital-notebook.containers.edit', $container->id) }}" 
                               class="flex-1 px-3 py-2 bg-teal-500 hover:bg-teal-600 text-white rounded-lg transition flex items-center justify-center"
                               title="Editar">
                                <span class="text-lg">✏️</span>
                            </a>
                            
                            {{-- Archivar/Activar --}}
                            @if(!$container->archived)
                                <button wire:click="archive({{ $container->id }})" 
                                        class="flex-1 px-3 py-2 bg-lime-500 hover:bg-lime-600 text-white rounded-lg transition flex items-center justify-center"
                                        title="Archivar">
                                    <span class="text-lg">📦</span>
                                </button>
                            @else
                                <button wire:click="unarchive({{ $container->id }})" 
                                        class="flex-1 px-3 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition flex items-center justify-center"
                                        title="Activar">
                                    <span class="text-lg">✅</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Paginación --}}
        <div class="mt-6">
            {{ $containers->links() }}
        </div>
    @else
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">No hay contenedores</h3>
            <p class="mt-2 text-gray-600">Comienza creando tu primer contenedor para gestionar tu bodega.</p>
            <div class="mt-6">
                <a href="{{ route('viticulturist.digital-notebook.containers.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-[var(--color-agro-green)] hover:bg-[var(--color-agro-green-dark)] text-white font-medium rounded-lg transition">
                    Crear Contenedor
                </a>
            </div>
        </div>
    @endif
    @endif
</div>
