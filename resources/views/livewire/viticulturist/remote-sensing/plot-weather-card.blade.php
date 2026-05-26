<div class="bg-white rounded-lg shadow-lg p-6 mt-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-agro-700 flex items-center gap-2">
            🌦️ {{ __('Datos Meteorológicos') }}
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
            {{ __('Actualizar') }}
        </button>
    </div>

    @if($error)
        <div class="p-4 bg-red-50 rounded-lg text-red-700 mb-4">
            <p>{{ $error }}</p>
        </div>
    @elseif($isLoading)
        <div class="flex items-center justify-center py-8">
            <svg class="w-8 h-8 animate-spin text-blue-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span class="ml-2 text-zinc-600">{{ __('Cargando datos meteorológicos...') }}</span>
        </div>
    @else
        <!-- Weather Cards Grid -->
        <div class="grid grid-cols-2 gap-4 mb-4">
            <!-- Temperature -->
            <div class="bg-gradient-to-br from-orange-50 to-red-50 rounded-lg p-4 border border-orange-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-semibold text-zinc-600">{{ __('Temperatura') }}</span>
                    <span class="text-xl">{{ __('🌡️') }}</span>
                </div>
                <div class="text-2xl font-bold text-orange-600">
                    {{ $weather['temperature'] ?? '--' }}°C
                </div>
                <div class="text-xs text-zinc-500 mt-1">
                    Min: {{ $weather['temperature_min'] ?? '--' }}° / Max: {{ $weather['temperature_max'] ?? '--' }}°
                </div>
            </div>

            <!-- Humidity -->
            <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-lg p-4 border border-blue-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-semibold text-zinc-600">{{ __('Humedad') }}</span>
                    <span class="text-xl">💧</span>
                </div>
                <div class="text-2xl font-bold text-blue-600">
                    {{ $weather['humidity'] ?? '--' }}%
                </div>
                <div class="text-xs text-zinc-500 mt-1">
                    {{ __('Relativa del aire') }}
                </div>
            </div>

            <!-- Precipitation -->
            <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-lg p-4 border border-indigo-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-semibold text-zinc-600">{{ __('Precipitación') }}</span>
                    <span class="text-xl">{{ __('🌧️') }}</span>
                </div>
                <div class="text-2xl font-bold text-indigo-600">
                    {{ $weather['precipitation'] ?? 0 }} mm
                </div>
                <div class="text-xs text-zinc-500 mt-1">
                    {{ __('Últimas 24h') }}
                </div>
            </div>

            <!-- Wind -->
            <div class="bg-gradient-to-br from-teal-50 to-emerald-50 rounded-lg p-4 border border-teal-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-semibold text-zinc-600">{{ __('Viento') }}</span>
                    <span class="text-xl">💨</span>
                </div>
                <div class="text-2xl font-bold text-teal-600">
                    {{ $weather['wind_speed'] ?? '--' }} km/h
                </div>
                <div class="text-xs text-zinc-500 mt-1">
                    {{ __('Velocidad media') }}
                </div>
            </div>
        </div>

        <!-- Soil & Solar Row -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <!-- Soil Moisture -->
            <div class="bg-gradient-to-br from-amber-50 to-yellow-50 rounded-lg p-4 border border-amber-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-semibold text-zinc-600">{{ __('Humedad Suelo') }}</span>
                    <span class="text-xl">🌱</span>
                </div>
                <div class="text-2xl font-bold text-amber-700">
                    {{ $soil['soil_moisture'] ?? '--' }}%
                </div>
                <div class="w-full bg-zinc-200 rounded-full h-2 mt-2">
                    <div class="h-2 rounded-full bg-gradient-to-r from-amber-300 to-amber-600"
                         style="width: {{ min(100, $soil['soil_moisture'] ?? 0) }}%"></div>
                </div>
                <div class="text-xs text-zinc-500 mt-1">
                    {{ __('Temp. suelo:') }} {{ $soil['soil_temperature'] ?? '--' }}°C
                </div>
            </div>

            <!-- Solar Radiation -->
            <div class="bg-gradient-to-br from-yellow-50 to-orange-50 rounded-lg p-4 border border-yellow-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-semibold text-zinc-600">{{ __('Radiación Solar') }}</span>
                    <span class="text-xl">☀️</span>
                </div>
                <div class="text-2xl font-bold text-yellow-600">
                    {{ $solar['solar_radiation'] ?? '--' }} MJ/m²
                </div>
                <div class="text-xs text-zinc-500 mt-1">
                    {{ $solar['sunshine_hours'] ?? '--' }}{{ __('h de sol') }}
                </div>
            </div>

            <!-- Water Stress -->
            <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-4 border border-green-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-semibold text-zinc-600">{{ __('Estrés Hídrico') }}</span>
                    <span class="text-xl">{{ $waterStress['emoji'] }}</span>
                </div>
                <div class="text-2xl font-bold {{ $waterStress['color'] }}">
                    {{ $waterStress['text'] }}
                </div>
                <div class="text-xs text-zinc-500 mt-1">
                    ET0: {{ $solar['et0'] ?? '--' }} mm/{{ __('día') }}
                </div>
            </div>
        </div>

        <!-- Forecast Toggle -->
        <div class="flex justify-between items-center">
            <button wire:click="toggleForecast"
                    class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                {{ $showForecast ? __('Ocultar pronóstico') : __('Ver pronóstico 7 días') }}
            </button>

            @if(isset($weather['mock']) && $weather['mock'])
                <span class="text-xs text-amber-600 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ __('Datos de demostración') }}
                </span>
            @endif
        </div>

        <!-- 7-Day Forecast -->
        @if($showForecast && count($forecast) > 0)
            <div class="mt-4 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
                <h3 class="text-sm font-semibold text-zinc-700 mb-3">📅 {{ __('Pronóstico 7 días') }}</h3>
                <div class="grid grid-cols-7 gap-2">
                    @foreach($forecast as $day)
                        <div class="text-center p-2 bg-white rounded-lg shadow-sm">
                            <div class="text-xs text-zinc-500">
                                {{ \Carbon\Carbon::parse($day['date'])->locale('es')->isoFormat('ddd') }}
                            </div>
                            <div class="text-xl my-1">
                                {{ \App\Services\RemoteSensing\WeatherService::getWeatherIcon($day['weather_code'] ?? 0) }}
                            </div>
                            <div class="text-xs font-bold">
                                <span class="text-red-500">{{ round($day['temp_max'] ?? 0) }}°</span>
                                <span class="text-blue-500">{{ round($day['temp_min'] ?? 0) }}°</span>
                            </div>
                            @if(($day['precipitation'] ?? 0) > 0)
                                <div class="text-xs text-blue-600 mt-1">
                                    💧 {{ round($day['precipitation']) }}mm
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif
</div>
