<div class="bg-white rounded-lg shadow-lg p-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-[var(--color-agro-green-dark)] flex items-center gap-2">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            🛰️ Teledetección Sentinel-2
        </h2>
        <button wire:click="refreshData" 
                wire:loading.attr="disabled"
                class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1 transition">
            <svg wire:loading.remove wire:target="refreshData" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <svg wire:loading wire:target="refreshData" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            Actualizar
        </button>
    </div>

    @if($error)
        <div class="p-4 bg-red-50 rounded-lg text-red-700 mb-4">
            <p>{{ $error }}</p>
        </div>
    @elseif($isLoading)
        <div class="flex items-center justify-center py-8">
            <svg class="w-8 h-8 animate-spin text-green-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span class="ml-2 text-gray-600">Cargando datos de satélite...</span>
        </div>
    @elseif($latestData)
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <!-- NDVI Principal -->
            <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-4 border border-green-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-semibold text-gray-600">NDVI</span>
                    <span class="text-lg">{{ $latestData->health_emoji }}</span>
                </div>
                <div class="text-3xl font-bold text-green-700 mb-1">
                    {{ number_format($latestData->ndvi_mean, 2) }}
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5 mb-2">
                    <div class="h-2.5 rounded-full transition-all duration-500"
                         style="width: {{ $latestData->ndvi_percentage }}%; 
                                background: linear-gradient(90deg, #ef4444, #eab308, #22c55e);"></div>
                </div>
                <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full
                    @if($latestData->health_status === 'excellent') bg-green-100 text-green-800
                    @elseif($latestData->health_status === 'good') bg-emerald-100 text-emerald-800
                    @elseif($latestData->health_status === 'moderate') bg-yellow-100 text-yellow-800
                    @elseif($latestData->health_status === 'poor') bg-orange-100 text-orange-800
                    @else bg-red-100 text-red-800
                    @endif">
                    {{ $latestData->health_text }}
                </span>
            </div>

            <!-- Tendencia -->
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg p-4 border border-blue-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-semibold text-gray-600">Tendencia</span>
                    <span class="text-lg {{ $latestData->trend_color }}">{{ $latestData->trend_icon }}</span>
                </div>
                <div class="text-2xl font-bold mb-1 {{ $latestData->trend_color }}">
                    @if($latestData->ndvi_change !== null)
                        {{ $latestData->ndvi_change > 0 ? '+' : '' }}{{ number_format($latestData->ndvi_change * 100, 1) }}%
                    @else
                        --
                    @endif
                </div>
                <p class="text-xs text-gray-500">
                    vs periodo anterior
                </p>
                <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full mt-2
                    @if($latestData->trend === 'increasing') bg-green-100 text-green-800
                    @elseif($latestData->trend === 'stable') bg-gray-100 text-gray-800
                    @else bg-red-100 text-red-800
                    @endif">
                    @if($latestData->trend === 'increasing') En aumento
                    @elseif($latestData->trend === 'stable') Estable
                    @else En descenso
                    @endif
                </span>
            </div>

            <!-- Información de imagen -->
            <div class="bg-gradient-to-br from-purple-50 to-violet-50 rounded-lg p-4 border border-purple-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-semibold text-gray-600">Última imagen</span>
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="text-xl font-bold text-purple-700 mb-1">
                    {{ $latestData->image_date->format('d/m/Y') }}
                </div>
                <p class="text-xs text-gray-500">
                    {{ $latestData->image_date->diffForHumans() }}
                </p>
                <div class="mt-2 flex items-center gap-2 text-xs text-gray-600">
                    <span>☁️ {{ $latestData->cloud_coverage ?? 0 }}% nubes</span>
                </div>
            </div>
        </div>
        
        <!-- 📊 Comparativa Interanual -->
        @if($lastYearNdvi !== null)
        <div class="bg-gradient-to-r from-amber-50 to-orange-50 rounded-lg p-4 border border-amber-200 mb-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center">
                        <span class="text-2xl">📅</span>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-800">Comparativa vs Año Anterior</h4>
                        <p class="text-xs text-gray-500">Mismo mes de {{ now()->year - 1 }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="flex items-center gap-4">
                        <div class="text-center">
                            <div class="text-sm text-gray-500">{{ now()->year - 1 }}</div>
                            <div class="text-xl font-bold text-gray-600">{{ number_format($lastYearNdvi, 2) }}</div>
                        </div>
                        <div class="text-2xl">→</div>
                        <div class="text-center">
                            <div class="text-sm text-gray-500">{{ now()->year }}</div>
                            <div class="text-xl font-bold text-green-600">{{ number_format($latestData->ndvi_mean, 2) }}</div>
                        </div>
                        <div class="px-3 py-1 rounded-full text-sm font-bold
                            @if($yearChange > 0) bg-green-100 text-green-700
                            @elseif($yearChange < 0) bg-red-100 text-red-700
                            @else bg-gray-100 text-gray-700
                            @endif">
                            @if($yearChange > 0) ↑ @elseif($yearChange < 0) ↓ @else = @endif
                            {{ number_format(abs($yearChange * 100), 1) }}%
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Información adicional -->
        <div class="bg-gray-50 rounded-lg p-4 mb-4">
            <p class="text-sm text-gray-700">
                <strong>Análisis:</strong> {{ $latestData->health_notes }}
            </p>
            @if($latestData->metadata && isset($latestData->metadata['mock_data']) && $latestData->metadata['mock_data'])
                <p class="text-xs text-amber-600 mt-2 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Datos de demostración. Conecta con Copernicus para datos reales.
                </p>
            @endif
        </div>

        <!-- Botón para ver gráfica histórica -->
        <div class="flex justify-between items-center">
            <button wire:click="toggleChart" 
                    class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                {{ $showChart ? 'Ocultar histórico' : 'Ver histórico (90 días)' }}
            </button>
            
            <a href="{{ route('remote-sensing.dashboard') }}" 
               class="text-sm text-green-600 hover:text-green-800 flex items-center gap-1 transition">
                Ver todas las parcelas
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <!-- Gráfica histórica -->
        @if($showChart && count($historicalData) > 0)
            <div class="mt-4 p-4 bg-white border rounded-lg">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">📈 Evolución NDVI - Últimos 90 días</h3>
                <div class="h-48 flex items-end justify-between gap-1">
                    @foreach($historicalData as $index => $data)
                        @php
                            $height = ($data['ndvi'] + 1) / 2 * 100;
                            $color = $data['ndvi'] >= 0.5 ? 'bg-green-500' : ($data['ndvi'] >= 0.3 ? 'bg-yellow-500' : 'bg-red-500');
                        @endphp
                        <div class="flex-1 flex flex-col items-center group relative">
                            <div class="absolute bottom-full mb-2 hidden group-hover:block bg-gray-800 text-white text-xs rounded py-1 px-2 whitespace-nowrap z-10">
                                {{ $data['fullDate'] }}: {{ number_format($data['ndvi'], 3) }}
                            </div>
                            <div class="w-full {{ $color }} rounded-t transition-all hover:opacity-80" 
                                 style="height: {{ max(5, $height) }}%;">
                            </div>
                            @if($index % 5 == 0)
                                <span class="text-[8px] text-gray-500 mt-1 rotate-45 origin-left">{{ $data['date'] }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
                <div class="flex justify-between text-xs text-gray-500 mt-2">
                    <span>← 90 días atrás</span>
                    <span>Hoy →</span>
                </div>
            </div>
        @endif
    @else
        <div class="text-center py-8 bg-gray-50 rounded-lg">
            <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/>
            </svg>
            <p class="text-gray-600 mb-2">No hay datos de teledetección disponibles</p>
            <p class="text-sm text-gray-500">Los datos se generarán automáticamente cuando haya imágenes satelitales disponibles para esta parcela.</p>
        </div>
    @endif
</div>
