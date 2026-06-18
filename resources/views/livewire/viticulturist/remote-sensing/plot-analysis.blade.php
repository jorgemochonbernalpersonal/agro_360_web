<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-agro-700 flex items-center gap-3">{{ __('🛰️ Análisis de Parcela') }}</h1>
                <p class="text-zinc-600 mt-1">{{ $plot->name }} - Datos satelitales y meteorológicos</p>
            </div>
            <div class="flex items-center gap-2">
                {{-- Export Buttons --}}
                <div class="flex items-center gap-1 mr-2">
                    <a href="{{ route('remote-sensing.export.pdf', $plot) }}" 
                       class="bg-red-50 text-red-700 px-3 py-2 rounded-lg hover:bg-red-100 transition flex items-center gap-2 text-sm border border-red-200">
                        📄 PDF
                    </a>
                    <a href="{{ route('remote-sensing.export.excel', $plot) }}" 
                       class="bg-agro-50 text-agro-700 px-3 py-2 rounded-lg hover:bg-agro-100 transition flex items-center gap-2 text-sm border border-agro-200">
                        📊 Excel
                    </a>
                </div>
                <button wire:click="refreshData" 
                        class="bg-agro-700 text-white px-4 py-2 rounded-lg hover:bg-agro-600 transition flex items-center gap-2 shadow-lg">
                    <svg wire:loading.remove wire:target="refreshData" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <svg wire:loading wire:target="refreshData" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Actualizar
                </button>
                <a href="{{ route('remote-sensing.dashboard') }}" 
                   class="border-2 border-zinc-300 text-zinc-700 px-4 py-2 rounded-lg hover:bg-zinc-50 transition flex items-center gap-2">
                    ← Volver
                </a>
            </div>
        </div>
    </div>

    @if($error)
        <div class="p-4 bg-red-50 rounded-lg text-red-700 mb-4">
            <p>{{ $error }}</p>
        </div>
    @elseif($isLoading)
        <div class="flex items-center justify-center py-16">
            <svg class="w-12 h-12 animate-spin text-agro-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span class="ml-3 text-zinc-600 text-lg">{{ __('Cargando datos...') }}</span>
        </div>
    @else
        <!-- Recommendations Banner -->
        @if(count($recommendations) > 0)
            <div class="mb-6 grid grid-cols-1 md:grid-cols-{{ min(count($recommendations), 3) }} gap-4">
                @foreach($recommendations as $rec)
                    <div class="p-4 rounded-lg border-l-4
                        @if($rec['type'] === 'danger') bg-red-50 border-red-500
                        @elseif($rec['type'] === 'warning') bg-amber-50 border-amber-500
                        @elseif($rec['type'] === 'success') bg-green-50 border-green-500
                        @else bg-blue-50 border-blue-500
                        @endif">
                        <div class="flex items-center gap-2">
                            <span class="text-xl">{{ $rec['icon'] }}</span>
                            <span class="font-semibold">{{ $rec['title'] }}</span>
                        </div>
                        <p class="text-sm text-zinc-600 mt-1">{{ $rec['text'] }}</p>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Tabs Navigation -->
        <div class="mb-6 border-b border-zinc-200">
            <nav class="flex gap-4 overflow-x-auto" aria-label="{{ __('Tabs') }}">
                <button wire:click="setTab('satellite')" 
                        class="px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap
                            {{ $activeTab === 'satellite' ? 'border-green-500 text-green-600' : 'border-transparent text-zinc-500 hover:text-zinc-700' }}">
                    🛰️ Satélite
                </button>
                <button wire:click="setTab('weather')" 
                        class="px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap
                            {{ $activeTab === 'weather' ? 'border-blue-500 text-blue-600' : 'border-transparent text-zinc-500 hover:text-zinc-700' }}">
                    🌦️ Clima
                </button>
                <button wire:click="setTab('soil')" 
                        class="px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap
                            {{ $activeTab === 'soil' ? 'border-amber-500 text-amber-600' : 'border-transparent text-zinc-500 hover:text-zinc-700' }}">
                    🌱 Suelo
                </button>
                <button wire:click="setTab('solar')" 
                        class="px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap
                            {{ $activeTab === 'solar' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-zinc-500 hover:text-zinc-700' }}">
                    ☀️ Radiación
                </button>
                <button wire:click="setTab('irrigation')" 
                        class="px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap
                            {{ $activeTab === 'irrigation' ? 'border-cyan-500 text-cyan-600' : 'border-transparent text-zinc-500 hover:text-zinc-700' }}">
                    💧 Riego
                </button>
                <button wire:click="setTab('comparison')" 
                        class="px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap
                            {{ $activeTab === 'comparison' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-zinc-500 hover:text-zinc-700' }}">
                    📊 Comparativa
                </button>
                <button wire:click="setTab('history')" 
                        class="px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap
                            {{ $activeTab === 'history' ? 'border-purple-500 text-purple-600' : 'border-transparent text-zinc-500 hover:text-zinc-700' }}">
                    📅 Histórico
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <!-- Satellite Tab -->
            @if($activeTab === 'satellite')
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- NDVI Card -->
                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-6 border border-green-200">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-semibold text-zinc-600">{{ __('NDVI') }}</span>
                            <span class="text-2xl">{{ $ndviData?->health_emoji ?? '❓' }}</span>
                        </div>
                        <div class="text-4xl font-bold text-green-700 mb-2">
                            {{ number_format($ndviData?->ndvi_mean ?? 0, 2) }}
                        </div>
                        <div class="w-full bg-zinc-200 rounded-full h-3 mb-3">
                            <div class="h-3 rounded-full transition-all duration-500"
                                 style="width: {{ $ndviData?->ndvi_percentage ?? 0 }}%; 
                                        background: linear-gradient(90deg, #ef4444, #eab308, #22c55e);"></div>
                        </div>
                        <span class="inline-block px-3 py-1 text-sm font-semibold rounded-full
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
                    <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-lg p-6 border border-blue-200">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-semibold text-zinc-600">{{ __('NDWI (Agua)') }}</span>
                            <span class="text-2xl">💧</span>
                        </div>
                        <div class="text-4xl font-bold text-blue-700 mb-2">
                            {{ number_format($ndviData?->ndwi_mean ?? 0, 2) }}
                        </div>
                        <p class="text-sm text-zinc-600">{{ __('Índice de contenido de agua en la vegetación') }}</p>
                    </div>

                    <!-- Year Comparison -->
                    <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-lg p-6 border border-amber-200">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-semibold text-zinc-600">vs {{ now()->year - 1 }}</span>
                            <span class="text-2xl">📅</span>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="text-center">
                                <div class="text-xs text-zinc-500">{{ now()->year - 1 }}</div>
                                <div class="text-2xl font-bold text-zinc-600">{{ number_format($lastYearNdvi ?? 0, 2) }}</div>
                            </div>
                            <div class="text-2xl">→</div>
                            <div class="text-center">
                                <div class="text-xs text-zinc-500">{{ now()->year }}</div>
                                <div class="text-2xl font-bold text-green-600">{{ number_format($ndviData?->ndvi_mean ?? 0, 2) }}</div>
                            </div>
                            <div class="px-3 py-1 rounded-full text-sm font-bold
                                @if($yearChange > 0) bg-green-100 text-green-700
                                @elseif($yearChange < 0) bg-red-100 text-red-700
                                @else bg-zinc-100 text-zinc-700
                                @endif">
                                @if($yearChange > 0) ↑ @elseif($yearChange < 0) ↓ @else = @endif
                                {{ number_format(abs(($yearChange ?? 0) * 100), 1) }}%
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Satellite Info -->
                <div class="mt-6 bg-zinc-50 rounded-lg p-4">
                    <div class="flex items-center gap-4 text-sm text-zinc-600">
                        <span>{{ __('📡 Fuente: NASA MODIS') }}</span>
                        <span>📅 Última imagen: {{ $ndviData?->image_date?->format('d/m/Y') ?? 'N/A' }}</span>
                        <span>☁️ Nubes: {{ $ndviData?->cloud_coverage ?? 0 }}%</span>
                    </div>
                </div>
            @endif

            <!-- Weather Tab -->
            @if($activeTab === 'weather')
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="bg-gradient-to-br from-orange-50 to-red-50 rounded-lg p-4 border border-orange-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold text-zinc-600">{{ __('Temperatura') }}</span>
                            <span class="text-xl">{{ __('🌡️') }}</span>
                        </div>
                        <div class="text-3xl font-bold text-orange-600">{{ $weather['temperature'] ?? '--' }}°C</div>
                        <div class="text-xs text-zinc-500 mt-1">
                            Min: {{ $weather['temperature_min'] ?? '--' }}° / Max: {{ $weather['temperature_max'] ?? '--' }}°
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-lg p-4 border border-blue-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold text-zinc-600">{{ __('Humedad') }}</span>
                            <span class="text-xl">💧</span>
                        </div>
                        <div class="text-3xl font-bold text-blue-600">{{ $weather['humidity'] ?? '--' }}%</div>
                        <div class="text-xs text-zinc-500 mt-1">{{ __('Relativa del aire') }}</div>
                    </div>

                    <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-lg p-4 border border-indigo-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold text-zinc-600">{{ __('Precipitación') }}</span>
                            <span class="text-xl">{{ __('🌧️') }}</span>
                        </div>
                        <div class="text-3xl font-bold text-indigo-600">{{ $weather['precipitation'] ?? 0 }} mm</div>
                        <div class="text-xs text-zinc-500 mt-1">{{ __('Últimas 24h') }}</div>
                    </div>

                    <div class="bg-gradient-to-br from-teal-50 to-emerald-50 rounded-lg p-4 border border-teal-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold text-zinc-600">{{ __('Viento') }}</span>
                            <span class="text-xl">💨</span>
                        </div>
                        <div class="text-3xl font-bold text-teal-600">{{ $weather['wind_speed'] ?? '--' }} km/h</div>
                        <div class="text-xs text-zinc-500 mt-1">{{ __('Velocidad media') }}</div>
                    </div>
                </div>

                <!-- 7-Day Forecast -->
                <h3 class="text-lg font-semibold mb-4">{{ __('📅 Pronóstico 7 días') }}</h3>
                <div class="grid grid-cols-7 gap-2">
                    @foreach($forecast as $day)
                        <div class="text-center p-3 bg-gradient-to-b from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
                            <div class="text-xs text-zinc-500 font-medium">
                                {{ \Carbon\Carbon::parse($day['date'])->locale('es')->isoFormat('ddd') }}
                            </div>
                            <div class="text-2xl my-2">
                                {{ \App\Services\RemoteSensing\WeatherService::getWeatherIcon($day['weather_code'] ?? 0) }}
                            </div>
                            <div class="text-sm font-bold">
                                <span class="text-red-500">{{ round($day['temp_max'] ?? 0) }}°</span>
                                <span class="text-blue-500">{{ round($day['temp_min'] ?? 0) }}°</span>
                            </div>
                            @if(($day['precipitation'] ?? 0) > 0)
                                <div class="text-xs text-blue-600 mt-1">💧 {{ round($day['precipitation']) }}mm</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Soil Tab -->
            @if($activeTab === 'soil')
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-gradient-to-br from-amber-50 to-yellow-50 rounded-lg p-6 border border-amber-200">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-semibold text-zinc-600">{{ __('Humedad del Suelo') }}</span>
                            <span class="text-2xl">🌱</span>
                        </div>
                        <div class="text-4xl font-bold text-amber-700 mb-3">{{ $soil['soil_moisture'] ?? '--' }}%</div>
                        <div class="w-full bg-zinc-200 rounded-full h-4">
                            <div class="h-4 rounded-full bg-gradient-to-r from-amber-300 to-amber-600" 
                                 style="width: {{ min(100, $soil['soil_moisture'] ?? 0) }}%"></div>
                        </div>
                        <p class="text-sm text-zinc-500 mt-2">{{ __('Porcentaje volumétrico') }}</p>
                    </div>

                    <div class="bg-gradient-to-br from-orange-50 to-red-50 rounded-lg p-6 border border-orange-200">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-semibold text-zinc-600">{{ __('Temperatura Suelo') }}</span>
                            <span class="text-2xl">{{ __('🌡️') }}</span>
                        </div>
                        <div class="text-4xl font-bold text-orange-700">{{ $soil['soil_temperature'] ?? '--' }}°C</div>
                        <p class="text-sm text-zinc-500 mt-2">{{ __('A nivel superficial') }}</p>
                    </div>

                    <div class="bg-gradient-to-br {{ $waterStress['bg'] }} rounded-lg p-6 border border-green-200">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-semibold text-zinc-600">{{ __('Estrés Hídrico') }}</span>
                            <span class="text-2xl">{{ $waterStress['emoji'] }}</span>
                        </div>
                        <div class="text-4xl font-bold {{ $waterStress['color'] }}">{{ $waterStress['text'] }}</div>
                        <p class="text-sm text-zinc-500 mt-2">{{ __('Basado en humedad y ET0') }}</p>
                    </div>
                </div>
            @endif

            <!-- Solar Tab -->
            @if($activeTab === 'solar')
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-gradient-to-br from-yellow-50 to-orange-50 rounded-lg p-6 border border-yellow-200">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-semibold text-zinc-600">{{ __('Radiación Solar') }}</span>
                            <span class="text-2xl">☀️</span>
                        </div>
                        <div class="text-4xl font-bold text-yellow-600">{{ $solar['solar_radiation'] ?? '--' }}</div>
                        <p class="text-sm text-zinc-500 mt-2">{{ __('MJ/m² (acumulado diario)') }}</p>
                    </div>

                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg p-6 border border-blue-200">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-semibold text-zinc-600">{{ __('ET0 (Evapotranspiración)') }}</span>
                            <span class="text-2xl">💦</span>
                        </div>
                        <div class="text-4xl font-bold text-blue-600">{{ $solar['et0'] ?? '--' }}</div>
                        <p class="text-sm text-zinc-500 mt-2">{{ __('mm/día (referencia FAO)') }}</p>
                    </div>

                    <div class="bg-gradient-to-br from-amber-50 to-yellow-50 rounded-lg p-6 border border-amber-200">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-semibold text-zinc-600">{{ __('Horas de Sol') }}</span>
                            <span class="text-2xl">{{ __('🌤️') }}</span>
                        </div>
                        <div class="text-4xl font-bold text-amber-600">{{ round($solar['sunshine_hours'] ?? 0, 1) }}h</div>
                        <p class="text-sm text-zinc-500 mt-2">{{ __('Exposición solar diaria') }}</p>
                    </div>
                </div>
            @endif

            <!-- Irrigation Tab -->
            @if($activeTab === 'irrigation')
                @livewire('viticulturist.remote-sensing.plot-irrigation-card', ['plot' => $plot], key('irrigation-' . $plot->id))
            @endif

            <!-- Year Comparison Tab -->
            @if($activeTab === 'comparison')
                @livewire('viticulturist.remote-sensing.plot-year-comparison', ['plot' => $plot], key('comparison-' . $plot->id))
            @endif

            <!-- History Tab -->
            @if($activeTab === 'history')
                @livewire('viticulturist.remote-sensing.plot-image-history', ['plot' => $plot], key('history-' . $plot->id))
                <h3 class="text-lg font-semibold mb-4">{{ __('📈 Evolución NDVI - Últimos 90 días') }}</h3>
                @if(count($historicalData) > 0)
                    <div class="h-64 flex items-end justify-between gap-1 bg-zinc-50 rounded-lg p-4">
                        @foreach($historicalData as $index => $data)
                            @php
                                $height = ($data['ndvi'] + 1) / 2 * 100;
                                $color = $data['ndvi'] >= 0.5 ? 'bg-green-500' : ($data['ndvi'] >= 0.3 ? 'bg-yellow-500' : 'bg-red-500');
                            @endphp
                            <div class="flex-1 flex flex-col items-center group relative">
                                <div class="absolute bottom-full mb-2 hidden group-hover:block bg-zinc-800 text-white text-xs rounded py-1 px-2 whitespace-nowrap z-10">
                                    {{ $data['fullDate'] }}: {{ number_format($data['ndvi'], 3) }}
                                </div>
                                <div class="w-full {{ $color }} rounded-t transition-all hover:opacity-80" 
                                     style="height: {{ max(10, $height) }}%;"></div>
                                @if($index % 5 == 0)
                                    <span class="text-[8px] text-zinc-500 mt-1 rotate-45 origin-left">{{ $data['date'] }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="flex justify-between text-xs text-zinc-500 mt-2 px-4">
                        <span>{{ __('← 90 días atrás') }}</span>
                        <span>{{ __('Hoy →') }}</span>
                    </div>
                @else
                    <div class="text-center py-12 text-zinc-500">
                        <p>{{ __('No hay datos históricos disponibles') }}</p>
                    </div>
                @endif
            @endif
        </div>

        <!-- Info Footer -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <span class="text-xl">ℹ️</span>
                <div>
                    <h4 class="font-semibold text-blue-800">{{ __('Fuentes de datos') }}</h4>
                    <p class="text-sm text-blue-700">{{ __('🛰️ Satélite: NASA MODIS (gratuito) | 
                        🌦️ Clima: Open-Meteo API (gratuito) | 
                        Actualización: cada hora') }}</p>
                </div>
            </div>
        </div>
    @endif
</div>
