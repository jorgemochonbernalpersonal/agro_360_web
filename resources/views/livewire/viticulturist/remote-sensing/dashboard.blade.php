<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-[var(--color-agro-green-dark)] flex items-center gap-3">
                🛰️ Teledetección
            </h1>
            <p class="text-gray-600 mt-1">Datos satelitales y meteorológicos de tus parcelas</p>
        </div>
        
        <div class="flex items-center gap-3">
            <!-- Plot Selector -->
            <select wire:model.live="selectedPlotId" 
                    class="px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 min-w-[200px]">
                @foreach($plots as $plot)
                    <option value="{{ $plot->id }}">{{ $plot->name }}</option>
                @endforeach
            </select>
            
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
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
        <div class="bg-white rounded-lg shadow p-3 text-center">
            <div class="text-2xl font-bold text-gray-700">{{ $stats['total_plots'] ?? 0 }}</div>
            <div class="text-xs text-gray-500">Parcelas</div>
        </div>
        <div class="bg-green-50 rounded-lg shadow p-3 text-center border border-green-200">
            <div class="text-2xl font-bold text-green-600">{{ $stats['average_ndvi'] ?? 0 }}</div>
            <div class="text-xs text-gray-500">NDVI Medio</div>
        </div>
        <div class="bg-emerald-50 rounded-lg shadow p-3 text-center border border-emerald-200">
            <div class="text-2xl font-bold text-emerald-600">{{ ($stats['excellent'] ?? 0) + ($stats['good'] ?? 0) }}</div>
            <div class="text-xs text-gray-500">Saludables</div>
        </div>
        <div class="bg-yellow-50 rounded-lg shadow p-3 text-center border border-yellow-200">
            <div class="text-2xl font-bold text-yellow-600">{{ $stats['moderate'] ?? 0 }}</div>
            <div class="text-xs text-gray-500">Moderadas</div>
        </div>
        <div class="bg-red-50 rounded-lg shadow p-3 text-center border border-red-200">
            <div class="text-2xl font-bold text-red-600">{{ $stats['alerts'] ?? 0 }}</div>
            <div class="text-xs text-gray-500">⚠️ Alertas</div>
        </div>
    </div>

    @if($isLoading)
        <div class="flex items-center justify-center py-16">
            <svg class="w-12 h-12 animate-spin text-green-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span class="ml-3 text-gray-600 text-lg">Cargando datos...</span>
        </div>
    @elseif($selectedPlot)
        <!-- Recommendations Banner -->
        @if(count($recommendations) > 0)
            <div class="mb-6 flex flex-wrap gap-3">
                @foreach($recommendations as $rec)
                    <div class="flex-1 min-w-[200px] p-3 rounded-lg border-l-4
                        @if($rec['type'] === 'danger') bg-red-50 border-red-500
                        @elseif($rec['type'] === 'warning') bg-amber-50 border-amber-500
                        @elseif($rec['type'] === 'success') bg-green-50 border-green-500
                        @else bg-blue-50 border-blue-500
                        @endif">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">{{ $rec['icon'] }}</span>
                            <span class="font-semibold text-sm">{{ $rec['title'] }}</span>
                        </div>
                        <p class="text-xs text-gray-600 mt-1">{{ $rec['text'] }}</p>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Tabs Navigation -->
        <div class="mb-4 border-b border-gray-200">
            <nav class="flex gap-1 overflow-x-auto" aria-label="Tabs">
                <button wire:click="setTab('satellite')" 
                        class="px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap
                            {{ $activeTab === 'satellite' ? 'border-green-500 text-green-600 bg-green-50' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    🛰️ Satélite
                </button>
                <button wire:click="setTab('weather')" 
                        class="px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap
                            {{ $activeTab === 'weather' ? 'border-blue-500 text-blue-600 bg-blue-50' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    🌦️ Clima
                </button>
                <button wire:click="setTab('soil')" 
                        class="px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap
                            {{ $activeTab === 'soil' ? 'border-amber-500 text-amber-600 bg-amber-50' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    🌱 Suelo
                </button>
                <button wire:click="setTab('solar')" 
                        class="px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap
                            {{ $activeTab === 'solar' ? 'border-yellow-500 text-yellow-600 bg-yellow-50' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    ☀️ Radiación
                </button>
                <button wire:click="setTab('irrigation')" 
                        class="px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap
                            {{ $activeTab === 'irrigation' ? 'border-cyan-500 text-cyan-600 bg-cyan-50' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    💧 Riego
                </button>
                <button wire:click="setTab('harvest')" 
                        class="px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap
                            {{ $activeTab === 'harvest' ? 'border-pink-500 text-pink-600 bg-pink-50' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    🍇 Cosecha
                </button>
                <button wire:click="setTab('compare')" 
                        class="px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap
                            {{ $activeTab === 'compare' ? 'border-violet-500 text-violet-600 bg-violet-50' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    ⚖️ Comparar
                </button>
                <button wire:click="setTab('history')" 
                        class="px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap
                            {{ $activeTab === 'history' ? 'border-purple-500 text-purple-600 bg-purple-50' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    📊 Histórico
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <!-- Header with plot name -->
            <div class="flex items-center gap-3 mb-4 pb-4 border-b">
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                    <span class="text-xl">🌱</span>
                </div>
                <div>
                    <h2 class="font-bold text-lg">{{ $selectedPlot->name }}</h2>
                    <p class="text-sm text-gray-500">{{ $selectedPlot->municipality?->name ?? 'Sin municipio' }}</p>
                </div>
            </div>

            <!-- Satellite Tab -->
            @if($activeTab === 'satellite')
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- NDVI Card -->
                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-5 border border-green-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold text-gray-600">NDVI</span>
                            <span class="text-2xl">{{ $ndviData?->health_emoji ?? '❓' }}</span>
                        </div>
                        <div class="text-4xl font-bold text-green-700 mb-2">
                            {{ number_format($ndviData?->ndvi_mean ?? 0, 2) }}
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5 mb-2">
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
                            <span class="text-sm font-semibold text-gray-600">NDWI (Agua)</span>
                            <span class="text-2xl">💧</span>
                        </div>
                        <div class="text-4xl font-bold text-blue-700 mb-2">
                            {{ number_format($ndviData?->ndwi_mean ?? 0, 2) }}
                        </div>
                        <p class="text-xs text-gray-600">Contenido de agua en vegetación</p>
                    </div>

                    <!-- Year Comparison -->
                    <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-lg p-5 border border-amber-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold text-gray-600">vs {{ now()->year - 1 }}</span>
                            <span class="text-2xl">📅</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="text-center">
                                <div class="text-xs text-gray-500">{{ now()->year - 1 }}</div>
                                <div class="text-xl font-bold text-gray-600">{{ number_format($lastYearNdvi ?? 0, 2) }}</div>
                            </div>
                            <span class="text-xl">→</span>
                            <div class="text-center">
                                <div class="text-xs text-gray-500">{{ now()->year }}</div>
                                <div class="text-xl font-bold text-green-600">{{ number_format($ndviData?->ndvi_mean ?? 0, 2) }}</div>
                            </div>
                            <div class="px-2 py-1 rounded-full text-xs font-bold
                                @if($yearChange > 0) bg-green-100 text-green-700
                                @elseif($yearChange < 0) bg-red-100 text-red-700
                                @else bg-gray-100 text-gray-700
                                @endif">
                                @if($yearChange > 0)↑@elseif($yearChange < 0)↓@else=@endif
                                {{ number_format(abs(($yearChange ?? 0) * 100), 1) }}%
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-4 bg-gray-50 rounded-lg p-3 text-sm text-gray-600 flex gap-4">
                    <span>📡 NASA MODIS</span>
                    <span>📅 {{ $ndviData?->image_date?->format('d/m/Y') ?? 'N/A' }}</span>
                    <span>☁️ {{ $ndviData?->cloud_coverage ?? 0 }}% nubes</span>
                </div>
            @endif

            <!-- Weather Tab -->
            @if($activeTab === 'weather')
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
                    <div class="bg-gradient-to-br from-orange-50 to-red-50 rounded-lg p-4 border border-orange-200 text-center">
                        <span class="text-2xl">🌡️</span>
                        <div class="text-2xl font-bold text-orange-600">{{ $weather['temperature'] ?? '--' }}°C</div>
                        <div class="text-xs text-gray-500">{{ $weather['temperature_min'] ?? '--' }}° / {{ $weather['temperature_max'] ?? '--' }}°</div>
                    </div>
                    <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-lg p-4 border border-blue-200 text-center">
                        <span class="text-2xl">💧</span>
                        <div class="text-2xl font-bold text-blue-600">{{ $weather['humidity'] ?? '--' }}%</div>
                        <div class="text-xs text-gray-500">Humedad</div>
                    </div>
                    <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-lg p-4 border border-indigo-200 text-center">
                        <span class="text-2xl">🌧️</span>
                        <div class="text-2xl font-bold text-indigo-600">{{ $weather['precipitation'] ?? 0 }}mm</div>
                        <div class="text-xs text-gray-500">Precipitación</div>
                    </div>
                    <div class="bg-gradient-to-br from-teal-50 to-emerald-50 rounded-lg p-4 border border-teal-200 text-center">
                        <span class="text-2xl">💨</span>
                        <div class="text-2xl font-bold text-teal-600">{{ $weather['wind_speed'] ?? '--' }}km/h</div>
                        <div class="text-xs text-gray-500">Viento</div>
                    </div>
                </div>
                
                <!-- 7-Day Forecast -->
                <h3 class="text-md font-semibold mb-3">📅 Pronóstico 7 días</h3>
                <div class="grid grid-cols-7 gap-2">
                    @foreach($forecast as $day)
                        <div class="text-center p-2 bg-gradient-to-b from-blue-50 to-indigo-50 rounded-lg border border-blue-100">
                            <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($day['date'])->locale('es')->isoFormat('ddd') }}</div>
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
                            <span class="text-sm font-semibold text-gray-600">Humedad Suelo</span>
                            <span class="text-2xl">🌱</span>
                        </div>
                        <div class="text-4xl font-bold text-amber-700">{{ $soil['soil_moisture'] ?? '--' }}%</div>
                        <div class="w-full bg-gray-200 rounded-full h-3 mt-2">
                            <div class="h-3 rounded-full bg-gradient-to-r from-amber-300 to-amber-600" style="width: {{ min(100, $soil['soil_moisture'] ?? 0) }}%"></div>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-orange-50 to-red-50 rounded-lg p-5 border border-orange-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold text-gray-600">Temp. Suelo</span>
                            <span class="text-2xl">🌡️</span>
                        </div>
                        <div class="text-4xl font-bold text-orange-700">{{ $soil['soil_temperature'] ?? '--' }}°C</div>
                    </div>
                    <div class="bg-gradient-to-br {{ $waterStress['bg'] }} rounded-lg p-5 border border-green-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold text-gray-600">Estrés Hídrico</span>
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
                            <span class="text-sm font-semibold text-gray-600">Radiación Solar</span>
                            <span class="text-2xl">☀️</span>
                        </div>
                        <div class="text-4xl font-bold text-yellow-600">{{ $solar['solar_radiation'] ?? '--' }}</div>
                        <p class="text-xs text-gray-500 mt-1">MJ/m²</p>
                    </div>
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg p-5 border border-blue-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold text-gray-600">ET0</span>
                            <span class="text-2xl">💦</span>
                        </div>
                        <div class="text-4xl font-bold text-blue-600">{{ $solar['et0'] ?? '--' }}</div>
                        <p class="text-xs text-gray-500 mt-1">mm/día</p>
                    </div>
                    <div class="bg-gradient-to-br from-amber-50 to-yellow-50 rounded-lg p-5 border border-amber-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold text-gray-600">Horas de Sol</span>
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
                        <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            💧 Balance Hídrico Semanal
                        </h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">ET0 (evapotranspiración)</span>
                                <span class="font-bold text-blue-600">{{ $irrigationNeeds['et0'] }} mm/día</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Kc (coef. cultivo vid)</span>
                                <span class="font-bold">{{ $irrigationNeeds['kc'] }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">ETc (necesidad cultivo)</span>
                                <span class="font-bold text-cyan-600">{{ $irrigationNeeds['etc'] }} mm/día</span>
                            </div>
                            <hr class="border-gray-200">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Necesidad semanal</span>
                                <span class="font-bold">{{ $irrigationNeeds['weekly_need_mm'] }} mm</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Lluvia prevista</span>
                                <span class="font-bold text-indigo-600">-{{ $irrigationNeeds['expected_rain_mm'] }} mm</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Reserva suelo</span>
                                <span class="font-bold text-amber-600">-{{ $irrigationNeeds['soil_reserve_mm'] }} mm</span>
                            </div>
                        </div>
                    </div>

                    <!-- Recommendation -->
                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-5 border border-green-200">
                        <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            🚿 Recomendación de Riego
                        </h3>
                        <div class="text-center py-4">
                            <div class="text-5xl mb-3">💧</div>
                            <div class="text-3xl font-bold {{ $irrigationNeeds['recommendation']['color'] }}">
                                {{ $irrigationNeeds['irrigation_need_mm'] }} mm
                            </div>
                            <div class="text-sm text-gray-600 mt-1">Déficit hídrico esta semana</div>
                            <div class="mt-4 px-4 py-2 rounded-full font-bold {{ $irrigationNeeds['recommendation']['bg'] }} {{ $irrigationNeeds['recommendation']['color'] }}">
                                {{ $irrigationNeeds['recommendation']['text'] }}
                            </div>
                            @if($irrigationNeeds['liters_per_ha'] > 0)
                                <div class="mt-4 text-sm text-gray-600">
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
                        <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
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
                            <div class="w-full bg-gray-200 rounded-full h-4 mt-4">
                                <div class="h-4 rounded-full bg-gradient-to-r from-green-400 to-purple-500" 
                                     style="width: {{ $gdd['stage']['progress'] }}%"></div>
                            </div>
                            <div class="flex justify-between text-xs text-gray-500 mt-1">
                                <span>Brotación</span>
                                <span>Vendimia</span>
                            </div>
                        </div>
                    </div>

                    <!-- GDD Stats -->
                    <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-lg p-5 border border-amber-200">
                        <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            🌡️ Grados-Día Acumulados (GDD)
                        </h3>
                        <div class="space-y-4">
                            <div class="bg-white rounded-lg p-3 shadow-sm">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">GDD Hoy</span>
                                    <span class="font-bold text-orange-600">+{{ $gdd['gdd_today'] }}°</span>
                                </div>
                            </div>
                            <div class="bg-white rounded-lg p-3 shadow-sm">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">GDD Próximos 7 días</span>
                                    <span class="font-bold text-amber-600">+{{ $gdd['gdd_week_forecast'] }}°</span>
                                </div>
                            </div>
                            <div class="bg-white rounded-lg p-3 shadow-sm">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">GDD Acumulado (desde 1 abril)</span>
                                    <span class="font-bold text-red-600">{{ $gdd['gdd_accumulated'] }}°</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                    <div class="h-2 rounded-full bg-gradient-to-r from-amber-400 to-red-500" 
                                         style="width: {{ min(100, ($gdd['gdd_accumulated'] / $gdd['gdd_target']) * 100) }}%"></div>
                                </div>
                                <div class="text-xs text-gray-500 mt-1 text-right">{{ $gdd['gdd_accumulated'] }} / {{ $gdd['gdd_target'] }}° objetivo</div>
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
                    <label class="block text-sm font-medium text-gray-700 mb-2">Selecciona parcela para comparar:</label>
                    <select wire:model.live="comparePlotId" 
                            class="px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-violet-500 focus:border-violet-500 min-w-[200px]">
                        <option value="">-- Seleccionar --</option>
                        @foreach($plots as $plot)
                            @if($plot->id !== $selectedPlotId)
                                <option value="{{ $plot->id }}">{{ $plot->name }}</option>
                            @endif
                        @endforeach
                    </select>
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
                                    <span class="text-sm text-gray-600">NDVI</span>
                                    <span class="font-bold text-2xl text-green-600">{{ number_format($ndviData?->ndvi_mean ?? 0, 2) }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b">
                                    <span class="text-sm text-gray-600">NDWI</span>
                                    <span class="font-bold text-blue-600">{{ number_format($ndviData?->ndwi_mean ?? 0, 2) }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b">
                                    <span class="text-sm text-gray-600">Temperatura</span>
                                    <span class="font-bold text-orange-600">{{ $weather['temperature'] ?? '--' }}°C</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b">
                                    <span class="text-sm text-gray-600">Humedad Suelo</span>
                                    <span class="font-bold text-amber-600">{{ $soil['soil_moisture'] ?? '--' }}%</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b">
                                    <span class="text-sm text-gray-600">ET0</span>
                                    <span class="font-bold text-cyan-600">{{ $solar['et0'] ?? '--' }} mm</span>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-sm text-gray-600">Estado</span>
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
                                    <span class="text-sm text-gray-600">NDVI</span>
                                    <span class="font-bold text-2xl text-violet-600">{{ number_format($compareNdviData?->ndvi_mean ?? 0, 2) }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b">
                                    <span class="text-sm text-gray-600">NDWI</span>
                                    <span class="font-bold text-blue-600">{{ number_format($compareNdviData?->ndwi_mean ?? 0, 2) }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b">
                                    <span class="text-sm text-gray-600">Temperatura</span>
                                    <span class="font-bold text-orange-600">{{ $compareWeather['temperature'] ?? '--' }}°C</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b">
                                    <span class="text-sm text-gray-600">Humedad Suelo</span>
                                    <span class="font-bold text-amber-600">{{ $compareSoil['soil_moisture'] ?? '--' }}%</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b">
                                    <span class="text-sm text-gray-600">ET0</span>
                                    <span class="font-bold text-cyan-600">{{ $compareSolar['et0'] ?? '--' }} mm</span>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-sm text-gray-600">Estado</span>
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
                    <div class="mt-4 bg-gray-100 rounded-lg p-4 text-center">
                        <span class="text-sm text-gray-600">Diferencia NDVI:</span>
                        <span class="ml-2 font-bold text-lg {{ $ndviDiff > 0 ? 'text-green-600' : ($ndviDiff < 0 ? 'text-red-600' : 'text-gray-600') }}">
                            {{ $ndviDiff > 0 ? '+' : '' }}{{ number_format($ndviDiff, 3) }}
                        </span>
                        <span class="ml-2 text-sm {{ $ndviDiff > 0 ? 'text-green-600' : ($ndviDiff < 0 ? 'text-red-600' : 'text-gray-600') }}">
                            @if($ndviDiff > 0.05) ({{ $selectedPlot->name }} mejor)
                            @elseif($ndviDiff < -0.05) ({{ $comparePlot->name }} mejor)
                            @else (similares)
                            @endif
                        </span>
                    </div>
                @else
                    <div class="text-center py-12 bg-gray-50 rounded-lg">
                        <span class="text-4xl">⚖️</span>
                        <p class="text-gray-600 mt-4">Selecciona una parcela para comparar con <strong>{{ $selectedPlot->name }}</strong></p>
                    </div>
                @endif
            @endif

            <!-- History Tab -->
            @if($activeTab === 'history')
                <h3 class="text-md font-semibold mb-3">📈 Evolución NDVI - 90 días</h3>
                @if(count($historicalData) > 0)
                    <div class="h-48 flex items-end justify-between gap-1 bg-gray-50 rounded-lg p-3">
                        @foreach($historicalData as $index => $data)
                            @php
                                $height = ($data['ndvi'] + 1) / 2 * 100;
                                $color = $data['ndvi'] >= 0.5 ? 'bg-green-500' : ($data['ndvi'] >= 0.3 ? 'bg-yellow-500' : 'bg-red-500');
                            @endphp
                            <div class="flex-1 flex flex-col items-center group relative">
                                <div class="absolute bottom-full mb-2 hidden group-hover:block bg-gray-800 text-white text-xs rounded py-1 px-2 z-10">
                                    {{ $data['fullDate'] }}: {{ number_format($data['ndvi'], 3) }}
                                </div>
                                <div class="w-full {{ $color }} rounded-t transition-all hover:opacity-80" style="height: {{ max(10, $height) }}%;"></div>
                            </div>
                        @endforeach
                    </div>
                    <div class="flex justify-between text-xs text-gray-500 mt-1">
                        <span>← 90 días</span>
                        <span>Hoy →</span>
                    </div>
                @else
                    <p class="text-center text-gray-500 py-8">No hay datos históricos</p>
                @endif
            @endif
        </div>

        <!-- Footer Info -->
        <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-3 text-sm text-blue-700 flex items-center gap-2">
            <span>ℹ️</span>
            <span>🛰️ NASA MODIS | 🌦️ Open-Meteo | APIs 100% gratuitas</span>
        </div>
    @else
        <div class="text-center py-16 bg-gray-50 rounded-lg">
            <span class="text-6xl">🛰️</span>
            <p class="text-gray-600 mt-4">Selecciona una parcela para ver el análisis</p>
        </div>
    @endif
</div>
