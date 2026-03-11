<div class="min-h-screen bg-gradient-to-br from-zinc-50 to-blue-50 relative">
    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-fade-in-up {
            animation: fadeInUp 0.5s ease-out forwards;
        }
        .delay-100 { animation-delay: 0.1s; opacity: 0; }
        .delay-200 { animation-delay: 0.2s; opacity: 0; }
        .delay-300 { animation-delay: 0.3s; opacity: 0; }
        .delay-400 { animation-delay: 0.4s; opacity: 0; }
        .delay-500 { animation-delay: 0.5s; opacity: 0; }
        .delay-600 { animation-delay: 0.6s; opacity: 0; }
    </style>

    {{-- Overlay bloqueante mientras Livewire ejecuta generateData --}}
    <div wire:loading wire:target="generateData"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md mx-4 text-center">
            <svg class="animate-spin h-16 w-16 text-green-600 mx-auto mb-6" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <h3 class="text-xl font-bold text-zinc-900 mb-2">🛰️ Obteniendo datos del satélite</h3>
            <p class="text-zinc-600">Consultando NASA... unos segundos.</p>
        </div>
    </div>
    
    <div class="container mx-auto px-4 py-6">
        {{-- Header --}}
        <div class="mb-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-4xl font-bold text-zinc-900 flex items-center gap-3">
                        🛰️ Dashboard Teledetección
                    </h1>
                    <p class="text-zinc-600 mt-2">Vista ejecutiva de salud y estado de tus parcelas</p>
                </div>
                
                <div class="flex items-center gap-3">
                    {{-- Recinto Selector (parcela + SIGPAC) --}}
                    <flux:select wire:model.live="selectedRecintoId">
                        @foreach($recintos as $recinto)
                            <option value="{{ $recinto['id'] }}">{{ $recinto['display_name'] }}</option>
                        @endforeach
                    </flux:select>
                    
                    <button wire:click="refreshData" 
                            wire:loading.attr="disabled"
                            class="px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-lg transition-all disabled:opacity-50 flex items-center gap-2">
                        <svg wire:loading.remove wire:target="refreshData" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <svg wire:loading wire:target="refreshData" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Actualizar
                    </button>
                </div>
            </div>
            
            @if($selectedPlot && !empty($summary))
                <div class="mt-4 flex flex-col md:flex-row items-start md:items-center justify-between gap-3">
                    <div class="flex items-center gap-4 text-sm text-zinc-600">
                        <span class="flex items-center gap-2">
                            <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                            Última actualización: {{ $summary['last_update'] }}
                        </span>
                        <span>•</span>
                        <span>Satélite: {{ $summary['satellite'] }}</span>
                        @if($selectedPlot->area)
                            <span>•</span>
                            <span class="flex items-center gap-1">
                                📐 {{ number_format($selectedPlot->area, 2) }} ha
                            </span>
                        @endif
                    </div>
                    
                    <a href="{{ route('remote-sensing.report.plot', $selectedPlot) }}" 
                       class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-semibold shadow-lg transition-all text-sm">
                        📄 Descargar PDF
                    </a>
                </div>
            @endif
        </div>

        @if($loading)
            <div class="flex items-center justify-center py-24">
                <div class="text-center">
                    <svg class="animate-spin h-16 w-16 text-blue-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <p class="text-zinc-600 text-lg">Cargando datos...</p>
                </div>
            </div>
        @elseif($selectedPlot && !empty($summary) && isset($summary['vigor']['status']) && $summary['vigor']['status'] !== 'no_data')
            {{-- Main Dashboard Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                {{-- Card 1: Vigor --}}
                <div class="bg-white rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-300 overflow-hidden group animate-fade-in-up delay-100">
                    <div class="bg-gradient-to-br from-{{ $summary['vigor']['color'] }}-50 to-{{ $summary['vigor']['color'] }}-100 p-6 border-b-4 border-{{ $summary['vigor']['color'] }}-400">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-lg font-bold text-zinc-900">🌱 VIGOR VEGETATIVO</h3>
                            <span class="text-4xl">{{ $summary['vigor']['icon'] }}</span>
                        </div>
                        <div class="text-{{ $summary['vigor']['color'] }}-700 font-semibold text-sm">{{ $summary['vigor']['label'] }}</div>
                    </div>
                    
                    <div class="p-6">
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-zinc-600 text-sm flex items-center gap-1">
                                    NDVI
                                    <span class="cursor-help text-zinc-400" title="Índice de Vegetación: 0-1, valores más altos indican mayor vigor vegetativo">ℹ️</span>
                                </span>
                                <span class="text-2xl font-bold text-{{ $summary['vigor']['color'] }}-700">{{ number_format($summary['vigor']['ndvi'], 2) }}</span>
                            </div>
                            
                            @if($summary['vigor']['gndvi'])
                                <div class="flex justify-between items-center">
                                    <span class="text-zinc-600 text-sm flex items-center gap-1">
                                        GNDVI
                                        <span class="cursor-help text-zinc-400" title="Indicador de Nitrógeno: detecta deficiencias nutricionales">ℹ️</span>
                                    </span>
                                    <span class="text-xl font-bold text-{{ $summary['vigor']['color'] }}-700">{{ number_format($summary['vigor']['gndvi'], 2) }}</span>
                                </div>
                            @endif
                            
                            @if($summary['vigor']['lai'])
                                <div class="flex justify-between items-center">
                                    <span class="text-zinc-600 text-sm flex items-center gap-1">
                                        LAI
                                        <span class="cursor-help text-zinc-400" title="Índice de Área Foliar: superficie de hojas por unidad de suelo">ℹ️</span>
                                    </span>
                                    <span class="text-xl font-bold text-{{ $summary['vigor']['color'] }}-700">{{ number_format($summary['vigor']['lai'], 2) }}</span>
                                </div>
                            @endif
                        </div>
                        
                        <a href="{{ $summary['vigor']['detail_route'] }}" 
                           class="mt-6 w-full bg-{{ $summary['vigor']['color'] }}-100 hover:bg-{{ $summary['vigor']['color'] }}-200 text-{{ $summary['vigor']['color'] }}-800 py-3 px-4 rounded-lg font-semibold transition-all flex items-center justify-center gap-2 group-hover:scale-105">
                            <span>📊 Ver Análisis Completo</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- Card 2: Agua --}}
                <div class="bg-white rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-300 overflow-hidden group animate-fade-in-up delay-200">
                    <div class="bg-gradient-to-br from-{{ $summary['water']['color'] }}-50 to-{{ $summary['water']['color'] }}-100 p-6 border-b-4 border-{{ $summary['water']['color'] }}-400">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-lg font-bold text-zinc-900">💧 ESTADO HÍDRICO</h3>
                            <span class="text-4xl">{{ $summary['water']['icon'] }}</span>
                        </div>
                        <div class="text-{{ $summary['water']['color'] }}-700 font-semibold text-sm">{{ $summary['water']['label'] }}</div>
                    </div>
                    
                    <div class="p-6">
                        <div class="space-y-3">
                            @if(isset($summary['water']['cwsi']))
                                <div class="flex justify-between items-center">
                                    <span class="text-zinc-600 text-sm flex items-center gap-1">
                                        CWSI
                                        <span class="cursor-help text-zinc-400" title="Índice de Estrés Hídrico: 0-1, valores bajos son mejores">ℹ️</span>
                                    </span>
                                    <span class="text-2xl font-bold text-{{ $summary['water']['color'] }}-700">{{ number_format($summary['water']['cwsi'], 2) }}</span>
                                </div>
                            @endif
                            
                            @if(isset($summary['water']['soil_moisture']))
                                <div class="flex justify-between items-center">
                                    <span class="text-zinc-600 text-sm flex items-center gap-1">
                                        Humedad Suelo
                                        <span class="cursor-help text-zinc-400" title="Porcentaje de humedad en el suelo">ℹ️</span>
                                    </span>
                                    <span class="text-xl font-bold text-{{ $summary['water']['color'] }}-700">{{ number_format($summary['water']['soil_moisture'], 1) }}%</span>
                                </div>
                            @endif
                        </div>
                        
                        <a href="{{ $summary['water']['detail_route'] }}" 
                           class="mt-6 w-full bg-{{ $summary['water']['color'] }}-100 hover:bg-{{ $summary['water']['color'] }}-200 text-{{ $summary['water']['color'] }}-800 py-3 px-4 rounded-lg font-semibold transition-all flex items-center justify-center gap-2 group-hover:scale-105">
                            <span>📊 Ver Análisis Completo</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- Card 3: Temperatura --}}
                <div class="bg-white rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-300 overflow-hidden group animate-fade-in-up delay-300">
                    <div class="bg-gradient-to-br from-{{ $summary['temperature']['color'] }}-50 to-{{ $summary['temperature']['color'] }}-100 p-6 border-b-4 border-{{ $summary['temperature']['color'] }}-400">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-lg font-bold text-zinc-900">🌡️ TEMPERATURA</h3>
                            <span class="text-4xl">{{ $summary['temperature']['icon'] }}</span>
                        </div>
                        <div class="text-{{ $summary['temperature']['color'] }}-700 font-semibold text-sm">{{ $summary['temperature']['label'] }}</div>
                    </div>
                    
                    <div class="p-6">
                        <div class="space-y-3">
                            @if(isset($summary['temperature']['lst_day']))
                                <div class="flex justify-between items-center">
                                    <span class="text-zinc-600 text-sm">LST Día</span>
                                    <span class="text-2xl font-bold text-{{ $summary['temperature']['color'] }}-700">{{ number_format($summary['temperature']['lst_day'], 1) }}°C</span>
                                </div>
                            @endif
                            
                            @if(isset($summary['temperature']['lst_night']))
                                <div class="flex justify-between items-center">
                                    <span class="text-zinc-600 text-sm">LST Noche</span>
                                    <span class="text-xl font-bold text-{{ $summary['temperature']['color'] }}-700">{{ number_format($summary['temperature']['lst_night'], 1) }}°C</span>
                                </div>
                            @endif
                            
                            @if(isset($summary['temperature']['lst_diff']))
                                <div class="flex justify-between items-center">
                                    <span class="text-zinc-600 text-sm">Amplitud</span>
                                    <span class="text-xl font-bold text-{{ $summary['temperature']['color'] }}-700">{{ number_format($summary['temperature']['lst_diff'], 1) }}°C</span>
                                </div>
                            @endif
                        </div>
                        
                        <a href="{{ $summary['temperature']['detail_route'] }}" 
                           class="mt-6 w-full bg-{{ $summary['temperature']['color'] }}-100 hover:bg-{{ $summary['temperature']['color'] }}-200 text-{{ $summary['temperature']['color'] }}-800 py-3 px-4 rounded-lg font-semibold transition-all flex items-center justify-center gap-2 group-hover:scale-105">
                            <span>📊 Ver Análisis Completo</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- Card 4: Cosecha --}}
                <div class="bg-white rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-300 overflow-hidden group animate-fade-in-up delay-400">
                    <div class="bg-gradient-to-br from-{{ $summary['harvest']['color'] }}-50 to-{{ $summary['harvest']['color'] }}-100 p-6 border-b-4 border-{{ $summary['harvest']['color'] }}-400">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-lg font-bold text-zinc-900">🍇 RENDIMIENTO</h3>
                            <span class="text-4xl">{{ $summary['harvest']['icon'] }}</span>
                        </div>
                        <div class="text-{{ $summary['harvest']['color'] }}-700 font-semibold text-sm">Predicción</div>
                    </div>
                    
                    <div class="p-6">
                        <div class="space-y-3">
                            @if(isset($summary['harvest']['yield_per_ha']))
                                <div class="flex justify-between items-center">
                                    <span class="text-zinc-600 text-sm">Por Hectárea</span>
                                    <span class="text-2xl font-bold text-{{ $summary['harvest']['color'] }}-700">{{ number_format($summary['harvest']['yield_per_ha'], 1) }} t/ha</span>
                                </div>
                            @endif
                            
                            @if(isset($summary['harvest']['total_yield']))
                                <div class="flex justify-between items-center">
                                    <span class="text-zinc-600 text-sm">Total</span>
                                    <span class="text-xl font-bold text-{{ $summary['harvest']['color'] }}-700">{{ number_format($summary['harvest']['total_yield'] * 1000, 0) }} kg</span>
                                </div>
                            @endif
                            
                            @if(isset($summary['harvest']['confidence_label']))
                                <div class="flex justify-between items-center">
                                    <span class="text-zinc-600 text-sm">Confianza</span>
                                    <span class="text-sm font-semibold text-{{ $summary['harvest']['color'] }}-700">{{ $summary['harvest']['confidence_label'] }}</span>
                                </div>
                            @endif
                        </div>
                        
                        <a href="{{ $summary['harvest']['detail_route'] }}" 
                           class="mt-6 w-full bg-{{ $summary['harvest']['color'] }}-100 hover:bg-{{ $summary['harvest']['color'] }}-200 text-{{ $summary['harvest']['color'] }}-800 py-3 px-4 rounded-lg font-semibold transition-all flex items-center justify-center gap-2 group-hover:scale-105">
                            <span>📈 Ver Pronóstico</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- Card 5: Nutrición --}}
                <div class="bg-white rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-300 overflow-hidden group animate-fade-in-up delay-500">
                    <div class="bg-gradient-to-br from-{{ $summary['nutrition']['color'] }}-50 to-{{ $summary['nutrition']['color'] }}-100 p-6 border-b-4 border-{{ $summary['nutrition']['color'] }}-400">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-lg font-bold text-zinc-900">🌈 NUTRICIÓN</h3>
                            <span class="text-4xl">{{ $summary['nutrition']['icon'] }}</span>
                        </div>
                        <div class="text-{{ $summary['nutrition']['color'] }}-700 font-semibold text-sm">{{ $summary['nutrition']['label'] }}</div>
                    </div>
                    
                    <div class="p-6">
                        <div class="space-y-3">
                            @if(isset($summary['nutrition']['gndvi']))
                                <div class="flex justify-between items-center">
                                    <span class="text-zinc-600 text-sm">GNDVI (N)</span>
                                    <span class="text-2xl font-bold text-{{ $summary['nutrition']['color'] }}-700">{{ number_format($summary['nutrition']['gndvi'], 2) }}</span>
                                </div>
                            @endif
                            
                            @if(isset($summary['nutrition']['chlorophyll']))
                                <div class="flex justify-between items-center">
                                    <span class="text-zinc-600 text-sm">Clorofila</span>
                                    <span class="text-xl font-bold text-{{ $summary['nutrition']['color'] }}-700">{{ number_format($summary['nutrition']['chlorophyll'], 0) }}</span>
                                </div>
                            @endif
                        </div>
                        
                        <a href="{{ $summary['nutrition']['detail_route'] }}" 
                           class="mt-6 w-full bg-{{ $summary['nutrition']['color'] }}-100 hover:bg-{{ $summary['nutrition']['color'] }}-200 text-{{ $summary['nutrition']['color'] }}-800 py-3 px-4 rounded-lg font-semibold transition-all flex items-center justify-center gap-2 group-hover:scale-105">
                            <span>📊 Ver Análisis</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- Card 6: Alertas --}}
                <div class="bg-white rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-300 overflow-hidden group animate-fade-in-up delay-600">
                    <div class="bg-gradient-to-br from-{{ $summary['alerts']['color'] }}-50 to-{{ $summary['alerts']['color'] }}-100 p-6 border-b-4 border-{{ $summary['alerts']['color'] }}-400">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-lg font-bold text-zinc-900">⚠️ ALERTAS</h3>
                            <span class="text-4xl">{{ $summary['alerts']['icon'] }}</span>
                        </div>
                        <div class="text-{{ $summary['alerts']['color'] }}-700 font-semibold text-sm">
                            @if($summary['alerts']['total'] == 0)
                                Sin alertas
                            @else
                                {{ $summary['alerts']['total'] }} {{ $summary['alerts']['total'] == 1 ? 'alerta' : 'alertas' }}
                            @endif
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-zinc-600 text-sm">🚨 Críticas</span>
                                <span class="text-2xl font-bold text-red-700">{{ $summary['alerts']['critical'] }}</span>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <span class="text-zinc-600 text-sm">⚠️ Avisos</span>
                                <span class="text-xl font-bold text-yellow-700">{{ $summary['alerts']['warnings'] }}</span>
                            </div>
                            
                            @if(!empty($summary['alerts']['list']))
                                <div class="mt-3 space-y-2">
                                    @foreach(array_slice($summary['alerts']['list'], 0, 2) as $alert)
                                        <div class="text-xs px-3 py-2 rounded-lg 
                                            {{ $alert['type'] === 'critical' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            • {{ $alert['message'] }}
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        
                        <a href="{{ $summary['alerts']['detail_route'] }}" 
                           class="mt-6 w-full bg-{{ $summary['alerts']['color'] }}-100 hover:bg-{{ $summary['alerts']['color'] }}-200 text-{{ $summary['alerts']['color'] }}-800 py-3 px-4 rounded-lg font-semibold transition-all flex items-center justify-center gap-2 group-hover:scale-105">
                            <span>🔔 Ver Todas</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Advanced Analysis Section --}}
            <div class="bg-gradient-to-r from-purple-50 to-blue-50 rounded-2xl shadow-lg p-8 border-2 border-purple-200">
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-zinc-900 mb-2">🔍 ¿Necesitas Análisis Más Detallado?</h2>
                    <p class="text-zinc-600">Accede al dashboard avanzado con todas las métricas y gráficos históricos</p>
                </div>
                
                <div class="flex flex-wrap justify-center gap-4">
                    <a href="{{ route('remote-sensing.advanced') }}" 
                       class="px-8 py-4 bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white rounded-xl font-bold shadow-xl transition-all transform hover:scale-105 flex items-center gap-3">
                        <span>📊 Análisis Completo (13 Pestañas)</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                    
                    <a href="{{ route('remote-sensing.advanced', ['tab' => 'history']) }}" 
                       class="px-8 py-4 bg-white hover:bg-zinc-50 text-zinc-800 border-2 border-purple-300 rounded-xl font-bold shadow-lg transition-all flex items-center gap-3">
                        <span>📈 Ver Histórico</span>
                    </a>
                    
                    <a href="{{ route('remote-sensing.advanced', ['tab' => 'compare']) }}" 
                       class="px-8 py-4 bg-white hover:bg-zinc-50 text-zinc-800 border-2 border-blue-300 rounded-xl font-bold shadow-lg transition-all flex items-center gap-3">
                        <span>⚖️ Comparar Parcelas</span>
                    </a>
                </div>
            </div>

            {{-- Footer --}}
            <div class="mt-8 text-center text-sm text-zinc-500">
                <p>🛰️ Datos NASA • VIIRS + MODIS + SMAP • 100% Gratuito</p>
            </div>
        @elseif($selectedPlot && !empty($summary))
            {{-- No hay datos de teledetección --}}
            <div class="text-center py-24 bg-white rounded-2xl shadow-lg">
                <span class="text-8xl">📡</span>
                <p class="text-zinc-900 mt-6 text-2xl font-bold">Sin datos de teledetección</p>
                <p class="text-zinc-600 mt-3 text-lg">La parcela "{{ $selectedPlot->name }}" aún no tiene datos satelitales</p>
                
                @if($selectedPlot->area)
                    <p class="text-zinc-500 mt-2">📐 Superficie: {{ number_format($selectedPlot->area, 2) }} ha</p>
                @endif
                
                <div class="mt-8 max-w-md mx-auto">
                    <div class="mt-6 flex justify-center">
                        <button wire:click="generateData"
                                wire:loading.attr="disabled"
                                wire:target="generateData"
                                class="px-8 py-3 bg-green-600 hover:bg-green-700 disabled:bg-zinc-400 text-white rounded-xl font-bold shadow-lg transition-all flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            🛰️ Generar Datos Ahora
                        </button>
                    </div>

                    @if($generateError)
                        <div class="mt-4 bg-red-50 border border-red-200 rounded-lg p-4 text-red-800 text-sm">
                            ❌ {{ $generateError }}
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="text-center py-24 bg-white rounded-2xl shadow-lg">
                <span class="text-8xl">🛰️</span>
                <p class="text-zinc-600 mt-6 text-xl">Selecciona una parcela para ver el análisis</p>
                @if(count($plots) === 0)
                    <div class="mt-8 max-w-md mx-auto bg-yellow-50 border border-yellow-200 rounded-xl p-6">
                        <p class="text-yellow-900 font-semibold mb-2">⚠️ No tienes parcelas</p>
                        <p class="text-yellow-800 text-sm">Crea tu primera parcela para empezar a ver datos de teledetección</p>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
