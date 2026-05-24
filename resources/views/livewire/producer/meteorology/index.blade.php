<div class="space-y-6 animate-fade-in">

<x-agro.page-header
    :title="__('Meteorología')"
    :description="__('Datos meteorológicos en tiempo real de tus parcelas: temperatura, humedad, suelo, radiación y pronóstico.')"
    icon="cloud"
>
    <x-slot:actions>
        <div class="flex items-center gap-2">
            @if($selectedPlot)
                <flux:button wire:click="refreshData" size="sm" variant="ghost" icon="arrow-path">
                    {{ __('Actualizar') }}
                </flux:button>
            @endif
        </div>
    </x-slot:actions>
</x-agro.page-header>

{{-- Selector de parcela --}}
<div class="flex flex-wrap items-end gap-4">
    <flux:field class="min-w-[260px]">
        <flux:label>{{ __('Parcela') }}</flux:label>
        <flux:select wire:model.live="selectedPlot">
            <option value="">{{ __('Seleccionar parcela...') }}</option>
            @foreach($plots as $p)
                <option value="{{ $p->id }}">{{ $p->name }} @if($p->municipality) ({{ $p->municipality->name }}) @endif</option>
            @endforeach
        </flux:select>
    </flux:field>
</div>

{{-- Resumen de todas las parcelas --}}
@if(count($plotSummaries) > 1)
    <div>
        <h3 class="text-sm font-semibold text-zinc-500 uppercase tracking-wider mb-3">{{ __('Vista general') }}</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-3">
            @foreach($plotSummaries as $plotId => $summary)
                <button
                    wire:click="$set('selectedPlot', '{{ $plotId }}')"
                    class="text-left p-3 rounded-xl border transition-all {{ (string) $plotId === $selectedPlot ? 'border-blue-400 bg-blue-50 ring-1 ring-blue-300' : 'border-zinc-200 bg-white hover:border-blue-200 hover:shadow-sm' }}"
                >
                    <p class="text-xs font-semibold text-zinc-700 truncate">{{ $summary['name'] }}</p>
                    @if($summary['temperature'] !== null)
                        <div class="flex items-center gap-2 mt-1.5">
                            <span class="text-lg">{{ \App\Services\RemoteSensing\WeatherService::getWeatherIcon($summary['weather_code'] ?? 0) }}</span>
                            <span class="text-lg font-bold text-zinc-900">{{ round($summary['temperature']) }}°C</span>
                        </div>
                        <div class="flex items-center gap-2 mt-1 text-[10px] text-zinc-400">
                            <span>{{ $summary['humidity'] ?? '--' }}% hum</span>
                            @if(($summary['precipitation'] ?? 0) > 0)
                                <span class="text-blue-500">{{ $summary['precipitation'] }} mm</span>
                            @endif
                        </div>
                    @else
                        <p class="text-xs text-zinc-300 mt-2">{{ __('Sin datos') }}</p>
                    @endif
                </button>
            @endforeach
        </div>
    </div>
@endif

{{-- Datos de la parcela seleccionada --}}
@if(!$selectedPlot)
    <x-agro.empty-state
        icon="cloud"
        :title="__('Selecciona una parcela')"
        :description="__('Elige una parcela para ver sus datos meteorológicos en tiempo real.')"
    />
@elseif($error)
    <x-agro.empty-state
        icon="exclamation-triangle"
        :title="$error"
        :description="__('Intenta de nuevo más tarde o selecciona otra parcela.')"
    />
@else
    {{-- Tiempo actual --}}
    <div>
        <h3 class="text-sm font-semibold text-zinc-500 uppercase tracking-wider mb-3">{{ __('Tiempo actual') }}</h3>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Temperatura --}}
            <div class="bg-gradient-to-br from-orange-50 to-red-50 rounded-2xl p-5 ring-1 ring-orange-200/60">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-orange-700">{{ __('Temperatura') }}</span>
                    <div class="w-7 h-7 rounded-lg bg-orange-500 flex items-center justify-center">
                        <flux:icon icon="fire" class="size-3.5 text-white" />
                    </div>
                </div>
                <p class="text-3xl font-bold text-zinc-900">{{ $weather['temperature'] ?? '--' }}°C</p>
                <p class="text-xs text-zinc-500 mt-1">
                    {{ __('Min') }} {{ $weather['temperature_min'] ?? '--' }}° / {{ __('Max') }} {{ $weather['temperature_max'] ?? '--' }}°
                </p>
            </div>

            {{-- Humedad --}}
            <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-2xl p-5 ring-1 ring-blue-200/60">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-blue-700">{{ __('Humedad') }}</span>
                    <div class="w-7 h-7 rounded-lg bg-blue-500 flex items-center justify-center">
                        <flux:icon icon="eye-dropper" class="size-3.5 text-white" />
                    </div>
                </div>
                <p class="text-3xl font-bold text-zinc-900">{{ $weather['humidity'] ?? '--' }}%</p>
                <p class="text-xs text-zinc-500 mt-1">{{ __('Humedad relativa del aire') }}</p>
            </div>

            {{-- Precipitacion --}}
            <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl p-5 ring-1 ring-indigo-200/60">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-indigo-700">{{ __('Precipitación') }}</span>
                    <div class="w-7 h-7 rounded-lg bg-indigo-500 flex items-center justify-center">
                        <flux:icon icon="cloud" class="size-3.5 text-white" />
                    </div>
                </div>
                <p class="text-3xl font-bold text-zinc-900">{{ $weather['precipitation'] ?? 0 }} mm</p>
                <p class="text-xs text-zinc-500 mt-1">{{ __('Últimas 24 horas') }}</p>
            </div>

            {{-- Viento --}}
            <div class="bg-gradient-to-br from-teal-50 to-emerald-50 rounded-2xl p-5 ring-1 ring-teal-200/60">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-teal-700">{{ __('Viento') }}</span>
                    <div class="w-7 h-7 rounded-lg bg-teal-500 flex items-center justify-center">
                        <flux:icon icon="signal" class="size-3.5 text-white" />
                    </div>
                </div>
                <p class="text-3xl font-bold text-zinc-900">{{ $weather['wind_speed'] ?? '--' }} km/h</p>
                <p class="text-xs text-zinc-500 mt-1">{{ __('Velocidad media') }}</p>
            </div>
        </div>
    </div>

    {{-- Suelo, Solar, Estres hidrico --}}
    <div>
        <h3 class="text-sm font-semibold text-zinc-500 uppercase tracking-wider mb-3">{{ __('Suelo y radiación') }}</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Humedad del suelo --}}
            <x-agro.card>
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center">
                            <flux:icon icon="eye-dropper" class="size-4 text-amber-600" />
                        </div>
                        <span class="text-sm font-semibold text-zinc-800">{{ __('Humedad del suelo') }}</span>
                    </div>
                </div>
                <p class="text-3xl font-bold text-amber-700">{{ $soil['soil_moisture'] ?? '--' }}%</p>
                <div class="w-full bg-zinc-100 rounded-full h-2.5 mt-3">
                    @php $moisture = min(100, $soil['soil_moisture'] ?? 0); @endphp
                    <div class="h-2.5 rounded-full transition-all duration-500 {{ $moisture > 40 ? 'bg-blue-500' : ($moisture > 20 ? 'bg-amber-500' : 'bg-red-500') }}"
                         style="width: {{ $moisture }}%"></div>
                </div>
                <div class="flex items-center justify-between mt-2 text-xs text-zinc-400">
                    <span>{{ __('Seco') }}</span>
                    <span>{{ __('Óptimo') }}</span>
                    <span>{{ __('Saturado') }}</span>
                </div>
                <p class="text-xs text-zinc-500 mt-3">
                    {{ __('Temp. suelo:') }} <span class="font-semibold text-zinc-700">{{ $soil['soil_temperature'] ?? '--' }}°C</span>
                </p>
            </x-agro.card>

            {{-- Radiacion solar --}}
            <x-agro.card>
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-yellow-50 flex items-center justify-center">
                            <flux:icon icon="sun" class="size-4 text-yellow-600" />
                        </div>
                        <span class="text-sm font-semibold text-zinc-800">{{ __('Radiación solar') }}</span>
                    </div>
                </div>
                <p class="text-3xl font-bold text-yellow-600">{{ $solar['solar_radiation'] ?? '--' }} <span class="text-base font-normal text-zinc-400">MJ/m2</span></p>
                <div class="grid grid-cols-2 gap-3 mt-4">
                    <div>
                        <p class="text-xs text-zinc-400">{{ __('Horas de sol') }}</p>
                        <p class="text-lg font-bold text-zinc-800">{{ number_format($solar['sunshine_hours'] ?? 0, 1) }}h</p>
                    </div>
                    <div>
                        <p class="text-xs text-zinc-400">{{ __('ET0 (evapotransp.)') }}</p>
                        <p class="text-lg font-bold text-zinc-800">{{ $solar['et0'] ?? '--' }} {{ __('mm/día') }}</p>
                    </div>
                </div>
            </x-agro.card>

            {{-- Estres hidrico --}}
            <x-agro.card>
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-{{ $waterStress['color'] }}-50 flex items-center justify-center">
                            <flux:icon :icon="$waterStress['icon']" class="size-4 text-{{ $waterStress['color'] }}-600" />
                        </div>
                        <span class="text-sm font-semibold text-zinc-800">{{ __('Estrés hídrico') }}</span>
                    </div>
                </div>
                <p class="text-3xl font-bold text-{{ $waterStress['color'] }}-600">{{ $waterStress['text'] }}</p>
                <p class="text-xs text-zinc-500 mt-2">
                    {{ __('Basado en humedad del suelo') }} ({{ $soil['soil_moisture'] ?? '--' }}%) {{ __('y evapotranspiración') }} ({{ $solar['et0'] ?? '--' }} {{ __('mm/día') }}).
                </p>
                @if($waterStress['status'] === 'severe')
                    <div class="mt-3 p-2 rounded-lg bg-red-50 border border-red-200">
                        <p class="text-xs text-red-700 font-medium">{{ __('Se recomienda riego urgente para evitar daños en la vid.') }}</p>
                    </div>
                @elseif($waterStress['status'] === 'moderate')
                    <div class="mt-3 p-2 rounded-lg bg-orange-50 border border-orange-200">
                        <p class="text-xs text-orange-700 font-medium">{{ __('Considerar riego en las próximas 24-48 horas.') }}</p>
                    </div>
                @endif
            </x-agro.card>
        </div>
    </div>

    {{-- Pronostico 7 dias --}}
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                        <flux:icon icon="calendar-days" class="size-4 text-blue-600" />
                    </div>
                    <span class="font-semibold text-zinc-900">{{ __('Pronóstico 7 días') }}</span>
                </div>
                <flux:button wire:click="toggleForecast" size="sm" variant="ghost">
                    {{ $showForecast ? __('Ocultar') : __('Mostrar') }}
                </flux:button>
            </div>
        </x-slot:header>

        @if($showForecast && count($forecast) > 0)
            <div class="grid grid-cols-7 gap-2 mt-2">
                @foreach($forecast as $day)
                    <div class="text-center p-3 rounded-xl bg-gradient-to-b from-white to-zinc-50 ring-1 ring-zinc-100">
                        <p class="text-xs font-semibold text-zinc-500 uppercase">
                            {{ \Carbon\Carbon::parse($day['date'])->locale('es')->isoFormat('ddd') }}
                        </p>
                        <p class="text-[10px] text-zinc-400">
                            {{ \Carbon\Carbon::parse($day['date'])->format('d/m') }}
                        </p>
                        <div class="text-2xl my-2">
                            {{ \App\Services\RemoteSensing\WeatherService::getWeatherIcon($day['weather_code'] ?? 0) }}
                        </div>
                        <p class="text-xs text-zinc-500">
                            {{ \App\Services\RemoteSensing\WeatherService::getWeatherDescription($day['weather_code'] ?? 0) }}
                        </p>
                        <div class="mt-2">
                            <span class="text-sm font-bold text-red-500">{{ round($day['temp_max'] ?? 0) }}°</span>
                            <span class="text-xs text-zinc-300">/</span>
                            <span class="text-sm font-bold text-blue-500">{{ round($day['temp_min'] ?? 0) }}°</span>
                        </div>
                        @if(($day['precipitation'] ?? 0) > 0)
                            <p class="text-[10px] text-blue-600 font-medium mt-1">{{ round($day['precipitation']) }} mm</p>
                        @endif
                        @if(($day['wind_speed'] ?? 0) > 20)
                            <p class="text-[10px] text-teal-600 mt-0.5">{{ round($day['wind_speed']) }} km/h</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @elseif(!$showForecast)
            <p class="text-sm text-zinc-400 text-center py-4">{{ __('Pulsa "Mostrar" para ver el pronóstico de los próximos 7 días.') }}</p>
        @else
            <p class="text-sm text-zinc-400 text-center py-4">{{ __('Sin datos de pronóstico disponibles.') }}</p>
        @endif
    </x-agro.card>

    {{-- Mock data indicator --}}
    @if(isset($weather['mock']) && $weather['mock'])
        <div class="flex items-center justify-center gap-2 text-xs text-amber-600 bg-amber-50 rounded-xl p-2">
            <flux:icon icon="information-circle" class="size-4" />
            <span>{{ __('Datos de demostración — activa la API de Open-Meteo en producción para datos reales.') }}</span>
        </div>
    @endif
@endif

</div>
