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
                <div class="mt-6 pt-6 border-t border-gray-200" data-cy="quick-actions">
                    <div class="flex flex-wrap gap-3 justify-center">
                        <a href="{{ route('viticulturist.digital-notebook.treatment.create') }}" data-cy="create-treatment-button" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm font-semibold">
                            + Tratamiento
                        </a>
                        <a href="{{ route('viticulturist.digital-notebook.fertilization.create') }}" data-cy="create-fertilization-button" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-semibold">
                            + Fertilización
                        </a>
                        <a href="{{ route('viticulturist.digital-notebook.irrigation.create') }}" data-cy="create-irrigation-button" class="px-4 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 transition text-sm font-semibold">
                            + Riego
                        </a>
                        <a href="{{ route('viticulturist.digital-notebook.cultural.create') }}" data-cy="create-cultural-button" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm font-semibold">
                            + Labor
                        </a>
                        <a href="{{ route('viticulturist.digital-notebook.observation.create') }}" data-cy="create-observation-button" class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition text-sm font-semibold">
                            + Observación
                        </a>
                        <a href="{{ route('viticulturist.digital-notebook.harvest.create') }}" data-cy="create-harvest-button" class="px-4 py-2 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-lg hover:from-purple-700 hover:to-purple-800 transition text-sm font-semibold shadow-md">
                            🍇 Cosecha
                        </a>
                    </div>
                </div>
            @endcan
        </div>
    @endif

    <!-- Filtros -->
    <x-filter-section title="Filtros de Búsqueda" color="green">
        <x-filter-select wire:model.live="selectedPlot" data-cy="plot-filter">
            <option value="">Todas las parcelas</option>
            @foreach($plots as $plot)
                <option value="{{ $plot->id }}">{{ $plot->name }}</option>
            @endforeach
        </x-filter-select>

        <x-filter-select wire:model.live="activityType" data-cy="activity-type-filter">
            <option value="">Todas las actividades</option>
            <option value="phytosanitary">Tratamientos Fitosanitarios</option>
            <option value="fertilization">Fertilizaciones</option>
            <option value="irrigation">Riegos</option>
            <option value="cultural">Labores Culturales</option>
            <option value="observation">Observaciones</option>
            <option value="harvest">Cosechas / Vendimias</option>
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
            data-cy="activity-search-input"
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
            ['label' => 'Equipo', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>'],
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
                            <div>
                                <span class="text-sm font-medium text-gray-900">{{ $activity->plot->name }}</span>
                                @if($activity->plotPlanting)
                                    <br><span class="text-xs text-gray-600">
                                        {{ $activity->plotPlanting->name }}
                                        @if($activity->plotPlanting->grapeVariety)
                                            - {{ $activity->plotPlanting->grapeVariety->name }}
                                        @endif
                                    </span>
                                @endif
                            </div>
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
                        @elseif($activity->activity_type === 'harvest')
                            <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold bg-purple-50 text-purple-700 ring-1 ring-purple-600/20">
                                🍇 Cosecha
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
                                @if($activity->phytosanitaryTreatment->pest)
                                    <div class="text-xs text-gray-500 mt-1">Objetivo: {{ $activity->phytosanitaryTreatment->pest->name }}</div>
                                @endif
                                
                                {{-- Safety Interval Badge --}}
                                @php
                                    $product = $activity->phytosanitaryTreatment->product;
                                    $safetyDays = $product->withdrawal_period_days ?? 0;
                                    
                                    if ($safetyDays > 0) {
                                        $treatmentDate = \Carbon\Carbon::parse($activity->activity_date);
                                        $safeDate = $treatmentDate->copy()->addDays($safetyDays);
                                        $isPassed = \Carbon\Carbon::today() >= $safeDate;
                                        $daysRemaining = \Carbon\Carbon::today()->diffInDays($safeDate, false);
                                    }
                                @endphp
                                
                                @if($safetyDays > 0)
                                    <div class="mt-2">
                                        @if($isPassed)
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-semibold bg-green-100 text-green-700 border border-green-200">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                Puede cosechar (desde {{ $safeDate->format('d/m/Y') }})
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-semibold bg-amber-100 text-amber-700 border border-amber-200">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                </svg>
                                                Esperar {{ abs($daysRemaining) }} día{{ abs($daysRemaining) != 1 ? 's' : '' }} (hasta {{ $safeDate->format('d/m/Y') }})
                                            </span>
                                        @endif
                                    </div>
                                @elseif($safetyDays === 0)
                                    <div class="mt-2">
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                            </svg>
                                            Sin plazo definido
                                        </span>
                                    </div>
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
                        @elseif($activity->harvest)
                            <div class="text-sm">
                                <span class="font-semibold text-purple-900">🍇 Vendimia</span>
                                @if($activity->harvest->plotPlanting && $activity->harvest->plotPlanting->grapeVariety)
                                    <span class="text-purple-700"> - {{ $activity->harvest->plotPlanting->grapeVariety->name }}</span>
                                @endif
                                <div class="flex gap-3 mt-1">
                                    <span class="text-xs font-semibold text-gray-700">{{ number_format($activity->harvest->total_weight, 0) }} kg</span>
                                    @if($activity->harvest->yield_per_hectare)
                                        <span class="text-xs text-gray-600">({{ number_format($activity->harvest->yield_per_hectare, 0) }} kg/ha)</span>
                                    @endif
                                    @if($activity->harvest->total_value)
                                        <span class="text-xs font-semibold text-green-700">{{ number_format($activity->harvest->total_value, 2) }}€</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </x-table-cell>
                    <x-table-cell>
                        @if($activity->crew)
                            <span class="text-sm text-gray-700">
                                Cuadrilla: {{ $activity->crew->name }}
                            </span>
                        @elseif($activity->crewMember && $activity->crewMember->viticulturist)
                            <span class="text-sm text-gray-700">
                                Trabajador: {{ $activity->crewMember->viticulturist->name }}
                            </span>
                        @else
                            <span class="text-sm text-gray-400">-</span>
                        @endif
                    </x-table-cell>
                    <x-table-cell>
                        @if($activity->notes)
                            <span class="text-sm text-gray-600">{{ Str::limit($activity->notes, 50) }}</span>
                        @else
                            <span class="text-sm text-gray-400">-</span>
                        @endif
                    </x-table-cell>
                    <x-table-actions align="right">
                        {{-- Botón de Historial de Auditoría --}}
                        <button 
                            wire:click="openAuditHistory({{ $activity->id }})"
                            class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition inline-flex items-center gap-1"
                            title="Ver historial de cambios"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Historial
                        </button>
                        
                        {{-- Badge de actividad bloqueada --}}
                        @if($activity->is_locked)
                            <x-activity-locked-badge :activity="$activity" />
                        @endif
                        
                        @if($activity->harvest)
                            {{-- Para cosechas, mostrar botones de ver y editar con iconos --}}
                            <x-action-button variant="view" href="{{ route('viticulturist.digital-notebook.harvest.show', $activity->harvest->id) }}" />
                            @can('update', $activity)
                                @if(!$activity->is_locked)
                                    <x-action-button variant="edit" href="{{ route('viticulturist.digital-notebook.harvest.edit', $activity->harvest->id) }}" />

                                @endif
                            @endcan
                        @else
                            {{-- Edit buttons for all activity types (same pattern as harvest) --}}
                            @can('update', $activity)
                                @if(!$activity->is_locked)
                                    @if($activity->activity_type === 'phytosanitary')
                                        <x-action-button variant="edit" href="{{ route('viticulturist.digital-notebook.treatment.edit', $activity->id) }}" />
                                    @elseif($activity->activity_type === 'fertilization')
                                        <x-action-button variant="edit" href="{{ route('viticulturist.digital-notebook.fertilization.edit', $activity->id) }}" />
                                    @elseif($activity->activity_type === 'irrigation')
                                        <x-action-button variant="edit" href="{{ route('viticulturist.digital-notebook.irrigation.edit', $activity->id) }}" />
                                    @elseif($activity->activity_type === 'cultural')
                                        <x-action-button variant="edit" href="{{ route('viticulturist.digital-notebook.cultural.edit', $activity->id) }}" />
                                    @elseif($activity->activity_type === 'observation')
                                        <x-action-button variant="edit" href="{{ route('viticulturist.digital-notebook.observation.edit', $activity->id) }}" />
                                    @endif

                                @endif
                            @endcan
                            @can('delete', $activity)
                                @if(!$activity->is_locked)
                                    <x-action-button 
                                        variant="delete" 
                                        wireClick="deleteActivity({{ $activity->id }})"
                                        wireConfirm="¿Estás seguro de eliminar esta actividad?"
                                    />
                                @endif
                            @endcan
                        @endif
                    </x-table-actions>
                </x-table-row>
            @endforeach
            <x-slot name="pagination">
                {{ $activities->links() }}
            </x-slot>
        @endif
    </x-data-table>

    {{-- Modal de Historial de Auditoría --}}
    @if($showAuditHistory && $selectedActivityId)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col">
                {{-- Header --}}
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">
                        📋 Historial de Auditoría
                    </h3>
                    <button 
                        wire:click="closeAuditHistory"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Content --}}
                <div class="px-6 py-4 overflow-y-auto flex-1">
                    @livewire('viticulturist.digital-notebook.activity-audit-history', ['activity' => \App\Models\AgriculturalActivity::find($selectedActivityId)], 'audit-'.$selectedActivityId)
                </div>

                {{-- Footer --}}
                <div class="bg-gray-50 px-6 py-3 border-t border-gray-200 flex justify-end">
                    <button 
                        wire:click="closeAuditHistory"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
                    >
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif


</div>

