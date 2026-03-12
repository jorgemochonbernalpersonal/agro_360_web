{{-- Tab Comparar: comparación lado a lado entre dos parcelas --}}

<div class="mb-4">
    <label class="block text-sm font-medium text-zinc-700 mb-2">Selecciona parcela para comparar:</label>
    <flux:select wire:model.live="comparePlotId">
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
        {{-- Parcela actual --}}
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

        {{-- Parcela comparada --}}
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

    {{-- Diferencia NDVI --}}
    @php $ndviDiff = ($ndviData?->ndvi_mean ?? 0) - ($compareNdviData?->ndvi_mean ?? 0); @endphp
    <div class="mt-4 bg-zinc-100 rounded-lg p-4 text-center">
        <span class="text-sm text-zinc-600">Diferencia NDVI:</span>
        <span class="ml-2 font-bold text-lg {{ $ndviDiff > 0 ? 'text-green-600' : ($ndviDiff < 0 ? 'text-red-600' : 'text-zinc-600') }}">
            {{ $ndviDiff > 0 ? '+' : '' }}{{ number_format($ndviDiff, 3) }}
        </span>
        <span class="ml-2 text-sm {{ $ndviDiff > 0 ? 'text-green-600' : ($ndviDiff < 0 ? 'text-red-600' : 'text-zinc-600') }}">
            @if($ndviDiff > 0.05)     ({{ $selectedPlot->name }} mejor)
            @elseif($ndviDiff < -0.05)({{ $comparePlot->name }} mejor)
            @else                      (similares)
            @endif
        </span>
    </div>
@else
    <div class="text-center py-12 bg-zinc-50 rounded-lg">
        <span class="text-4xl">⚖️</span>
        <p class="text-zinc-600 mt-4">Selecciona una parcela para comparar con <strong>{{ $selectedPlot->name }}</strong></p>
    </div>
@endif
