<div class="space-y-6 animate-fade-in">
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>';
    @endphp
    <x-page-header
        :icon="$icon"
        title="Detalle de Cosecha"
        description="Información completa de la cosecha y sus contenedores"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    >
        <x-slot:actionButton>
            <div class="flex gap-2">
                <a 
                    href="{{ route('viticulturist.digital-notebook.harvest.edit', $harvest->id) }}"
                    class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition font-semibold"
                >
                    Editar
                </a>
                <a 
                    href="{{ route('viticulturist.digital-notebook') }}"
                    class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition font-semibold"
                >
                    Volver
                </a>
            </div>
        </x-slot:actionButton>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Información Principal -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Información Básica -->
            <div class="glass-card rounded-xl p-6">
                <h2 class="text-xl font-bold text-[var(--color-agro-green-dark)] mb-4">Información Básica</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-semibold text-gray-500">Parcela</label>
                        <p class="text-gray-900 text-lg font-semibold">{{ $harvest->activity->plot->name ?? 'Sin parcela' }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-gray-500">Variedad</label>
                        @if($harvest->plotPlanting->name)
                            <p class="text-gray-900 text-sm font-semibold text-purple-700 mb-1">{{ $harvest->plotPlanting->name }}</p>
                        @endif
                        <p class="text-gray-900 text-lg font-semibold">{{ $harvest->plotPlanting->grapeVariety->name ?? 'Sin variedad' }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-gray-500">Campaña</label>
                        <p class="text-gray-900">{{ $harvest->activity->campaign->name ?? 'Sin campaña' }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-gray-500">Fecha de Cosecha</label>
                        <p class="text-gray-900">
                            {{ $harvest->harvest_start_date->format('d/m/Y') }}
                            @if($harvest->harvest_end_date)
                                - {{ $harvest->harvest_end_date->format('d/m/Y') }}
                            @endif
                        </p>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-gray-500">Peso Total</label>
                        <p class="text-gray-900 text-lg font-bold text-purple-700">{{ number_format($harvest->total_weight, 2) }} kg</p>
                    </div>

                    @if($harvest->yield_per_hectare)
                        <div>
                            <label class="text-sm font-semibold text-gray-500">Rendimiento</label>
                            <p class="text-gray-900 text-lg font-semibold">{{ number_format($harvest->yield_per_hectare, 2) }} kg/ha</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Calidad de la Uva -->
            @if($harvest->baume_degree || $harvest->brix_degree || $harvest->acidity_level || $harvest->ph_level || $harvest->color_rating || $harvest->aroma_rating || $harvest->health_status)
                <div class="glass-card rounded-xl p-6">
                    <h2 class="text-xl font-bold text-[var(--color-agro-green-dark)] mb-4">Calidad de la Uva</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        @if($harvest->baume_degree)
                            <div>
                                <label class="text-sm font-semibold text-gray-500">Grados Baumé</label>
                                <p class="text-gray-900">{{ number_format($harvest->baume_degree, 2) }} °Bé</p>
                            </div>
                        @endif

                        @if($harvest->brix_degree)
                            <div>
                                <label class="text-sm font-semibold text-gray-500">Grados Brix</label>
                                <p class="text-gray-900">{{ number_format($harvest->brix_degree, 2) }} °Bx</p>
                            </div>
                        @endif

                        @if($harvest->acidity_level)
                            <div>
                                <label class="text-sm font-semibold text-gray-500">Acidez Total</label>
                                <p class="text-gray-900">{{ number_format($harvest->acidity_level, 2) }} g/L</p>
                            </div>
                        @endif

                        @if($harvest->ph_level)
                            <div>
                                <label class="text-sm font-semibold text-gray-500">pH</label>
                                <p class="text-gray-900">{{ number_format($harvest->ph_level, 2) }}</p>
                            </div>
                        @endif

                        @if($harvest->color_rating)
                            <div>
                                <label class="text-sm font-semibold text-gray-500">Color</label>
                                <p class="text-gray-900 capitalize">{{ $harvest->color_rating }}</p>
                            </div>
                        @endif

                        @if($harvest->aroma_rating)
                            <div>
                                <label class="text-sm font-semibold text-gray-500">Aroma</label>
                                <p class="text-gray-900 capitalize">{{ $harvest->aroma_rating }}</p>
                            </div>
                        @endif

                        @if($harvest->health_status)
                            <div>
                                <label class="text-sm font-semibold text-gray-500">Estado de Salud</label>
                                <p class="text-gray-900 capitalize">{{ str_replace('_', ' ', $harvest->health_status) }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Destino y Valor Económico -->
            @if($harvest->destination_type || $harvest->destination || $harvest->buyer_name || $harvest->price_per_kg || $harvest->total_value)
                <div class="glass-card rounded-xl p-6">
                    <h2 class="text-xl font-bold text-[var(--color-agro-green-dark)] mb-4">Destino y Valor Económico</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @if($harvest->destination_type)
                            <div>
                                <label class="text-sm font-semibold text-gray-500">Tipo de Destino</label>
                                <p class="text-gray-900 capitalize">{{ str_replace('_', ' ', $harvest->destination_type) }}</p>
                            </div>
                        @endif

                        @if($harvest->destination)
                            <div>
                                <label class="text-sm font-semibold text-gray-500">Destino</label>
                                <p class="text-gray-900">{{ $harvest->destination }}</p>
                            </div>
                        @endif

                        @if($harvest->buyer_name)
                            <div>
                                <label class="text-sm font-semibold text-gray-500">Comprador</label>
                                <p class="text-gray-900">{{ $harvest->buyer_name }}</p>
                            </div>
                        @endif

                        @if($harvest->price_per_kg)
                            <div>
                                <label class="text-sm font-semibold text-gray-500">Precio por kg</label>
                                <p class="text-gray-900">{{ number_format($harvest->price_per_kg, 4) }} €/kg</p>
                            </div>
                        @endif

                        @if($harvest->total_value)
                            <div class="md:col-span-2">
                                <label class="text-sm font-semibold text-gray-500">Valor Total</label>
                                <p class="text-gray-900 text-2xl font-bold text-green-700">{{ number_format($harvest->total_value, 2) }} €</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Contenedores -->
            <div class="glass-card rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-[var(--color-agro-green-dark)]">Contenedores</h2>
                    <a 
                        href="{{ route('viticulturist.digital-notebook.containers.create') }}?harvest_id={{ $harvest->id }}"
                        class="px-4 py-2 rounded-lg bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition font-semibold text-sm"
                    >
                        + Agregar Contenedor
                    </a>
                </div>

                @if($harvest->container)
                    @php
                        $container = $harvest->container;
                    @endphp
                    <div class="space-y-4">
                        <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h3 class="font-semibold text-gray-900">
                                            {{ ucfirst($container->container_type) }}
                                            @if($container->container_number)
                                                #{{ $container->container_number }}
                                            @endif
                                        </h3>
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
                                    </div>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                        <div>
                                            <span class="text-gray-500">Cantidad:</span>
                                            <span class="ml-2 font-semibold text-gray-900">{{ $container->quantity }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Peso:</span>
                                            <span class="ml-2 font-semibold text-gray-900">{{ number_format($container->weight, 2) }} kg</span>
                                        </div>
                                        @if($container->location)
                                            <div>
                                                <span class="text-gray-500">Ubicación:</span>
                                                <span class="ml-2 font-semibold text-gray-900">{{ $container->location }}</span>
                                            </div>
                                        @endif
                                        @if($container->filled_date)
                                            <div>
                                                <span class="text-gray-500">Fecha llenado:</span>
                                                <span class="ml-2 font-semibold text-gray-900">{{ $container->filled_date->format('d/m/Y') }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    @if($container->notes)
                                        <div class="mt-3 pt-3 border-t border-gray-200">
                                            <p class="text-sm text-gray-600">{{ $container->notes }}</p>
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <a href="{{ route('viticulturist.digital-notebook.containers.edit', $container) }}" 
                                       class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        Editar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <p class="text-sm">Esta cosecha no tiene contenedor asignado</p>
                    </div>
                @endif
                            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <h3 class="font-semibold text-gray-900">
                                                {{ ucfirst($container->container_type) }}
                                                @if($container->container_number)
                                                    #{{ $container->container_number }}
                                                @endif
                                            </h3>
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
                                        </div>
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                            <div>
                                                <span class="text-gray-500">Cantidad:</span>
                                                <span class="ml-2 font-semibold text-gray-900">{{ $container->quantity }}</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-500">Peso:</span>
                                                <span class="ml-2 font-semibold text-gray-900">{{ number_format($container->weight, 2) }} kg</span>
                                            </div>
                                            @if($container->location)
                                                <div>
                                                    <span class="text-gray-500">Ubicación:</span>
                                                    <span class="ml-2 text-gray-900">{{ $container->location }}</span>
                                                </div>
                                            @endif
                                            @if($container->filled_date)
                                                <div>
                                                    <span class="text-gray-500">Llenado:</span>
                                                    <span class="ml-2 text-gray-900">{{ $container->filled_date->format('d/m/Y') }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <a 
                                        href="{{ route('viticulturist.digital-notebook.containers.edit', $container->id) }}"
                                        class="ml-4 px-3 py-1.5 text-xs font-semibold rounded-lg bg-blue-100 text-blue-700 hover:bg-blue-200 transition"
                                    >
                                        Editar
                                    </a>
                                </div>
                            </div>
                        @endforeach

                        <!-- Resumen de Contenedores -->
                        <div class="mt-4 p-4 bg-purple-50 border border-purple-200 rounded-lg">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-600">Total Contenedores:</span>
                                    <span class="ml-2 font-bold text-gray-900">{{ $this->getContainersCount() }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Peso Total Contenedores:</span>
                                    <span class="ml-2 font-bold text-gray-900">{{ number_format($this->getContainersTotalWeight(), 2) }} kg</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Peso de Cosecha:</span>
                                    <span class="ml-2 font-bold text-purple-700">{{ number_format($harvest->total_weight, 2) }} kg</span>
                                </div>
                            </div>
                            @if($this->getContainersTotalWeight() > $harvest->total_weight)
                                <div class="mt-2 p-2 bg-red-50 border border-red-200 rounded text-xs text-red-700">
                                    ⚠️ El peso total de los contenedores ({{ number_format($this->getContainersTotalWeight(), 2) }} kg) excede el peso de la cosecha ({{ number_format($harvest->total_weight, 2) }} kg)
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <p>No hay contenedores registrados para esta cosecha</p>
                        <a 
                            href="{{ route('viticulturist.digital-notebook.containers.create') }}?harvest_id={{ $harvest->id }}"
                            class="mt-3 inline-block px-4 py-2 rounded-lg bg-purple-600 text-white hover:bg-purple-700 transition text-sm font-semibold"
                        >
                            Agregar Primer Contenedor
                        </a>
                    </div>
                @endif
            </div>

            <!-- Información de la Actividad -->
            <div class="glass-card rounded-xl p-6">
                <h2 class="text-xl font-bold text-[var(--color-agro-green-dark)] mb-4">Información de la Actividad</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-semibold text-gray-500">Fecha de Actividad</label>
                        <p class="text-gray-900">{{ $harvest->activity->activity_date->format('d/m/Y') }}</p>
                    </div>

                    @if($harvest->activity->crew)
                        <div>
                            <label class="text-sm font-semibold text-gray-500">Equipo</label>
                            <p class="text-gray-900">{{ $harvest->activity->crew->name }}</p>
                        </div>
                    @endif

                    @if($harvest->activity->crewMember && $harvest->activity->crewMember->viticulturist)
                        <div>
                            <label class="text-sm font-semibold text-gray-500">Viticultor</label>
                            <p class="text-gray-900">{{ $harvest->activity->crewMember->viticulturist->name }}</p>
                        </div>
                    @endif

                    @if($harvest->activity->machinery)
                        <div>
                            <label class="text-sm font-semibold text-gray-500">Maquinaria</label>
                            <p class="text-gray-900">{{ $harvest->activity->machinery->name }}</p>
                        </div>
                    @endif

                    @if($harvest->activity->weather_conditions)
                        <div>
                            <label class="text-sm font-semibold text-gray-500">Condiciones Meteorológicas</label>
                            <p class="text-gray-900">{{ $harvest->activity->weather_conditions }}</p>
                        </div>
                    @endif

                    @if($harvest->activity->temperature)
                        <div>
                            <label class="text-sm font-semibold text-gray-500">Temperatura</label>
                            <p class="text-gray-900">{{ number_format($harvest->activity->temperature, 1) }} °C</p>
                        </div>
                    @endif

                    @if($harvest->activity->notes)
                        <div class="md:col-span-2">
                            <label class="text-sm font-semibold text-gray-500">Notas</label>
                            <p class="text-gray-900 whitespace-pre-wrap">{{ $harvest->activity->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Rendimiento Estimado -->
            @if($estimatedYield)
                <div class="glass-card rounded-xl p-6">
                    <h3 class="text-lg font-bold text-[var(--color-agro-green-dark)] mb-4">Rendimiento Estimado</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm font-semibold text-gray-500">Estimado</label>
                            <p class="text-gray-900 font-bold">{{ number_format($estimatedYield->estimated_total_yield, 2) }} kg</p>
                            <p class="text-xs text-gray-500">{{ number_format($estimatedYield->estimated_yield_per_hectare, 2) }} kg/ha</p>
                        </div>
                        @if($estimatedYield->hasActualYield())
                            <div>
                                <label class="text-sm font-semibold text-gray-500">Real</label>
                                <p class="text-gray-900 font-bold">{{ number_format($estimatedYield->actual_total_yield, 2) }} kg</p>
                                <p class="text-xs text-gray-500">{{ number_format($estimatedYield->actual_yield_per_hectare, 2) }} kg/ha</p>
                            </div>
                            @if($estimatedYield->variance_percentage)
                                <div>
                                    <label class="text-sm font-semibold text-gray-500">Diferencia</label>
                                    <p class="text-gray-900 font-bold {{ $estimatedYield->variance_percentage > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $estimatedYield->variance_percentage > 0 ? '+' : '' }}{{ number_format($estimatedYield->variance_percentage, 2) }}%
                                    </p>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            @endif

            <!-- Estado vs Límite de Plantación -->
            @if($harvestLimitInfo)
                <div class="glass-card rounded-xl p-6">
                    <h3 class="text-lg font-bold text-[var(--color-agro-green-dark)] mb-4">Estado vs Límite de Plantación</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm font-semibold text-gray-500">Límite máximo</label>
                            <p class="text-gray-900 font-bold">{{ number_format($harvestLimitInfo['limit'], 2) }} kg</p>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-gray-500">Total cosechado (campaña)</label>
                            <p class="text-gray-900 font-bold">{{ number_format($harvestLimitInfo['harvested'], 2) }} kg</p>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-gray-500">Disponible restante</label>
                            <p class="text-gray-900 font-bold {{ $harvestLimitInfo['remaining'] < 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ number_format($harvestLimitInfo['remaining'], 2) }} kg
                            </p>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-gray-500">Porcentaje usado</label>
                            <p class="text-gray-900 font-bold {{ $harvestLimitInfo['percentage'] > 100 ? 'text-red-600' : ($harvestLimitInfo['percentage'] > 80 ? 'text-orange-600' : 'text-gray-600') }}">
                                {{ number_format($harvestLimitInfo['percentage'], 1) }}%
                            </p>
                        </div>
                        @if($harvestLimitInfo['exceeds'])
                            <div class="mt-3 bg-red-50 border-l-4 border-red-500 p-3 rounded-r-lg">
                                <p class="text-sm font-semibold text-red-800">
                                    ⚠️ El total cosechado excede el límite de la plantación
                                </p>
                            </div>
                        @elseif($harvestLimitInfo['percentage'] > 80)
                            <div class="mt-3 bg-orange-50 border-l-4 border-orange-500 p-3 rounded-r-lg">
                                <p class="text-sm font-semibold text-orange-800">
                                    ⚠️ Se ha utilizado más del 80% del límite
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Información de Edición -->
            @if($harvest->wasEdited())
                <div class="glass-card rounded-xl p-6 bg-amber-50 border border-amber-200">
                    <h3 class="text-lg font-bold text-amber-900 mb-4">Historial de Ediciones</h3>
                    <div class="space-y-2 text-sm">
                        <div>
                            <label class="text-amber-700 font-semibold">Editada el:</label>
                            <p class="text-amber-900">{{ $harvest->edited_at->format('d/m/Y H:i') }}</p>
                        </div>
                        @if($harvest->editor)
                            <div>
                                <label class="text-amber-700 font-semibold">Por:</label>
                                <p class="text-amber-900">{{ $harvest->editor->name }}</p>
                            </div>
                        @endif
                        @if($harvest->edit_notes)
                            <div>
                                <label class="text-amber-700 font-semibold">Motivo:</label>
                                <p class="text-amber-900">{{ $harvest->edit_notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Fechas -->
            <div class="glass-card rounded-xl p-6">
                <h3 class="text-lg font-bold text-[var(--color-agro-green-dark)] mb-4">Fechas</h3>
                <div class="space-y-2 text-sm">
                    <div>
                        <label class="text-gray-500">Creada</label>
                        <p class="text-gray-900">{{ $harvest->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <label class="text-gray-500">Actualizada</label>
                        <p class="text-gray-900">{{ $harvest->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

