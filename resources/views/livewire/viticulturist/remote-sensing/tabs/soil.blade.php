{{-- Tab Suelo: humedad, temperatura y Open-Meteo --}}

<div class="grid grid-cols-3 gap-3">
    {{-- Humedad Suelo --}}
    <div class="bg-zinc-50 rounded-xl p-4 border border-zinc-100">
        <div class="text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1">{{ __('Humedad Suelo') }}</div>
        <div class="text-2xl font-bold text-amber-600">{{ $soil['soil_moisture'] ?? '—' }}%</div>
        <div class="mt-2 h-1.5 bg-zinc-200 rounded-full overflow-hidden">
            <div class="bg-amber-400 h-full rounded-full" style="width: {{ min(100, $soil['soil_moisture'] ?? 0) }}%"></div>
        </div>
        <div class="text-xs text-zinc-400 mt-1">{{ __('volumétrico') }}</div>
    </div>

    {{-- Temp. Suelo --}}
    <div class="bg-zinc-50 rounded-xl p-4 border border-zinc-100">
        <div class="text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1">{{ __('Temp. Suelo') }}</div>
        <div class="text-2xl font-bold text-orange-600">
            {{ $soil['soil_temperature'] !== null ? number_format($soil['soil_temperature'], 1).'°C' : '—' }}
        </div>
        <div class="text-xs text-zinc-400 mt-1">{{ __('superficie') }}</div>
    </div>

    {{-- Estrés Hídrico --}}
    <div class="bg-zinc-50 rounded-xl p-4 border border-zinc-100">
        <div class="text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1">{{ __('Estrés Hídrico') }}</div>
        <div class="text-2xl font-bold {{ $waterStress['color'] }}">{{ $waterStress['text'] }}</div>
        <div class="text-xs text-zinc-400 mt-1">{{ __('índice calculado') }}</div>
    </div>
</div>

{{-- Humedad de Suelo (Open-Meteo) --}}
<div class="mt-6 pt-6 border-t border-zinc-100">
    <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wide mb-4">{{ __('Humedad de Suelo · Open-Meteo') }}</p>
    @livewire('viticulturist.remote-sensing.smap-soil-card', ['plot' => $selectedPlot, 'sigpacId' => $selectedSigpacId], key('smap-'.$selectedPlot->id.'-'.$selectedSigpacId))
</div>
