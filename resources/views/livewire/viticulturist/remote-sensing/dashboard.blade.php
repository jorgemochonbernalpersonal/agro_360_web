<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('remote-sensing.dashboard') }}" 
                   class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span class="text-sm font-medium">Volver al Resumen</span>
                </a>
            </div>
            <h1 class="text-3xl font-bold text-agro-700 flex items-center gap-3">
                🛰️ Análisis Avanzado
            </h1>
            <p class="text-zinc-600 mt-1">Datos satelitales y meteorológicos detallados</p>
        </div>
        
        <div class="flex items-center gap-3">
            <!-- Plot Selector -->
            <flux:select wire:model.live="selectedPlotId" 
                    >
                @if(count($plots) > 0)
                    @foreach($plots as $plot)
                        <option value="{{ $plot->id }}" wire:key="plot-{{ $plot->id }}">{{ $plot->name }}</option>
                    @endforeach
                @else
                    <option value="">Sin parcelas con geometrías</option>
                @endif
            </flux:select>
            
            <button wire:click="refreshData" 
                    class="bg-gradient-to-r from-green-600 to-emerald-600 text-white px-4 py-2 rounded-lg hover:from-green-700 hover:to-emerald-700 transition flex items-center gap-2 shadow-lg">
                <svg wire:loading.remove wire:target="refreshData" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <svg wire:loading wire:target="refreshData" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Actualizar
            </button>
            
            <button wire:click="downloadReport" 
                    class="bg-white text-zinc-700 border border-zinc-300 px-4 py-2 rounded-lg hover:bg-zinc-50 transition flex items-center gap-2 shadow-sm">
                <svg wire:loading.remove wire:target="downloadReport" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <svg wire:loading wire:target="downloadReport" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Informe PDF
            </button>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
        <div class="bg-white rounded-lg shadow p-3 text-center">
            <div class="text-2xl font-bold text-zinc-700">{{ $stats['total_plots'] ?? 0 }}</div>
            <div class="text-xs text-zinc-500">Parcelas</div>
        </div>
        <div class="bg-green-50 rounded-lg shadow p-3 text-center border border-green-200">
            <div class="text-2xl font-bold text-green-600">{{ $stats['average_ndvi'] ?? 0 }}</div>
            <div class="text-xs text-zinc-500">NDVI Medio</div>
        </div>
        <div class="bg-emerald-50 rounded-lg shadow p-3 text-center border border-emerald-200">
            <div class="text-2xl font-bold text-emerald-600">{{ ($stats['excellent'] ?? 0) + ($stats['good'] ?? 0) }}</div>
            <div class="text-xs text-zinc-500">Saludables</div>
        </div>
        <div class="bg-yellow-50 rounded-lg shadow p-3 text-center border border-yellow-200">
            <div class="text-2xl font-bold text-yellow-600">{{ $stats['moderate'] ?? 0 }}</div>
            <div class="text-xs text-zinc-500">Moderadas</div>
        </div>
        <div class="bg-red-50 rounded-lg shadow p-3 text-center border border-red-200">
            <div class="text-2xl font-bold text-red-600">{{ $stats['alerts'] ?? 0 }}</div>
            <div class="text-xs text-zinc-500">⚠️ Alertas</div>
        </div>
    </div>

    <div wire:key="dashboard-body-{{ $isLoading ? 'loading' : ($selectedPlotId ?? 'none') }}">
    @if($isLoading)
        <div class="flex items-center justify-center py-16" wire:key="loading-state">
            <svg class="w-12 h-12 animate-spin text-green-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span class="ml-3 text-zinc-600 text-lg">Cargando datos...</span>
        </div>
    @elseif($selectedPlot)
        <!-- Recommendations Banner -->
        @if(count($recommendations) > 0)
            <div class="mb-6 flex flex-wrap gap-3" wire:key="recommendations-{{ $selectedPlotId ?? 'none' }}">
                @foreach($recommendations as $index => $rec)
                    <div wire:key="rec-{{ $selectedPlotId ?? 'none' }}-{{ $index }}" 
                         class="flex-1 min-w-[200px] p-3 rounded-lg border-l-4
                        @if($rec['type'] === 'danger') bg-red-50 border-red-500
                        @elseif($rec['type'] === 'warning') bg-amber-50 border-amber-500
                        @elseif($rec['type'] === 'success') bg-green-50 border-green-500
                        @else bg-blue-50 border-blue-500
                        @endif">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">{{ $rec['icon'] }}</span>
                            <span class="font-semibold text-sm">{{ $rec['title'] }}</span>
                        </div>
                        <p class="text-xs text-zinc-600 mt-1">{{ $rec['text'] }}</p>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Tabs Navigation -->
        <div class="mb-4 border-b border-zinc-200">
            <nav class="flex gap-1 overflow-x-auto" aria-label="Tabs">
                <button wire:click="setTab('satellite')" 
                        class="px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap
                            {{ $activeTab === 'satellite' ? 'border-green-500 text-green-600 bg-green-50' : 'border-transparent text-zinc-500 hover:text-zinc-700' }}">
                    🛰️ Satélite
                </button>
                <button wire:click="setTab('spectral')" 
                        class="px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap
                            {{ $activeTab === 'spectral' ? 'border-rainbow-500 text-purple-600 bg-purple-50' : 'border-transparent text-zinc-500 hover:text-zinc-700' }}">
                    🌈 Espectral
                </button>
                <button wire:click="setTab('lai-official')" 
                        class="px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap
                            {{ $activeTab === 'lai-official' ? 'border-emerald-500 text-emerald-600 bg-emerald-50' : 'border-transparent text-zinc-500 hover:text-zinc-700' }}">
                    🌿 LAI Oficial
                </button>
                <button wire:click="setTab('thermal')" 
                        class="px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap
                            {{ $activeTab === 'thermal' ? 'border-orange-500 text-orange-600 bg-orange-50' : 'border-transparent text-zinc-500 hover:text-zinc-700' }}">
                    🌡️ Térmico
                </button>
                <button wire:click="setTab('smap-soil')" 
                        class="px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap
                            {{ $activeTab === 'smap-soil' ? 'border-brown-500 text-amber-600 bg-amber-50' : 'border-transparent text-zinc-500 hover:text-zinc-700' }}">
                    🛰️ SMAP Suelo
                </button>
                <button wire:click="setTab('vigor-map')" 
                        class="px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap
                            {{ $activeTab === 'vigor-map' ? 'border-purple-500 text-purple-600 bg-purple-50' : 'border-transparent text-zinc-500 hover:text-zinc-700' }}">
                    🗺️ Mapa Vigor
                </button>
                <button wire:click="setTab('weather')" 
                        class="px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap
                            {{ $activeTab === 'weather' ? 'border-blue-500 text-blue-600 bg-blue-50' : 'border-transparent text-zinc-500 hover:text-zinc-700' }}">
                    🌦️ Clima
                </button>
                <button wire:click="setTab('soil')" 
                        class="px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap
                            {{ $activeTab === 'soil' ? 'border-amber-500 text-amber-600 bg-amber-50' : 'border-transparent text-zinc-500 hover:text-zinc-700' }}">
                    🌱 Suelo
                </button>
                <button wire:click="setTab('solar')" 
                        class="px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap
                            {{ $activeTab === 'solar' ? 'border-yellow-500 text-yellow-600 bg-yellow-50' : 'border-transparent text-zinc-500 hover:text-zinc-700' }}">
                    ☀️ Radiación
                </button>
                <button wire:click="setTab('irrigation')" 
                        class="px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap
                            {{ $activeTab === 'irrigation' ? 'border-cyan-500 text-cyan-600 bg-cyan-50' : 'border-transparent text-zinc-500 hover:text-zinc-700' }}">
                    💧 Riego
                </button>
                <button wire:click="setTab('harvest')" 
                        class="px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap
                            {{ $activeTab === 'harvest' ? 'border-pink-500 text-pink-600 bg-pink-50' : 'border-transparent text-zinc-500 hover:text-zinc-700' }}">
                    🍇 Cosecha
                </button>
                <button wire:click="setTab('compare')" 
                        class="px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap
                            {{ $activeTab === 'compare' ? 'border-violet-500 text-violet-600 bg-violet-50' : 'border-transparent text-zinc-500 hover:text-zinc-700' }}">
                    ⚖️ Comparar
                </button>
                <button wire:click="setTab('history')" 
                        class="px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap
                            {{ $activeTab === 'history' ? 'border-purple-500 text-purple-600 bg-purple-50' : 'border-transparent text-zinc-500 hover:text-zinc-700' }}">
                    📊 Histórico
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="bg-white rounded-lg shadow-lg p-6" wire:key="tab-content-{{ $selectedPlotId ?? 'none' }}-{{ $activeTab }}">
            <!-- Header with plot name -->
            <div class="flex items-center gap-3 mb-4 pb-4 border-b">
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                    <span class="text-xl">🌱</span>
                </div>
                <div>
                    <h2 class="font-bold text-lg">{{ $selectedPlot->name }}</h2>
                    <p class="text-sm text-zinc-500">{{ $selectedPlot->municipality?->name ?? 'Sin municipio' }}</p>
                </div>
            </div>
            
            {{-- Recinto Selector --}}
            @if(!empty($availableRecintos))
                {{-- Caso 1: Solo 1 recinto - Info estática --}}
                @if(count($availableRecintos) === 1)
                    <div class="mb-4 bg-blue-50 border-l-4 border-blue-500 rounded-r-lg p-3">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"/>
                            </svg>
                            <div class="flex-1">
                                <div class="text-sm font-semibold text-blue-900">
                                    📍 {{ $availableRecintos[0]['display_name'] }}
                                </div>
                                <div class="text-xs text-blue-700 mt-1">
                                    ✓ Analizando {{ number_format($availableRecintos[0]['area_ha'], 2) }} hectáreas
                                    @if($availableRecintos[0]['centroid'])
                                        • Lat: {{ number_format($availableRecintos[0]['centroid']['lat'], 4) }}°, 
                                        Lon: {{ number_format($availableRecintos[0]['centroid']['lng'], 4) }}°
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                
                {{-- Caso 2: 2-4 recintos - Botones Radio --}}
                @elseif(count($availableRecintos) <= 4)
                    <div class="mb-4 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-4">
                        <label class="block text-sm font-semibold text-zinc-800 mb-3">
                            📍 Selecciona recinto a analizar:
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($availableRecintos as $recinto)
                                <button 
                                    wire:click="$set('selectedRecintoId', {{ $recinto['id'] }})"
                                    wire:key="recinto-btn-{{ $recinto['id'] }}"
                                    class="text-left p-3 rounded-lg border-2 transition-all
                                        {{ $selectedRecintoId == $recinto['id'] 
                                            ? 'border-green-500 bg-green-50 shadow-md' 
                                            : 'border-zinc-300 bg-white hover:border-blue-400' }}">
                                    
                                    <div class="flex items-start gap-2">
                                        <div class="mt-0.5">
                                            @if($selectedRecintoId == $recinto['id'])
                                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                                </svg>
                                            @else
                                                <svg class="w-5 h-5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 20 20">
                                                    <circle cx="10" cy="10" r="8" stroke-width="2"/>
                                                </svg>
                                            @endif
                                        </div>
                                        
                                        <div class="flex-1">
                                            <div class="text-sm font-semibold {{ $selectedRecintoId == $recinto['id'] ? 'text-green-900' : 'text-zinc-900' }}">
                                                {{ $recinto['display_name'] }}
                                            </div>
                                            <div class="text-xs {{ $selectedRecintoId == $recinto['id'] ? 'text-green-700' : 'text-zinc-600' }} mt-1">
                                                📏 {{ number_format($recinto['area_ha'], 2) }} ha
                                                @if($recinto['centroid'])
                                                    <br>📍 {{ number_format($recinto['centroid']['lat'], 4) }}°, {{ number_format($recinto['centroid']['lng'], 4) }}°
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </div>
                
                {{-- Caso 3: Más de 4 recintos - Dropdown --}}
                @else
                    <div class="mb-4 bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3">
                            <div class="flex-1">
                                <label class="block text-sm font-semibold text-zinc-800 mb-2">
                                    📍 Recinto a analizar
                                    <span class="text-xs font-normal text-zinc-600">
                                        (parcela con {{ count($availableRecintos) }} recintos)
                                    </span>
                                </label>
                                
                                <flux:select wire:model.live="selectedRecintoId" 
                                        >
                                    @foreach($availableRecintos as $recinto)
                                        <option value="{{ $recinto['id'] }}">
                                            {{ $recinto['display_name'] }} 
                                            ({{ number_format($recinto['area_ha'], 2) }} ha)
                                        </option>
                                    @endforeach
                                </flux:select>
                            </div>
                            
                            @php
                                $selectedRecinto = collect($availableRecintos)->firstWhere('id', $selectedRecintoId);
                            @endphp
                            
                            @if($selectedRecinto && $selectedRecinto['centroid'])
                                <div class="bg-white rounded-lg px-4 py-3 border border-blue-200 text-xs">
                                    <div class="font-semibold text-zinc-700 mb-1">Coordenadas:</div>
                                    <div class="text-zinc-600">
                                        📍 Lat: {{ number_format($selectedRecinto['centroid']['lat'], 6) }}°<br>
                                        📍 Lon: {{ number_format($selectedRecinto['centroid']['lng'], 6) }}°
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            @endif

            <!-- Satellite Tab -->
            @if($activeTab === 'satellite')
                {{-- Date Selector for Satellite Tab --}}
                <div class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div class="flex flex-col md:flex-row md:items-center gap-2">
                        <span class="text-sm font-medium text-zinc-700">📅 Fecha de análisis:</span>
                        @if(count($satelliteAvailableDates) > 0)
                            <flux:select wire:model.live="satelliteSelectedDate" 
                                    >
                                <option value="">Último dato disponible</option>
                                @foreach($satelliteAvailableDates as $date)
                                    <option value="{{ $date }}">{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</option>
                                @endforeach
                            </flux:select>
                        @else
                            <span class="text-sm text-amber-600 font-medium bg-amber-50 px-3 py-2 rounded-lg border border-amber-200">
                                ⚠️ Sin datos históricos - Carga datos para comenzar
                            </span>
                        @endif
                    </div>
                    
                    <div class="flex items-center gap-2 flex-wrap">
                        @if($satelliteSelectedDate && count($satelliteAvailableDates) > 0)
                            <button wire:click="$set('satelliteSelectedDate', '')" 
                                    class="text-sm text-blue-600 hover:text-blue-800 transition font-medium">
                                🔄 Ver último dato
                            </button>
                        @endif
                        
                        {{-- Botón para solicitar datos si no existen o están en cero --}}
                        @if(!$ndviData || ($ndviData->ndvi_mean ?? 0) == 0)
                            <button wire:click="requestDataForDate" 
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white text-sm font-medium rounded-lg transition-all shadow-md disabled:opacity-50">
                                <svg wire:loading.remove wire:target="requestDataForDate" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                <svg wire:loading wire:target="requestDataForDate" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <span wire:loading.remove wire:target="requestDataForDate">🛰️ Cargar Datos NASA</span>
                                <span wire:loading wire:target="requestDataForDate">Cargando...</span>
                            </button>
                        @endif
                    </div>
                </div>
                
                {{-- Alert when no data --}}
                @if(!$ndviData || ($ndviData->ndvi_mean ?? 0) == 0)
                    <div class="mb-6 bg-gradient-to-r from-yellow-50 to-amber-50 border-l-4 border-yellow-400 rounded-r-lg p-5 shadow-sm">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"/>
                                </svg>
                            </div>
                            <div class="ml-4 flex-1">
                                <h4 class="text-base font-semibold text-yellow-900 mb-2">
                                    📡 Sin datos satelitales para esta parcela
                                </h4>
                                <div class="text-sm text-yellow-800 space-y-2 mb-3">
                                    <p>Esta parcela <strong>{{ $selectedPlot->name }}</strong> no tiene datos de Remote Sensing cargados
                                    @if($satelliteSelectedDate)
                                        para la fecha <strong>{{ \Carbon\Carbon::parse($satelliteSelectedDate)->format('d/m/Y') }}</strong>
                                    @endif.</p>
                                    
                                    <p class="font-medium">🎯 ¿Qué hacer?</p>
                                    <ul class="list-disc list-inside space-y-1 ml-2">
                                        <li>Haz clic en <strong>"🛰️ Cargar Datos NASA"</strong> para obtener datos satelitales actuales</li>
                                        <li>Los datos se cargarán directamente desde NASA Earthdata (gratuito)</li>
                                        <li>Verás NDVI, humedad, temperatura y más indicadores en 30-60 segundos</li>
                                    </ul>
                                </div>
                                
                                {{-- Verificación de requisitos --}}
                                <div class="bg-white rounded-lg p-3 text-xs border border-yellow-200">
                                    <p class="font-semibold text-zinc-800 mb-2">📋 Verifica que:</p>
                                    <div class="space-y-1 text-zinc-700">
                                        <div class="flex items-center gap-2">
                                            @php
                                                $recintoCoords = $this->getSelectedRecintoCoordinates();
                                            @endphp
                                            @if($recintoCoords)
                                                <span class="text-green-600">✓</span>
                                                <span>El recinto tiene coordenadas (Lat: {{ number_format($recintoCoords['lat'], 4) }}°, Lon: {{ number_format($recintoCoords['lng'], 4) }}°)</span>
                                            @else
                                                <span class="text-red-600">✗</span>
                                                <span class="text-red-600 font-semibold">⚠️ El recinto seleccionado NO tiene geometría configurada</span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-blue-600">ℹ️</span>
                                            <span>Las credenciales NASA están configuradas en el servidor</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-blue-600">ℹ️</span>
                                            <span>La cola de trabajos está procesándose</span>
                                        </div>
                                    </div>
                                </div>
                                
                                @if($dataLoadError)
                                    <div class="mt-3 bg-red-50 border border-red-200 rounded-lg p-3">
                                        <p class="text-sm text-red-700">
                                            <strong>❌ Error:</strong> {{ $dataLoadError }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- NDVI Card -->
                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-5 border border-green-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold text-zinc-600">NDVI</span>
                            <span class="text-2xl">{{ $ndviData?->health_emoji ?? '❓' }}</span>
                        </div>
                        <div class="text-4xl font-bold text-green-700 mb-2">
                            {{ number_format($ndviData?->ndvi_mean ?? 0, 2) }}
                        </div>
                        <div class="w-full bg-zinc-200 rounded-full h-2.5 mb-2">
                            <div class="h-2.5 rounded-full" style="width: {{ $ndviData?->ndvi_percentage ?? 0 }}%; background: linear-gradient(90deg, #ef4444, #eab308, #22c55e);"></div>
                        </div>
                        <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full
                            @if($ndviData?->health_status === 'excellent') bg-green-100 text-green-800
                            @elseif($ndviData?->health_status === 'good') bg-emerald-100 text-emerald-800
                            @elseif($ndviData?->health_status === 'moderate') bg-yellow-100 text-yellow-800
                            @elseif($ndviData?->health_status === 'poor') bg-orange-100 text-orange-800
                            @else bg-red-100 text-red-800
                            @endif">
                            {{ $ndviData?->health_text ?? 'Sin datos' }}
                        </span>
                    </div>

                    <!-- NDWI Card -->
                    <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-lg p-5 border border-blue-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold text-zinc-600">NDWI (Agua)</span>
                            <span class="text-2xl">💧</span>
                        </div>
                        <div class="text-4xl font-bold text-blue-700 mb-2">
                            {{ number_format($ndviData?->ndwi_mean ?? 0, 2) }}
                        </div>
                        <p class="text-xs text-zinc-600">Contenido de agua en vegetación</p>
                    </div>

                    <!-- Year Comparison -->
                    <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-lg p-5 border border-amber-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold text-zinc-600">vs {{ now()->year - 1 }}</span>
                            <span class="text-2xl">📅</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="text-center">
                                <div class="text-xs text-zinc-500">{{ now()->year - 1 }}</div>
                                <div class="text-xl font-bold text-zinc-600">{{ number_format($lastYearNdvi ?? 0, 2) }}</div>
                            </div>
                            <span class="text-xl">→</span>
                            <div class="text-center">
                                <div class="text-xs text-zinc-500">{{ now()->year }}</div>
                                <div class="text-xl font-bold text-green-600">{{ number_format($ndviData?->ndvi_mean ?? 0, 2) }}</div>
                            </div>
                            <div class="px-2 py-1 rounded-full text-xs font-bold
                                @if($yearChange > 0) bg-green-100 text-green-700
                                @elseif($yearChange < 0) bg-red-100 text-red-700
                                @else bg-zinc-100 text-zinc-700
                                @endif">
                                @if($yearChange > 0)↑@elseif($yearChange < 0)↓@else=@endif
                                {{ number_format(abs(($yearChange ?? 0) * 100), 1) }}%
                            </div>
                        </div>
                    </div>
                </div>
                
                @if($ndviData && ($ndviData->ndvi_mean ?? 0) > 0)
                    <div class="mt-4 bg-zinc-50 rounded-lg p-3 text-sm text-zinc-600 flex gap-4">
                        <span>📡 NASA MODIS</span>
                        <span>📅 {{ $ndviData?->image_date?->format('d/m/Y') ?? 'N/A' }}</span>
                        <span>☁️ {{ $ndviData?->cloud_coverage ?? 0 }}% nubes</span>
                    </div>
                @endif
            @endif

            <!-- Spectral Bands Tab -->
            @if($activeTab === 'spectral')
                @livewire('viticulturist.remote-sensing.spectral-bands-card', ['plot' => $selectedPlot], key('spectral-'.$selectedPlot->id))
            @endif

            <!-- Official LAI Tab -->
            @if($activeTab === 'lai-official')
                @livewire('viticulturist.remote-sensing.official-lai-card', ['plot' => $selectedPlot], key('lai-official-'.$selectedPlot->id))
            @endif

            <!-- Thermal Tab -->
            @if($activeTab === 'thermal')
                @livewire('viticulturist.remote-sensing.thermal-stress-card', ['plot' => $selectedPlot], key('thermal-'.$selectedPlot->id))
            @endif

            <!-- Vigor Map Tab -->
            @if($activeTab === 'vigor-map')
                @livewire('viticulturist.remote-sensing.vigor-map-card', ['plot' => $selectedPlot], key('vigor-'.$selectedPlot->id))
            @endif

            <!-- SMAP Soil Tab -->
            @if($activeTab === 'smap-soil')
                @livewire('viticulturist.remote-sensing.smap-soil-card', ['plot' => $selectedPlot], key('smap-'.$selectedPlot->id))
            @endif

            <!-- Weather Tab -->
            @if($activeTab === 'weather')
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
                    <div class="bg-gradient-to-br from-orange-50 to-red-50 rounded-lg p-4 border border-orange-200 text-center">
                        <span class="text-2xl">🌡️</span>
                        <div class="text-2xl font-bold text-orange-600">{{ $weather['temperature'] ?? '--' }}°C</div>
                        <div class="text-xs text-zinc-500">{{ $weather['temperature_min'] ?? '--' }}° / {{ $weather['temperature_max'] ?? '--' }}°</div>
                    </div>
                    <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-lg p-4 border border-blue-200 text-center">
                        <span class="text-2xl">💧</span>
                        <div class="text-2xl font-bold text-blue-600">{{ $weather['humidity'] ?? '--' }}%</div>
                        <div class="text-xs text-zinc-500">Humedad</div>
                    </div>
                    <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-lg p-4 border border-indigo-200 text-center">
                        <span class="text-2xl">🌧️</span>
                        <div class="text-2xl font-bold text-indigo-600">{{ $weather['precipitation'] ?? 0 }}mm</div>
                        <div class="text-xs text-zinc-500">Precipitación</div>
                    </div>
                    <div class="bg-gradient-to-br from-teal-50 to-emerald-50 rounded-lg p-4 border border-teal-200 text-center">
                        <span class="text-2xl">💨</span>
                        <div class="text-2xl font-bold text-teal-600">{{ $weather['wind_speed'] ?? '--' }}km/h</div>
                        <div class="text-xs text-zinc-500">Viento</div>
                    </div>
                </div>
                
                <!-- 7-Day Forecast -->
                <h3 class="text-md font-semibold mb-3">📅 Pronóstico 7 días</h3>
                <div class="grid grid-cols-7 gap-2">
                    @foreach($forecast as $day)
                        <div class="text-center p-2 bg-gradient-to-b from-blue-50 to-indigo-50 rounded-lg border border-blue-100">
                            <div class="text-xs text-zinc-500">{{ \Carbon\Carbon::parse($day['date'])->locale('es')->isoFormat('ddd') }}</div>
                            <div class="text-xl my-1">{{ \App\Services\RemoteSensing\WeatherService::getWeatherIcon($day['weather_code'] ?? 0) }}</div>
                            <div class="text-xs font-bold">
                                <span class="text-red-500">{{ round($day['temp_max'] ?? 0) }}°</span>
                                <span class="text-blue-500">{{ round($day['temp_min'] ?? 0) }}°</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Soil Tab -->
            @if($activeTab === 'soil')
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-gradient-to-br from-amber-50 to-yellow-50 rounded-lg p-5 border border-amber-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold text-zinc-600">Humedad Suelo</span>
                            <span class="text-2xl">🌱</span>
                        </div>
                        <div class="text-4xl font-bold text-amber-700">{{ $soil['soil_moisture'] ?? '--' }}%</div>
                        <div class="w-full bg-zinc-200 rounded-full h-3 mt-2">
                            <div class="h-3 rounded-full bg-gradient-to-r from-amber-300 to-amber-600" style="width: {{ min(100, $soil['soil_moisture'] ?? 0) }}%"></div>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-orange-50 to-red-50 rounded-lg p-5 border border-orange-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold text-zinc-600">Temp. Suelo</span>
                            <span class="text-2xl">🌡️</span>
                        </div>
                        <div class="text-4xl font-bold text-orange-700">{{ $soil['soil_temperature'] ?? '--' }}°C</div>
                    </div>
                    <div class="bg-gradient-to-br {{ $waterStress['bg'] }} rounded-lg p-5 border border-green-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold text-zinc-600">Estrés Hídrico</span>
                            <span class="text-2xl">{{ $waterStress['emoji'] }}</span>
                        </div>
                        <div class="text-4xl font-bold {{ $waterStress['color'] }}">{{ $waterStress['text'] }}</div>
                    </div>
                </div>
            @endif

            <!-- Solar Tab -->
            @if($activeTab === 'solar')
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-gradient-to-br from-yellow-50 to-orange-50 rounded-lg p-5 border border-yellow-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold text-zinc-600">Radiación Solar</span>
                            <span class="text-2xl">☀️</span>
                        </div>
                        <div class="text-4xl font-bold text-yellow-600">{{ $solar['solar_radiation'] ?? '--' }}</div>
                        <p class="text-xs text-zinc-500 mt-1">MJ/m²</p>
                    </div>
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg p-5 border border-blue-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold text-zinc-600">ET0</span>
                            <span class="text-2xl">💦</span>
                        </div>
                        <div class="text-4xl font-bold text-blue-600">{{ $solar['et0'] ?? '--' }}</div>
                        <p class="text-xs text-zinc-500 mt-1">mm/día</p>
                    </div>
                    <div class="bg-gradient-to-br from-amber-50 to-yellow-50 rounded-lg p-5 border border-amber-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold text-zinc-600">Horas de Sol</span>
                            <span class="text-2xl">🌤️</span>
                        </div>
                        <div class="text-4xl font-bold text-amber-600">{{ round($solar['sunshine_hours'] ?? 0, 1) }}h</div>
                    </div>
                </div>
            @endif

            <!-- Irrigation Tab -->
            @if($activeTab === 'irrigation')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Water Balance -->
                    <div class="bg-gradient-to-br from-cyan-50 to-blue-50 rounded-lg p-5 border border-cyan-200">
                        <h3 class="font-semibold text-zinc-800 mb-4 flex items-center gap-2">
                            💧 Balance Hídrico Semanal
                        </h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-zinc-600">ET0 (evapotranspiración)</span>
                                <span class="font-bold text-blue-600">{{ $irrigationNeeds['et0'] }} mm/día</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-zinc-600">Kc (coef. cultivo vid)</span>
                                <span class="font-bold">{{ $irrigationNeeds['kc'] }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-zinc-600">ETc (necesidad cultivo)</span>
                                <span class="font-bold text-cyan-600">{{ $irrigationNeeds['etc'] }} mm/día</span>
                            </div>
                            <hr class="border-zinc-200">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-zinc-600">Necesidad semanal</span>
                                <span class="font-bold">{{ $irrigationNeeds['weekly_need_mm'] }} mm</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-zinc-600">Lluvia prevista</span>
                                <span class="font-bold text-indigo-600">-{{ $irrigationNeeds['expected_rain_mm'] }} mm</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-zinc-600">Reserva suelo</span>
                                <span class="font-bold text-amber-600">-{{ $irrigationNeeds['soil_reserve_mm'] }} mm</span>
                            </div>
                        </div>
                    </div>

                    <!-- Recommendation -->
                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-5 border border-green-200">
                        <h3 class="font-semibold text-zinc-800 mb-4 flex items-center gap-2">
                            🚿 Recomendación de Riego
                        </h3>
                        <div class="text-center py-4">
                            <div class="text-5xl mb-3">💧</div>
                            <div class="text-3xl font-bold {{ $irrigationNeeds['recommendation']['color'] }}">
                                {{ $irrigationNeeds['irrigation_need_mm'] }} mm
                            </div>
                            <div class="text-sm text-zinc-600 mt-1">Déficit hídrico esta semana</div>
                            <div class="mt-4 px-4 py-2 rounded-full font-bold {{ $irrigationNeeds['recommendation']['bg'] }} {{ $irrigationNeeds['recommendation']['color'] }}">
                                {{ $irrigationNeeds['recommendation']['text'] }}
                            </div>
                            @if($irrigationNeeds['liters_per_ha'] > 0)
                                <div class="mt-4 text-sm text-zinc-600">
                                    ≈ <strong>{{ number_format($irrigationNeeds['liters_per_ha']) }}</strong> litros/ha
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Harvest Tab (GDD) -->
            @if($activeTab === 'harvest')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Phenological Stage -->
                    <div class="bg-gradient-to-br from-pink-50 to-purple-50 rounded-lg p-5 border border-pink-200">
                        <h3 class="font-semibold text-zinc-800 mb-4 flex items-center gap-2">
                            🍇 Estado Fenológico
                        </h3>
                        <div class="text-center py-4">
                            <div class="text-6xl mb-3">
                                @switch($gdd['stage']['icon'])
                                    @case('sprout') 🌱 @break
                                    @case('flower') 🌸 @break
                                    @case('grape') 🍇 @break
                                    @case('green') 🟢 @break
                                    @case('purple') 🟣 @break
                                    @case('wine') 🍷 @break
                                    @default {{ $gdd['stage']['icon'] }}
                                @endswitch
                            </div>
                            <div class="text-2xl font-bold text-purple-700">{{ $gdd['stage']['name'] }}</div>
                            <div class="w-full bg-zinc-200 rounded-full h-4 mt-4">
                                <div class="h-4 rounded-full bg-gradient-to-r from-green-400 to-purple-500" 
                                     style="width: {{ $gdd['stage']['progress'] }}%"></div>
                            </div>
                            <div class="flex justify-between text-xs text-zinc-500 mt-1">
                                <span>Brotación</span>
                                <span>Vendimia</span>
                            </div>
                        </div>
                    </div>

                    <!-- GDD Stats -->
                    <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-lg p-5 border border-amber-200">
                        <h3 class="font-semibold text-zinc-800 mb-4 flex items-center gap-2">
                            🌡️ Grados-Día Acumulados (GDD)
                        </h3>
                        <div class="space-y-4">
                            <div class="bg-white rounded-lg p-3 shadow-sm">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-zinc-600">GDD Hoy</span>
                                    <span class="font-bold text-orange-600">+{{ $gdd['gdd_today'] }}°</span>
                                </div>
                            </div>
                            <div class="bg-white rounded-lg p-3 shadow-sm">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-zinc-600">GDD Próximos 7 días</span>
                                    <span class="font-bold text-amber-600">+{{ $gdd['gdd_week_forecast'] }}°</span>
                                </div>
                            </div>
                            <div class="bg-white rounded-lg p-3 shadow-sm">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-zinc-600">GDD Acumulado (desde 1 abril)</span>
                                    <span class="font-bold text-red-600">{{ $gdd['gdd_accumulated'] }}°</span>
                                </div>
                                <div class="w-full bg-zinc-200 rounded-full h-2 mt-2">
                                    <div class="h-2 rounded-full bg-gradient-to-r from-amber-400 to-red-500" 
                                         style="width: {{ min(100, ($gdd['gdd_accumulated'] / $gdd['gdd_target']) * 100) }}%"></div>
                                </div>
                                <div class="text-xs text-zinc-500 mt-1 text-right">{{ $gdd['gdd_accumulated'] }} / {{ $gdd['gdd_target'] }}° objetivo</div>
                            </div>
                            @if($gdd['estimated_harvest_date'])
                                <div class="bg-purple-100 rounded-lg p-4 text-center">
                                    <div class="text-sm text-purple-600">Fecha estimada de vendimia</div>
                                    <div class="text-xl font-bold text-purple-800">📅 {{ $gdd['estimated_harvest_date'] }}</div>
                                    <div class="text-xs text-purple-600">(en ~{{ $gdd['days_to_harvest'] }} días)</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Compare Tab -->
            @if($activeTab === 'compare')
                <div class="mb-4">
                    <label class="block text-sm font-medium text-zinc-700 mb-2">Selecciona parcela para comparar:</label>
                    <flux:select wire:model.live="comparePlotId" 
                            >
                        <option value="">-- Seleccionar --</option>
                        @foreach($plots as $plot)
                            @if($plot->id !== $selectedPlotId)
                                <option value="{{ $plot->id }}">{{ $plot->name }}</option>
                            @endif
                        @endforeach
                    </flux:select>
                </div>

                @if($comparePlot)
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Left: Current Plot -->
                        <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-4 border-2 border-green-300">
                            <h3 class="text-lg font-bold text-green-800 mb-3 flex items-center gap-2">
                                🌱 {{ $selectedPlot->name }}
                            </h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center py-2 border-b">
                                    <span class="text-sm text-zinc-600">NDVI</span>
                                    <span class="font-bold text-2xl text-green-600">{{ number_format($ndviData?->ndvi_mean ?? 0, 2) }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b">
                                    <span class="text-sm text-zinc-600">NDWI</span>
                                    <span class="font-bold text-blue-600">{{ number_format($ndviData?->ndwi_mean ?? 0, 2) }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b">
                                    <span class="text-sm text-zinc-600">Temperatura</span>
                                    <span class="font-bold text-orange-600">{{ $weather['temperature'] ?? '--' }}°C</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b">
                                    <span class="text-sm text-zinc-600">Humedad Suelo</span>
                                    <span class="font-bold text-amber-600">{{ $soil['soil_moisture'] ?? '--' }}%</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b">
                                    <span class="text-sm text-zinc-600">ET0</span>
                                    <span class="font-bold text-cyan-600">{{ $solar['et0'] ?? '--' }} mm</span>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-sm text-zinc-600">Estado</span>
                                    <span class="px-2 py-1 rounded text-sm font-bold
                                        @if($ndviData?->health_status === 'excellent') bg-green-100 text-green-800
                                        @elseif($ndviData?->health_status === 'good') bg-emerald-100 text-emerald-800
                                        @else bg-yellow-100 text-yellow-800
                                        @endif">
                                        {{ $ndviData?->health_text ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Compare Plot -->
                        <div class="bg-gradient-to-br from-violet-50 to-purple-50 rounded-lg p-4 border-2 border-violet-300">
                            <h3 class="text-lg font-bold text-violet-800 mb-3 flex items-center gap-2">
                                🌱 {{ $comparePlot->name }}
                            </h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center py-2 border-b">
                                    <span class="text-sm text-zinc-600">NDVI</span>
                                    <span class="font-bold text-2xl text-violet-600">{{ number_format($compareNdviData?->ndvi_mean ?? 0, 2) }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b">
                                    <span class="text-sm text-zinc-600">NDWI</span>
                                    <span class="font-bold text-blue-600">{{ number_format($compareNdviData?->ndwi_mean ?? 0, 2) }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b">
                                    <span class="text-sm text-zinc-600">Temperatura</span>
                                    <span class="font-bold text-orange-600">{{ $compareWeather['temperature'] ?? '--' }}°C</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b">
                                    <span class="text-sm text-zinc-600">Humedad Suelo</span>
                                    <span class="font-bold text-amber-600">{{ $compareSoil['soil_moisture'] ?? '--' }}%</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b">
                                    <span class="text-sm text-zinc-600">ET0</span>
                                    <span class="font-bold text-cyan-600">{{ $compareSolar['et0'] ?? '--' }} mm</span>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-sm text-zinc-600">Estado</span>
                                    <span class="px-2 py-1 rounded text-sm font-bold
                                        @if($compareNdviData?->health_status === 'excellent') bg-green-100 text-green-800
                                        @elseif($compareNdviData?->health_status === 'good') bg-emerald-100 text-emerald-800
                                        @else bg-yellow-100 text-yellow-800
                                        @endif">
                                        {{ $compareNdviData?->health_text ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Difference Summary -->
                    @php
                        $ndviDiff = ($ndviData?->ndvi_mean ?? 0) - ($compareNdviData?->ndvi_mean ?? 0);
                    @endphp
                    <div class="mt-4 bg-zinc-100 rounded-lg p-4 text-center">
                        <span class="text-sm text-zinc-600">Diferencia NDVI:</span>
                        <span class="ml-2 font-bold text-lg {{ $ndviDiff > 0 ? 'text-green-600' : ($ndviDiff < 0 ? 'text-red-600' : 'text-zinc-600') }}">
                            {{ $ndviDiff > 0 ? '+' : '' }}{{ number_format($ndviDiff, 3) }}
                        </span>
                        <span class="ml-2 text-sm {{ $ndviDiff > 0 ? 'text-green-600' : ($ndviDiff < 0 ? 'text-red-600' : 'text-zinc-600') }}">
                            @if($ndviDiff > 0.05) ({{ $selectedPlot->name }} mejor)
                            @elseif($ndviDiff < -0.05) ({{ $comparePlot->name }} mejor)
                            @else (similares)
                            @endif
                        </span>
                    </div>
                @else
                    <div class="text-center py-12 bg-zinc-50 rounded-lg">
                        <span class="text-4xl">⚖️</span>
                        <p class="text-zinc-600 mt-4">Selecciona una parcela para comparar con <strong>{{ $selectedPlot->name }}</strong></p>
                    </div>
                @endif
            @endif

            <!-- History Tab -->
            @if($activeTab === 'history')
                <div wire:key="history-tab-content-{{ $selectedPlotId ?? 'none' }}-{{ $historyPeriod }}">
                {{-- Period Selector --}}
                <div class="mb-6 bg-white rounded-lg border border-zinc-200 p-4">
                    <div class="flex flex-col md:flex-row gap-4">
                        {{-- Predefined Periods --}}
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-zinc-700 mb-2">
                                📅 Período de análisis
                            </label>
                            <flux:select wire:model.live="historyPeriod" 
                                    >
                                <option value="7_days">Últimos 7 días</option>
                                <option value="30_days">Últimos 30 días</option>
                                <option value="90_days">Últimos 90 días (por defecto)</option>
                                <option value="current_season">Temporada actual (Abril - Hoy)</option>
                                <option value="last_season">Temporada anterior (Abril - Octubre)</option>
                                <option value="1_year">Último año</option>
                                <option value="custom">🎯 Personalizado</option>
                            </flux:select>
                        </div>

                        {{-- Custom Date Range (only visible when custom is selected) --}}
                        @if($historyPeriod === 'custom')
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-zinc-700 mb-2">
                                    Rango personalizado
                                </label>
                                <div class="flex gap-2 items-end">
                                    <div class="flex-1">
                                        <input type="date" 
                                               wire:model="customStartDate"
                                               max="{{ now()->format('Y-m-d') }}"
                                               class="w-full px-3 py-2 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm"
                                               placeholder="Desde">
                                    </div>
                                    <span class="text-zinc-500">→</span>
                                    <div class="flex-1">
                                        <input type="date" 
                                               wire:model="customEndDate"
                                               max="{{ now()->format('Y-m-d') }}"
                                               class="w-full px-3 py-2 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm"
                                               placeholder="Hasta">
                                    </div>
                                    <button wire:click="applyCustomDateRange"
                                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors text-sm font-medium">
                                        Aplicar
                                    </button>
                                </div>
                                <p class="text-xs text-zinc-500 mt-1">
                                    Máximo: 2 años de rango
                                </p>
                            </div>
                        @endif
                    </div>

                    {{-- Period Info --}}
                    <div class="mt-3 flex items-center gap-4 text-sm text-zinc-600">
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Mostrando: <strong>{{ $historyDays }} días</strong>
                        </span>
                        @if(count($historicalData) > 0)
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                <strong>{{ count($historicalData) }}</strong> registros
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Historical Chart --}}
                <h3 class="text-md font-semibold mb-3">📈 Evolución NDVI</h3>
                @if(count($historicalData) > 0)
                    <div class="bg-white rounded-lg border border-zinc-200 p-4 mb-4">
                        <div class="h-64 flex items-end justify-between gap-1 bg-zinc-50 rounded-lg p-4">
                            @foreach($historicalData as $index => $data)
                                @php
                                    $height = ($data['ndvi'] + 1) / 2 * 100;
                                    $color = match($data['health_status'] ?? 'moderate') {
                                        'excellent' => 'bg-green-600',
                                        'good' => 'bg-green-500',
                                        'moderate' => 'bg-yellow-500',
                                        'poor' => 'bg-orange-500',
                                        'critical' => 'bg-red-500',
                                        default => 'bg-zinc-400',
                                    };
                                @endphp
                                <div class="flex-1 flex flex-col items-center group relative">
                                    {{-- Tooltip --}}
                                    <div class="absolute bottom-full mb-2 hidden group-hover:block bg-zinc-900 text-white text-xs rounded py-2 px-3 z-10 whitespace-nowrap">
                                        <div class="font-semibold">{{ $data['fullDate'] }}</div>
                                        <div>NDVI: {{ number_format($data['ndvi'], 3) }}</div>
                                        <div class="text-zinc-300 capitalize">{{ $data['health_status'] ?? 'N/A' }}</div>
                                    </div>
                                    {{-- Bar --}}
                                    <div class="w-full {{ $color }} rounded-t transition-all hover:opacity-80 cursor-pointer" 
                                         style="height: {{ max(10, $height) }}%;"></div>
                                </div>
                            @endforeach
                        </div>
                        
                        {{-- X-axis labels --}}
                        <div class="flex justify-between text-xs text-zinc-500 mt-2 px-2">
                            @if(count($historicalData) > 0)
                                <span>← {{ $historicalData[count($historicalData) - 1]['fullDate'] ?? '' }}</span>
                                <span>{{ $historicalData[0]['fullDate'] ?? '' }} →</span>
                            @endif
                        </div>
                    </div>

                    {{-- Statistics Summary --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
                        @php
                            $ndviValues = array_column($historicalData, 'ndvi');
                            $avgNdvi = count($ndviValues) > 0 ? array_sum($ndviValues) / count($ndviValues) : 0;
                            $maxNdvi = count($ndviValues) > 0 ? max($ndviValues) : 0;
                            $minNdvi = count($ndviValues) > 0 ? min($ndviValues) : 0;
                            $stdDev = count($ndviValues) > 1 ? sqrt(array_sum(array_map(fn($x) => pow($x - $avgNdvi, 2), $ndviValues)) / count($ndviValues)) : 0;
                        @endphp
                        
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-center">
                            <div class="text-xs text-blue-600 mb-1">Promedio</div>
                            <div class="text-2xl font-bold text-blue-900">{{ number_format($avgNdvi, 3) }}</div>
                        </div>
                        
                        <div class="bg-green-50 border border-green-200 rounded-lg p-3 text-center">
                            <div class="text-xs text-green-600 mb-1">Máximo</div>
                            <div class="text-2xl font-bold text-green-900">{{ number_format($maxNdvi, 3) }}</div>
                        </div>
                        
                        <div class="bg-orange-50 border border-orange-200 rounded-lg p-3 text-center">
                            <div class="text-xs text-orange-600 mb-1">Mínimo</div>
                            <div class="text-2xl font-bold text-orange-900">{{ number_format($minNdvi, 3) }}</div>
                        </div>
                        
                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-3 text-center">
                            <div class="text-xs text-purple-600 mb-1">Variabilidad</div>
                            <div class="text-2xl font-bold text-purple-900">{{ number_format($stdDev, 3) }}</div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex flex-wrap gap-3 mb-6">
                        <button wire:click="exportCSV" 
                                class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors text-sm font-medium">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Exportar CSV
                        </button>

                        <button wire:click="exportPDF" 
                                class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors text-sm font-medium">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            Exportar PDF
                        </button>

                        <button wire:click="toggleComparison" 
                                class="inline-flex items-center px-4 py-2 {{ $showComparison ? 'bg-purple-600 hover:bg-purple-700' : 'bg-zinc-600 hover:bg-zinc-700' }} text-white rounded-lg transition-colors text-sm font-medium">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            {{ $showComparison ? 'Ocultar' : 'Comparar' }} Períodos
                        </button>
                    </div>

                    {{-- Period Comparison --}}
                    @if($showComparison)
                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-6">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-lg font-semibold text-purple-900">📊 Comparación de Períodos</h4>
                                <flux:select wire:model.live="comparisonPeriod" 
                                        >
                                    <option value="last_year">Mismo período año anterior</option>
                                    <option value="last_season">Temporada anterior</option>
                                    <option value="same_month_last_year">Mismo mes año anterior</option>
                                </flux:select>
                            </div>

                            @if(count($comparisonData) > 0)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    {{-- Current Period --}}
                                    <div class="bg-white rounded-lg p-4">
                                        <h5 class="text-sm font-semibold text-zinc-700 mb-3">Período Actual</h5>
                                        <div class="space-y-2 text-sm">
                                            <div class="flex justify-between">
                                                <span class="text-zinc-600">NDVI Promedio:</span>
                                                <span class="font-bold text-green-600">{{ number_format($avgNdvi, 3) }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-zinc-600">Máximo:</span>
                                                <span class="font-semibold">{{ number_format($maxNdvi, 3) }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-zinc-600">Registros:</span>
                                                <span class="font-semibold">{{ count($historicalData) }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Comparison Period --}}
                                    <div class="bg-white rounded-lg p-4">
                                        <h5 class="text-sm font-semibold text-zinc-700 mb-3">Período Comparación</h5>
                                        @php
                                            $compNdviValues = array_column($comparisonData, 'ndvi');
                                            $compAvgNdvi = count($compNdviValues) > 0 ? array_sum($compNdviValues) / count($compNdviValues) : 0;
                                            $compMaxNdvi = count($compNdviValues) > 0 ? max($compNdviValues) : 0;
                                        @endphp
                                        <div class="space-y-2 text-sm">
                                            <div class="flex justify-between">
                                                <span class="text-zinc-600">NDVI Promedio:</span>
                                                <span class="font-bold text-blue-600">{{ number_format($compAvgNdvi, 3) }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-zinc-600">Máximo:</span>
                                                <span class="font-semibold">{{ number_format($compMaxNdvi, 3) }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-zinc-600">Registros:</span>
                                                <span class="font-semibold">{{ count($comparisonData) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Difference Analysis --}}
                                @php
                                    $avgDiff = $avgNdvi - $compAvgNdvi;
                                    $percentChange = $compAvgNdvi > 0 ? ($avgDiff / $compAvgNdvi) * 100 : 0;
                                @endphp
                                <div class="mt-4 bg-white rounded-lg p-4 text-center">
                                    <div class="text-sm text-zinc-600 mb-1">Diferencia Promedio</div>
                                    <div class="text-3xl font-bold {{ $avgDiff > 0 ? 'text-green-600' : ($avgDiff < 0 ? 'text-red-600' : 'text-zinc-600') }}">
                                        {{ $avgDiff > 0 ? '+' : '' }}{{ number_format($avgDiff, 3) }}
                                        <span class="text-lg">({{ $percentChange > 0 ? '+' : '' }}{{ number_format($percentChange, 1) }}%)</span>
                                    </div>
                                    <div class="text-sm mt-2">
                                        @if($avgDiff > 0.05)
                                            <span class="text-green-600">✅ Mejora significativa respecto al período anterior</span>
                                        @elseif($avgDiff < -0.05)
                                            <span class="text-red-600">⚠️ Reducción respecto al período anterior</span>
                                        @else
                                            <span class="text-zinc-600">➡️ Valores similares al período anterior</span>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <p class="text-center text-purple-700 py-4">No hay datos disponibles para el período de comparación</p>
                            @endif
                        </div>
                    @endif

                    {{-- Alerts Section --}}
                    @if(count($periodAlerts) > 0)
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                            <h4 class="text-lg font-semibold text-yellow-900 mb-3">
                                ⚠️ Alertas Detectadas ({{ count($periodAlerts) }})
                            </h4>
                            
                            <div class="mb-3">
                                <label class="text-sm text-zinc-700 font-medium">Umbral NDVI:</label>
                                <input type="range" 
                                       wire:model.live="ndviThreshold"
                                       min="0" 
                                       max="1" 
                                       step="0.05"
                                       class="w-full h-2 bg-zinc-200 rounded-lg appearance-none cursor-pointer">
                                <div class="flex justify-between text-xs text-zinc-600 mt-1">
                                    <span>0.0</span>
                                    <span class="font-bold text-yellow-700">{{ number_format($ndviThreshold, 2) }}</span>
                                    <span>1.0</span>
                                </div>
                            </div>

                            <div class="space-y-2 max-h-60 overflow-y-auto">
                                @foreach($periodAlerts as $alert)
                                    <div class="flex items-start gap-3 p-3 bg-white rounded-lg {{ $alert['severity'] === 'critical' ? 'border-l-4 border-red-500' : 'border-l-4 border-yellow-500' }}">
                                        <span class="text-2xl">{{ $alert['severity'] === 'critical' ? '🚨' : '⚠️' }}</span>
                                        <div class="flex-1">
                                            <div class="text-sm font-semibold text-zinc-900">{{ $alert['message'] }}</div>
                                            <div class="text-xs text-zinc-600 mt-1">Tipo: {{ $alert['type'] }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Trend Prediction --}}
                    @if(!empty($trendPrediction))
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h4 class="text-lg font-semibold text-blue-900 mb-3">
                                🔮 Predicción de Tendencia (próximos 7 días)
                            </h4>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div class="bg-white rounded-lg p-3 text-center">
                                    <div class="text-xs text-zinc-600 mb-1">Tendencia</div>
                                    <div class="text-2xl font-bold {{ $trendPrediction['trend'] === 'improving' ? 'text-green-600' : ($trendPrediction['trend'] === 'declining' ? 'text-red-600' : 'text-zinc-600') }}">
                                        @if($trendPrediction['trend'] === 'improving')
                                            ↗️ Mejorando
                                        @elseif($trendPrediction['trend'] === 'declining')
                                            ↘️ Declinando
                                        @else
                                            → Estable
                                        @endif
                                    </div>
                                </div>

                                <div class="bg-white rounded-lg p-3 text-center">
                                    <div class="text-xs text-zinc-600 mb-1">Pendiente</div>
                                    <div class="text-2xl font-bold text-blue-900">
                                        {{ number_format($trendPrediction['slope'], 4) }}
                                    </div>
                                </div>

                                <div class="bg-white rounded-lg p-3 text-center">
                                    <div class="text-xs text-zinc-600 mb-1">Confianza (R²)</div>
                                    <div class="text-2xl font-bold text-purple-900">
                                        {{ number_format($trendPrediction['confidence'] * 100, 1) }}%
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white rounded-lg p-4">
                                <h5 class="text-sm font-semibold text-zinc-700 mb-3">Valores Predichos:</h5>
                                <div class="grid grid-cols-7 gap-2">
                                    @foreach($trendPrediction['predictions'] as $pred)
                                        <div class="text-center p-2 bg-blue-50 rounded">
                                            <div class="text-xs text-zinc-600">+{{ $pred['day'] }}d</div>
                                            <div class="text-sm font-bold text-blue-900">{{ number_format($pred['ndvi'], 3) }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mt-3 text-xs text-blue-700 bg-white rounded p-2">
                                <strong>Nota:</strong> Las predicciones se basan en regresión lineal de los datos históricos.
                                Confianza alta (>80%) indica patrón consistente. Úsalo solo como guía orientativa.
                            </div>
                        </div>
                    @endif
                @else
                    <div class="text-center py-12 bg-zinc-50 rounded-lg border border-zinc-200">
                        <svg class="w-16 h-16 mx-auto mb-4 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <p class="text-zinc-600 font-medium mb-1">No hay datos históricos</p>
                        <p class="text-sm text-zinc-500">Para el período seleccionado</p>
                    </div>
                @endif
                </div>{{-- cierre wire:key history-tab --}}
            @endif
        </div>

        <!-- Footer Info -->
        <div class="mt-4 bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center gap-2 mb-2">
                <span>🌟</span>
                <span class="font-semibold text-zinc-900">Teledetección NASA - Nivel Profesional</span>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs text-zinc-700">
                <div>🛰️ <strong>VIIRS</strong> NDVI (375m)</div>
                <div>🌈 <strong>Bandas</strong> Espectrales</div>
                <div>🌿 <strong>LAI</strong> Oficial MODIS</div>
                <div>🌡️ <strong>LST</strong> Temperatura</div>
                <div>🛰️ <strong>SMAP</strong> Humedad Suelo</div>
                <div>💧 <strong>ET</strong> NASA Oficial</div>
                <div>🗺️ <strong>Area</strong> Request</div>
                <div>🌦️ <strong>Open-Meteo</strong> Weather</div>
            </div>
            <div class="mt-2 text-xs text-blue-700">
                <strong>✓ 100% Gratuito</strong> • 7 Productos NASA • Datos Reales (no modelos)
            </div>
        </div>
    @else
        <div class="text-center py-16 bg-zinc-50 rounded-lg" wire:key="empty-state">
            <span class="text-6xl">🛰️</span>
            <p class="text-zinc-600 mt-4">Selecciona una parcela para ver el análisis</p>
        </div>
    @endif
    </div>{{-- cierre wire:key dashboard-body --}}
</div>
