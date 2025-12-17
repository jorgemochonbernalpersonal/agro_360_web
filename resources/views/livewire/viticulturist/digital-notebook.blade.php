<div class="space-y-6 animate-fade-in">
    <!-- Mensajes Flash -->
    @if(session('message'))
        <div class="glass-card rounded-xl p-4 bg-green-50 border-l-4 border-green-600">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm font-semibold text-green-800">{{ session('message') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="glass-card rounded-xl p-4 bg-red-50 border-l-4 border-red-600">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm font-semibold text-red-800">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <!-- Header -->
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>';
    @endphp
    <x-page-header
        :icon="$icon"
        title="Cuaderno Digital"
        :description="$currentCampaign ? 'Campaña ' . $currentCampaign->name . ' - ' . $currentCampaign->year : 'Registro completo de todas tus actividades agrícolas'"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    >
        <x-slot:actionButton>
            @if($currentCampaign)
                <div class="flex items-center gap-3">
                    <span class="text-sm font-semibold text-gray-700">Campaña:</span>
                    <select 
                        wire:model.live="selectedCampaign" 
                        class="px-4 py-2 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all bg-white"
                    >
                        @foreach($campaigns as $campaign)
                            <option value="{{ $campaign->id }}">
                                {{ $campaign->name }} ({{ $campaign->year }})
                                @if($campaign->active) [Activa] @endif
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
        </x-slot:actionButton>
    </x-page-header>

    @if($currentCampaign)
        <!-- Estadísticas de la Campaña -->
        <div class="glass-card rounded-xl p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-[var(--color-agro-green-dark)]">{{ $stats['total'] }}</div>
                    <div class="text-sm text-gray-600">Total Actividades</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-600">{{ $stats['phytosanitary'] }}</div>
                    <div class="text-sm text-gray-600">Tratamientos</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ $stats['fertilization'] }}</div>
                    <div class="text-sm text-gray-600">Fertilizaciones</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-cyan-600">{{ $stats['irrigation'] }}</div>
                    <div class="text-sm text-gray-600">Riegos</div>
                </div>
            </div>
            
            <!-- Botones de acción rápida -->
            @can('create', \App\Models\AgriculturalActivity::class)
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <div class="flex flex-wrap gap-3 justify-center">
                        <a href="{{ route('viticulturist.digital-notebook.treatment.create') }}" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm font-semibold">
                            + Tratamiento
                        </a>
                        <a href="{{ route('viticulturist.digital-notebook.fertilization.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-semibold">
                            + Fertilización
                        </a>
                        <a href="{{ route('viticulturist.digital-notebook.irrigation.create') }}" class="px-4 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 transition text-sm font-semibold">
                            + Riego
                        </a>
                        <a href="{{ route('viticulturist.digital-notebook.cultural.create') }}" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm font-semibold">
                            + Labor
                        </a>
                        <a href="{{ route('viticulturist.digital-notebook.observation.create') }}" class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition text-sm font-semibold">
                            + Observación
                        </a>
                    </div>
                </div>
            @endcan
        </div>
    @endif

    <!-- Filtros -->
    <div class="glass-card rounded-xl p-6">
        <div class="flex items-center gap-3 mb-4">
            <svg class="w-5 h-5 text-[var(--color-agro-green-dark)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
            </svg>
            <h2 class="text-lg font-semibold text-[var(--color-agro-green-dark)]">Filtros de Búsqueda</h2>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
            <!-- Filtro por Parcela -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Parcela</label>
                <select 
                    wire:model.live="selectedPlot" 
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all"
                >
                    <option value="">Todas las parcelas</option>
                    @foreach($plots as $plot)
                        <option value="{{ $plot->id }}">{{ $plot->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filtro por Tipo de Actividad -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Tipo de Actividad</label>
                <select 
                    wire:model.live="activityType" 
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all"
                >
                    <option value="">Todas las actividades</option>
                    <option value="phytosanitary">Tratamientos Fitosanitarios</option>
                    <option value="fertilization">Fertilizaciones</option>
                    <option value="irrigation">Riegos</option>
                    <option value="cultural">Labores Culturales</option>
                    <option value="observation">Observaciones</option>
                </select>
            </div>

            <!-- Filtro por Producto (solo si es tratamiento fitosanitario) -->
            @if($activityType === 'phytosanitary' && $products->count() > 0)
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Producto</label>
                    <select 
                        wire:model.live="productFilter" 
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all"
                    >
                        <option value="">Todos los productos</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- Filtro por Fecha Desde -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha Desde</label>
                <input 
                    type="date" 
                    wire:model.live="dateFrom"
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all"
                >
            </div>

            <!-- Filtro por Fecha Hasta -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha Hasta</label>
                <input 
                    type="date" 
                    wire:model.live="dateTo"
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all"
                >
            </div>

            <!-- Búsqueda -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Búsqueda</label>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search"
                    placeholder="Buscar en notas, parcelas, productos..."
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all"
                >
            </div>
        </div>

        <!-- Botón Limpiar Filtros -->
        @if($selectedPlot || $activityType || $search || $dateFrom || $dateTo || $productFilter)
            <div class="flex justify-end">
                <button 
                    wire:click="clearFilters"
                    class="px-4 py-2 text-sm font-semibold text-[var(--color-agro-green-dark)] hover:bg-[var(--color-agro-green-bg)] rounded-lg transition-colors"
                >
                    Limpiar Filtros
                </button>
            </div>
        @endif
    </div>

    <!-- Tabla de Actividades -->
    <div class="glass-card rounded-2xl overflow-hidden shadow-xl">
        @if($activities->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-[var(--color-agro-green-bg)] to-[var(--color-agro-green-bright)]/30">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-[var(--color-agro-green-dark)] uppercase">Fecha</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-[var(--color-agro-green-dark)] uppercase">Parcela</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-[var(--color-agro-green-dark)] uppercase">Tipo</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-[var(--color-agro-green-dark)] uppercase">Detalle</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-[var(--color-agro-green-dark)] uppercase">Cuadrilla</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-[var(--color-agro-green-dark)] uppercase">Notas</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-[var(--color-agro-green-dark)] uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @foreach($activities as $activity)
                            <tr class="hover:bg-[var(--color-agro-green-bg)]/40 transition-all">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-semibold text-gray-900">
                                        {{ $activity->activity_date->format('d/m/Y') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm font-medium text-gray-900">{{ $activity->plot->name }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($activity->activity_type === 'phytosanitary')
                                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold bg-red-50 text-red-700 ring-1 ring-red-600/20">
                                            Tratamiento
                                        </span>
                                    @elseif($activity->activity_type === 'fertilization')
                                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 ring-1 ring-blue-600/20">
                                            Fertilización
                                        </span>
                                    @elseif($activity->activity_type === 'irrigation')
                                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold bg-cyan-50 text-cyan-700 ring-1 ring-cyan-600/20">
                                            Riego
                                        </span>
                                    @elseif($activity->activity_type === 'cultural')
                                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold bg-yellow-50 text-yellow-700 ring-1 ring-yellow-600/20">
                                            Labor
                                        </span>
                                    @elseif($activity->activity_type === 'observation')
                                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold bg-gray-50 text-gray-700 ring-1 ring-gray-600/20">
                                            Observación
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($activity->phytosanitaryTreatment)
                                        <div class="text-sm">
                                            <span class="font-semibold text-gray-900">{{ $activity->phytosanitaryTreatment->product->name }}</span>
                                            @if($activity->phytosanitaryTreatment->area_treated)
                                                <span class="text-gray-600"> - {{ number_format($activity->phytosanitaryTreatment->area_treated, 3) }} ha</span>
                                            @endif
                                            @if($activity->phytosanitaryTreatment->target_pest)
                                                <div class="text-xs text-gray-500 mt-1">Objetivo: {{ $activity->phytosanitaryTreatment->target_pest }}</div>
                                            @endif
                                        </div>
                                    @elseif($activity->fertilization)
                                        <div class="text-sm">
                                            <span class="font-semibold text-gray-900">{{ $activity->fertilization->fertilizer_name ?: 'Fertilización' }}</span>
                                            @if($activity->fertilization->quantity)
                                                <span class="text-gray-600"> - {{ number_format($activity->fertilization->quantity, 2) }} kg</span>
                                            @endif
                                        </div>
                                    @elseif($activity->irrigation)
                                        <div class="text-sm">
                                            <span class="font-semibold text-gray-900">Riego</span>
                                            @if($activity->irrigation->water_volume)
                                                <span class="text-gray-600"> - {{ number_format($activity->irrigation->water_volume, 2) }} L</span>
                                            @endif
                                        </div>
                                    @elseif($activity->culturalWork)
                                        <div class="text-sm">
                                            <span class="font-semibold text-gray-900">{{ $activity->culturalWork->work_type ?: 'Labor cultural' }}</span>
                                            @if($activity->culturalWork->hours_worked)
                                                <span class="text-gray-600"> - {{ number_format($activity->culturalWork->hours_worked, 2) }} h</span>
                                            @endif
                                        </div>
                                    @elseif($activity->observation)
                                        <div class="text-sm">
                                            <span class="font-semibold text-gray-900">{{ $activity->observation->observation_type ?: 'Observación' }}</span>
                                            @if($activity->observation->severity)
                                                <span class="text-gray-600"> - {{ ucfirst($activity->observation->severity) }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-gray-700">{{ $activity->crew->name ?? '-' }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($activity->notes)
                                        <span class="text-sm text-gray-600">{{ Str::limit($activity->notes, 50) }}</span>
                                    @else
                                        <span class="text-sm text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        @can('view', $activity)
                                            <button 
                                                class="px-3 py-1 text-xs font-semibold text-blue-600 hover:bg-blue-50 rounded-lg transition"
                                                title="Ver detalles"
                                            >
                                                Ver
                                            </button>
                                        @endcan
                                        @can('update', $activity)
                                            <button 
                                                class="px-3 py-1 text-xs font-semibold text-green-600 hover:bg-green-50 rounded-lg transition"
                                                title="Editar"
                                            >
                                                Editar
                                            </button>
                                        @endcan
                                        @can('delete', $activity)
                                            <button 
                                                wire:click="deleteActivity({{ $activity->id }})"
                                                wire:confirm="¿Estás seguro de eliminar esta actividad?"
                                                class="px-3 py-1 text-xs font-semibold text-red-600 hover:bg-red-50 rounded-lg transition"
                                                title="Eliminar"
                                            >
                                                Eliminar
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Paginación -->
            <div class="px-6 py-4 border-t border-gray-200 bg-gradient-to-r from-[var(--color-agro-green-bg)]/30 to-transparent">
                {{ $activities->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="p-16 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="text-xl font-bold text-gray-900 mb-2">No hay actividades registradas</h3>
                <p class="text-gray-500 mb-6">
                    @if($selectedPlot || $activityType || $search || $dateFrom || $dateTo)
                        No se encontraron actividades con los filtros seleccionados
                    @else
                        Comienza registrando tu primera actividad agrícola
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>

