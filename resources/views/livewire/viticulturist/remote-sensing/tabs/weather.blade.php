{{-- Tab Clima: tiempo actual, pronóstico 7 días y radiación solar / ET0 --}}

<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
    <div class="bg-gradient-to-br from-orange-50 to-red-50 rounded-lg p-4 border border-orange-200 text-center">
        <span class="text-2xl">🌡️</span>
        <div class="text-2xl font-bold text-orange-600">{{ $weather['temperature'] ?? '--' }}°C</div>
        <div class="text-xs text-zinc-500">{{ $weather['temperature_min'] ?? '--' }}° / {{ $weather['temperature_max'] ?? '--' }}°</div>
    </div>
    <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-lg p-4 border border-blue-200 text-center">
        <span class="text-2xl">💧</span>
        <div class="text-2xl font-bold text-blue-600">{{ $weather['humidity'] ?? '--' }}%</div>
        <div class="text-xs text-zinc-500">{{ __('Humedad') }}</div>
    </div>
    <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-lg p-4 border border-indigo-200 text-center">
        <span class="text-2xl">🌧️</span>
        <div class="text-2xl font-bold text-indigo-600">{{ $weather['precipitation'] ?? 0 }}mm</div>
        <div class="text-xs text-zinc-500">{{ __('Precipitación') }}</div>
    </div>
    <div class="bg-gradient-to-br from-teal-50 to-emerald-50 rounded-lg p-4 border border-teal-200 text-center">
        <span class="text-2xl">💨</span>
        <div class="text-2xl font-bold text-teal-600">{{ $weather['wind_speed'] ?? '--' }}km/h</div>
        <div class="text-xs text-zinc-500">{{ __('Viento') }}</div>
    </div>
</div>

{{-- Pronóstico 7 días --}}
<h3 class="text-md font-semibold mb-3">📅 {{ __('Pronóstico 7 días') }}</h3>
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

{{-- Radiación Solar & ET0 --}}
<div class="mt-6 pt-6 border-t border-zinc-100 mb-4">
    <h3 class="text-sm font-semibold text-zinc-500 uppercase tracking-wide mb-4">{{ __('Radiación & Evapotranspiración') }}</h3>
</div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="bg-gradient-to-br from-yellow-50 to-orange-50 rounded-lg p-5 border border-yellow-200">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-semibold text-zinc-600">{{ __('Radiación Solar') }}</span>
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
        <p class="text-xs text-zinc-500 mt-1">mm/{{ __('día') }}</p>
    </div>
    <div class="bg-gradient-to-br from-amber-50 to-yellow-50 rounded-lg p-5 border border-amber-200">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-semibold text-zinc-600">{{ __('Horas de Sol') }}</span>
            <span class="text-2xl">🌤️</span>
        </div>
        <div class="text-4xl font-bold text-amber-600">{{ round($solar['sunshine_hours'] ?? 0, 1) }}h</div>
    </div>
</div>
