{{-- Tab Suelo: humedad, temperatura y SMAP NASA --}}

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

{{-- Humedad SMAP (NASA) --}}
<div class="mt-6 pt-6 border-t border-zinc-100">
    <h3 class="text-sm font-semibold text-zinc-500 uppercase tracking-wide mb-4">Humedad SMAP (NASA)</h3>
    @livewire('viticulturist.remote-sensing.smap-soil-card', ['plot' => $selectedPlot], key('smap-'.$selectedPlot->id))
</div>
