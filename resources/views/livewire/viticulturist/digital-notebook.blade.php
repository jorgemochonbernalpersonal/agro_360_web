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
    <x-filter-section title="Filtros de Búsqueda" color="green">
        <x-filter-select wire:model.live="selectedPlot">
            <option value="">Todas las parcelas</option>
            @foreach($plots as $plot)
                <option value="{{ $plot->id }}">{{ $plot->name }}</option>
            @endforeach
        </x-filter-select>

        <x-filter-select wire:model.live="activityType">
            <option value="">Todas las actividades</option>
            <option value="phytosanitary">Tratamientos Fitosanitarios</option>
            <option value="fertilization">Fertilizaciones</option>
            <option value="irrigation">Riegos</option>
            <option value="cultural">Labores Culturales</option>
            <option value="observation">Observaciones</option>
        </x-filter-select>

        @if($activityType === 'phytosanitary' && $products->count() > 0)
            <x-filter-select wire:model.live="productFilter">
                <option value="">Todos los productos</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                @endforeach
            </x-filter-select>
        @endif

        <x-filter-input 
            wire:model.live="dateFrom" 
            type="date"
            placeholder="Fecha desde..."
            icon="calendar"
        />

        <x-filter-input 
            wire:model.live="dateTo" 
            type="date"
            placeholder="Fecha hasta..."
            icon="calendar"
        />

        <x-filter-input 
            wire:model.live.debounce.300ms="search" 
            placeholder="Buscar en notas, parcelas, productos..."
        />

        <x-slot:actions>
            @if($selectedPlot || $activityType || $search || $dateFrom || $dateTo || $productFilter)
                <x-button wire:click="clearFilters" variant="ghost" size="sm">
                    Limpiar Filtros
                </x-button>
            @endif
        </x-slot:actions>
    </x-filter-section>

    <!-- Tabla de Actividades -->
    @php
        $headers = [
            ['label' => 'Fecha', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'],
            ['label' => 'Parcela', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>'],
            ['label' => 'Tipo', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>'],
            ['label' => 'Detalle', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'],
            ['label' => 'Cuadrilla', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>'],
            ['label' => 'Notas', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>'],
            'Acciones',
        ];
    @endphp

    <x-data-table 
        :headers="$headers" 
        empty-message="No hay actividades registradas" 
        empty-description="{{ ($selectedPlot || $activityType || $search || $dateFrom || $dateTo || $productFilter) ? 'No se encontraron actividades con los filtros seleccionados' : 'Comienza registrando tu primera actividad agrícola' }}"
        color="green"
    >
        @if($activities->count() > 0)
            @foreach($activities as $activity)
                <x-table-row>
                    <x-table-cell>
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-[var(--color-agro-blue-light)] text-[var(--color-agro-blue)] text-sm font-semibold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            {{ $activity->activity_date->format('d/m/Y') }}
                        </span>
                    </x-table-cell>
                    <x-table-cell>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-[var(--color-agro-green)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                            </svg>
                            <span class="text-sm font-medium text-gray-900">{{ $activity->plot->name }}</span>
                        </div>
                    </x-table-cell>
                    <x-table-cell>
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
                    </x-table-cell>
                    <x-table-cell>
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
                    </x-table-cell>
                    <x-table-cell>
                        <span class="text-sm text-gray-700">{{ $activity->crew->name ?? '-' }}</span>
                    </x-table-cell>
                    <x-table-cell>
                        @if($activity->notes)
                            <span class="text-sm text-gray-600">{{ Str::limit($activity->notes, 50) }}</span>
                        @else
                            <span class="text-sm text-gray-400">-</span>
                        @endif
                    </x-table-cell>
                    <x-table-actions align="right">
                        @can('view', $activity)
                            <x-action-button variant="view" href="{{ route('viticulturist.digital-notebook', ['activity' => $activity->id]) }}" />
                        @endcan
                        @can('update', $activity)
                            <x-action-button variant="edit" href="{{ route('viticulturist.digital-notebook', ['activity' => $activity->id, 'edit' => true]) }}" />
                        @endcan
                        @can('delete', $activity)
                            <x-action-button 
                                variant="delete" 
                                wireClick="deleteActivity({{ $activity->id }})"
                                wireConfirm="¿Estás seguro de eliminar esta actividad?"
                            />
                        @endcan
                    </x-table-actions>
                </x-table-row>
            @endforeach
            <x-slot name="pagination">
                {{ $activities->links() }}
            </x-slot>
        @endif
    </x-data-table>
</div>

