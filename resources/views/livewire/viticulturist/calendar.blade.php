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
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>';
    @endphp
    <x-page-header
        :icon="$icon"
        title="Calendario de Actividades"
        :description="$currentCampaign ? 'Campaña ' . $currentCampaign->name . ' - ' . $currentCampaign->year : 'Visualiza todas tus actividades agrícolas'"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    >
        <x-slot:actionButton>
            @if($currentCampaign)
                <div class="flex items-center gap-3">
                    <span class="text-sm font-semibold text-gray-700">Campaña:</span>
                    <select 
                        wire:model.live="selectedCampaign" 
                        class="px-4 py-2 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green)] focus:border-transparent transition-all bg-white"
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

    <!-- Estadísticas del Mes -->
    <div class="glass-card rounded-xl p-6">
        <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</div>
                <div class="text-sm text-gray-600">Total</div>
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
            <div class="text-center">
                <div class="text-2xl font-bold text-yellow-600">{{ $stats['cultural'] }}</div>
                <div class="text-sm text-gray-600">Labores</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-gray-600">{{ $stats['observation'] }}</div>
                <div class="text-sm text-gray-600">Observaciones</div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="glass-card rounded-xl p-6">
        <div class="flex items-center gap-3 mb-4">
            <svg class="w-5 h-5 text-[var(--color-agro-green-dark)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
            </svg>
            <h2 class="text-lg font-semibold text-gray-900">Filtros</h2>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Filtro por Tipo de Actividad -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Tipo de Actividad</label>
                <select 
                    wire:model.live="activityType" 
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green)] focus:border-transparent transition-all"
                >
                    <option value="">Todas las actividades</option>
                    <option value="phytosanitary">Tratamientos Fitosanitarios</option>
                    <option value="fertilization">Fertilizaciones</option>
                    <option value="irrigation">Riegos</option>
                    <option value="cultural">Labores Culturales</option>
                    <option value="observation">Observaciones</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Calendario -->
    <div class="glass-card rounded-xl p-6">
        <!-- Navegación del Calendario -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-4">
                <button 
                    wire:click="previousMonth"
                    class="p-2 rounded-lg hover:bg-gray-100 transition-colors"
                    title="Mes anterior"
                >
                    <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <h2 class="text-2xl font-bold text-gray-900">
                    {{ $monthName }} {{ $currentYear }}
                </h2>
                <button 
                    wire:click="nextMonth"
                    class="p-2 rounded-lg hover:bg-gray-100 transition-colors"
                    title="Mes siguiente"
                >
                    <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
            <button 
                wire:click="goToToday"
                class="px-4 py-2 bg-[var(--color-agro-green)] text-white rounded-lg hover:bg-[var(--color-agro-green-dark)] transition-colors font-semibold"
            >
                Hoy
            </button>
        </div>

        <!-- Días de la semana -->
        <div class="grid grid-cols-7 gap-2 mb-2">
            @foreach(['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'] as $day)
                <div class="text-center text-sm font-bold text-gray-700 py-2">
                    {{ $day }}
                </div>
            @endforeach
        </div>

        <!-- Días del calendario -->
        <div class="grid grid-cols-7 gap-2">
            @foreach($calendarDays as $day)
                <div 
                    class="min-h-[100px] border-2 rounded-lg p-2 transition-all cursor-pointer hover:shadow-md
                        {{ $day['isCurrentMonth'] ? 'bg-white border-gray-200' : 'bg-gray-50 border-gray-100 opacity-60' }}
                        {{ $day['isToday'] ? 'ring-2 ring-[var(--color-agro-green)] border-[var(--color-agro-green)]' : '' }}
                    "
                    wire:click="selectDate('{{ $day['dateKey'] }}')"
                >
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-semibold {{ $day['isToday'] ? 'text-[var(--color-agro-green-dark)]' : ($day['isCurrentMonth'] ? 'text-gray-900' : 'text-gray-400') }}">
                            {{ $day['day'] }}
                        </span>
                        @if($day['activityCount'] > 0)
                            <span class="text-xs font-bold text-gray-600 bg-gray-200 px-2 py-0.5 rounded-full">
                                {{ $day['activityCount'] }}
                            </span>
                        @endif
                    </div>
                    
                    <div class="space-y-1 mt-1">
                        @foreach($day['activities']->take(3) as $activity)
                            <div 
                                class="text-xs px-2 py-1 rounded border {{ $this->getActivityTypeColor($activity->activity_type) }} truncate"
                                title="{{ $this->getActivityTypeLabel($activity->activity_type) }} - {{ $activity->plot->name }}"
                            >
                                {{ $this->getActivityTypeLabel($activity->activity_type) }}
                            </div>
                        @endforeach
                        @if($day['activityCount'] > 3)
                            <div class="text-xs text-gray-500 font-semibold text-center">
                                +{{ $day['activityCount'] - 3 }} más
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Leyenda -->
    <div class="glass-card rounded-xl p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Leyenda</h3>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-red-100 border border-red-300"></div>
                <span class="text-sm text-gray-700">Tratamientos</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-blue-100 border border-blue-300"></div>
                <span class="text-sm text-gray-700">Fertilizaciones</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-cyan-100 border border-cyan-300"></div>
                <span class="text-sm text-gray-700">Riegos</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-yellow-100 border border-yellow-300"></div>
                <span class="text-sm text-gray-700">Labores</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-gray-100 border border-gray-300"></div>
                <span class="text-sm text-gray-700">Observaciones</span>
            </div>
        </div>
    </div>

    <!-- Modal de Actividades del Día -->
    @if($showActivityModal && $selectedActivity)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" wire:click="closeModal">
            <div class="glass-card rounded-xl p-6 max-w-2xl w-full max-h-[90vh] overflow-y-auto" wire:click.stop>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-gray-900">
                        Actividades del {{ $this->getFormattedSelectedDate() }}
                    </h3>
                    <button 
                        wire:click="closeModal"
                        class="p-2 rounded-lg hover:bg-gray-100 transition-colors"
                    >
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                @if($selectedActivity->count() > 0)
                    <div class="space-y-4">
                        @foreach($selectedActivity as $activity)
                            <div class="border-2 rounded-lg p-4 {{ $this->getActivityTypeColor($activity->activity_type) }}">
                                <div class="flex items-start justify-between mb-2">
                                    <div>
                                        <span class="font-bold text-lg">{{ $this->getActivityTypeLabel($activity->activity_type) }}</span>
                                        <span class="text-sm text-gray-600 ml-2">{{ $activity->plot->name }}</span>
                                    </div>
                                </div>

                                @if($activity->phytosanitaryTreatment)
                                    <div class="text-sm mt-2">
                                        <p><strong>Producto:</strong> {{ $activity->phytosanitaryTreatment->product->name }}</p>
                                        @if($activity->phytosanitaryTreatment->area_treated)
                                            <p><strong>Área tratada:</strong> {{ number_format($activity->phytosanitaryTreatment->area_treated, 3) }} ha</p>
                                        @endif
                                        @if($activity->phytosanitaryTreatment->target_pest)
                                            <p><strong>Objetivo:</strong> {{ $activity->phytosanitaryTreatment->target_pest }}</p>
                                        @endif
                                    </div>
                                @elseif($activity->fertilization)
                                    <div class="text-sm mt-2">
                                        <p><strong>Fertilizante:</strong> {{ $activity->fertilization->fertilizer_name ?: 'N/A' }}</p>
                                        @if($activity->fertilization->quantity)
                                            <p><strong>Cantidad:</strong> {{ number_format($activity->fertilization->quantity, 2) }} kg</p>
                                        @endif
                                    </div>
                                @elseif($activity->irrigation)
                                    <div class="text-sm mt-2">
                                        @if($activity->irrigation->water_volume)
                                            <p><strong>Volumen de agua:</strong> {{ number_format($activity->irrigation->water_volume, 2) }} L</p>
                                        @endif
                                    </div>
                                @elseif($activity->culturalWork)
                                    <div class="text-sm mt-2">
                                        <p><strong>Tipo de labor:</strong> {{ $activity->culturalWork->work_type ?: 'N/A' }}</p>
                                        @if($activity->culturalWork->hours_worked)
                                            <p><strong>Horas trabajadas:</strong> {{ number_format($activity->culturalWork->hours_worked, 2) }} h</p>
                                        @endif
                                    </div>
                                @elseif($activity->observation)
                                    <div class="text-sm mt-2">
                                        <p><strong>Tipo:</strong> {{ $activity->observation->observation_type ?: 'N/A' }}</p>
                                        @if($activity->observation->severity)
                                            <p><strong>Severidad:</strong> {{ ucfirst($activity->observation->severity) }}</p>
                                        @endif
                                    </div>
                                @endif

                                @if($activity->crew)
                                    <p class="text-sm mt-2"><strong>Cuadrilla:</strong> {{ $activity->crew->name }}</p>
                                @endif

                                @if($activity->machinery)
                                    <p class="text-sm mt-2"><strong>Maquinaria:</strong> {{ $activity->machinery->name }}</p>
                                @endif

                                @if($activity->notes)
                                    <p class="text-sm mt-2"><strong>Notas:</strong> {{ $activity->notes }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <p class="text-gray-500">No hay actividades registradas para esta fecha</p>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

