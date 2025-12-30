<div class="container mx-auto px-4 py-6">
    {{-- Breadcrumb --}}
    <nav class="mb-6">
        <a href="{{ route('viticulturist.digital-notebook.containers.index') }}" class="text-blue-600 hover:underline">← Volver a contenedores</a>
    </nav>

    {{-- Header --}}
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $container->name }}</h1>
                @if($container->serial_number)
                    <p class="text-lg text-gray-600 mt-1">SN: {{ $container->serial_number }}</p>
                @endif
                <div class="flex items-center space-x-2 mt-3">
                    <span class="px-3 py-1 rounded-full text-sm font-medium {{ $statusBadge }}">
                        {{ $statusText }}
                    </span>
                    @if($maintenanceWarning)
                        <span class="px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                            ⚠️ {{ $maintenanceWarning['message'] }}
                        </span>
                    @endif
                </div>
            </div>
            <a href="{{ route('viticulturist.digital-notebook.containers.edit', $container->id) }}" 
               class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                Editar
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Columna Principal --}}
        <div class="lg:col-span-2 space-y-6">
            
            {{-- Ocupación --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">📊 Ocupación</h2>
                
                <div class="mb-4">
                    <div class="flex justify-between text-sm text-gray-600 mb-2">
                        <span>Capacidad Usada</span>
                        <span class="font-semibold text-lg">{{ $container->getOccupancyPercentage() }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-4">
                        <div class="h-4 rounded-full transition-all {{ $occupancyColor }}"
                             style="width: {{ $container->getOccupancyPercentage() }}%">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 text-center">
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs text-gray-600">Capacidad Total</p>
                        <p class="text-lg font-bold text-gray-900">{{ number_format($container->capacity, 0) }} L</p>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-3">
                        <p class="text-xs text-gray-600">Ocupado</p>
                        <p class="text-lg font-bold text-blue-900">{{ number_format($container->used_capacity, 0) }} L</p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-3">
                        <p class="text-xs text-gray-600">Disponible</p>
                        <p class="text-lg font-bold text-green-900">{{ number_format($container->getAvailableCapacity(), 0) }} L</p>
                    </div>
                </div>
            </div>

            {{-- Vendimias Almacenadas --}}
            @if($container->harvests->count() > 0)
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">
                        🍇 Vendimias Almacenadas ({{ $container->harvests->count() }})
                    </h2>
                    
                    <div class="space-y-3">
                        @foreach($container->harvests as $harvest)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-900">
                                        {{ $harvest->plotPlanting->grapeVariety->name ?? 'Vendimia' }}
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        {{ number_format($harvest->total_weight, 0) }} kg | 
                                        {{ $harvest->harvest_start_date->format('d/m/Y') }}
                                    </p>
                                    @if($harvest->brix_degree)
                                        <p class="text-xs text-gray-500 mt-1">
                                            Brix: {{ $harvest->brix_degree }}° | pH: {{ $harvest->ph_level }}
                                        </p>
                                    @endif
                                </div>
                                <a href="{{ route('viticulturist.digital-notebook.harvest.show', $harvest->id) }}" 
                                   class="text-sm text-blue-600 hover:text-blue-800">
                                    Ver →
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Historial de Operaciones --}}
            @if($container->histories->count() > 0)
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">📜 Historial de Operaciones</h2>
                    
                    <div class="space-y-2">
                        @foreach($container->histories->take(10) as $history)
                            <div class="flex items-center text-sm">
                                <span class="text-gray-500">{{ $history->start_date->format('d/m/Y H:i') }}</span>
                                <span class="mx-2">•</span>
                                <span class="text-gray-900">
                                    {{ ucfirst($history->operation_type) }}: 
                                    {{ $history->quantity > 0 ? '+' : '' }}{{ number_format($history->quantity, 0) }} L
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Columna Lateral --}}
        <div class="space-y-6">
            
            {{-- Información General --}}
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="font-semibold text-gray-900 mb-3">ℹ️ Información</h3>
                
                <dl class="space-y-3 text-sm">
                    @if($container->description)
                        <div>
                            <dt class="text-gray-600">Descripción</dt>
                            <dd class="text-gray-900 mt-1">{{ $container->description }}</dd>
                        </div>
                    @endif
                    
                    @if($container->purchase_date)
                        <div>
                            <dt class="text-gray-600">Fecha de Compra</dt>
                            <dd class="text-gray-900 mt-1">{{ $container->purchase_date->format('d/m/Y') }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Acciones Rápidas --}}
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="font-semibold text-gray-900 mb-3">⚡ Acciones Rápidas</h3>
                <div class="space-y-2">
                    <a href="{{ route('viticulturist.digital-notebook.harvest.create', ['container_id' => $container->id]) }}" 
                       class="block w-full bg-green-600 hover:bg-green-700 text-white text-center py-2 px-4 rounded-lg transition-colors">
                        🍇 Nueva Vendimia
                    </a>
                    <a href="{{ route('viticulturist.digital-notebook.containers.edit', $container->id) }}" 
                       class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-center py-2 px-4 rounded-lg transition-colors">
                        ✏️ Editar Contenedor
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
