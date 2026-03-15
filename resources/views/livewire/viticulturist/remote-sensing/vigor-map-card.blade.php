@php
    $areaStats = $areaStats ?? null;
    $vigorZones = $vigorZones ?? null;
    $error = $error ?? null;
    $availableDates = $availableDates ?? [];
@endphp
<div class="bg-white rounded-lg shadow-md p-6">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex-1">
            <h3 class="text-xl font-bold text-zinc-900">🗺️ Mapa de Vigor Intra-Parcela</h3>
            <p class="text-sm text-zinc-500 mt-1">
                Variabilidad espacial dentro de la parcela
            </p>
        </div>
        
        <div class="flex items-center gap-2">
            @if($areaStats && count($availableDates) > 0)
                <flux:select wire:model.live="selectedDate" 
                        >
                    <option value="">Último dato</option>
                    @foreach($availableDates as $date)
                        <option value="{{ $date }}">{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</option>
                    @endforeach
                </flux:select>
            @endif
            
            @if($areaStats)
                <button 
                    wire:click="loadData" 
                    wire:loading.attr="disabled"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50"
                >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Actualizar
            </button>
        @endif
    </div>

    {{-- No Data - Request Button --}}
    @if($error && !$areaStats)
        <div class="text-center py-8">
            <div class="mb-4">
                <svg class="w-16 h-16 mx-auto text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                </svg>
            </div>
            <p class="text-zinc-600 mb-4">{{ $error }}</p>
            <button 
                wire:click="requestAreaData" 
                wire:loading.attr="disabled"
                class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50"
            >
                <svg wire:loading.remove wire:target="requestAreaData" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <svg wire:loading wire:target="requestAreaData" class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Solicitar Mapa de Vigor
            </button>
            <p class="text-xs text-zinc-500 mt-2">⏱️ El procesamiento puede tardar 5-10 minutos</p>
        </div>
    @endif

    {{-- Area Statistics --}}
    @if($areaStats)
        {{-- Stats Grid --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-zinc-50 rounded-lg p-3">
                <span class="text-xs text-zinc-500">Promedio</span>
                <div class="text-xl font-bold text-zinc-900">{{ number_format($areaStats['mean'], 3) }}</div>
            </div>
            <div class="bg-red-50 rounded-lg p-3">
                <span class="text-xs text-zinc-500">Mínimo</span>
                <div class="text-xl font-bold text-red-900">{{ number_format($areaStats['min'], 3) }}</div>
            </div>
            <div class="bg-green-50 rounded-lg p-3">
                <span class="text-xs text-zinc-500">Máximo</span>
                <div class="text-xl font-bold text-green-900">{{ number_format($areaStats['max'], 3) }}</div>
            </div>
            <div class="bg-blue-50 rounded-lg p-3">
                <span class="text-xs text-zinc-500">Desv. Est.</span>
                <div class="text-xl font-bold text-blue-900">{{ number_format($areaStats['stddev'], 3) }}</div>
            </div>
        </div>

        {{-- Variability Analysis --}}
        <div class="mb-6">
            <h4 class="text-lg font-semibold text-zinc-800 mb-3">Análisis de Variabilidad</h4>
            <div class="bg-zinc-50 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm text-zinc-700">Coeficiente de Variación</span>
                    <span class="text-lg font-bold text-zinc-900">{{ number_format($areaStats['coefficient_variation'], 1) }}%</span>
                </div>
                <div class="text-xs text-zinc-600 mb-3">
                    @if($areaStats['coefficient_variation'] < 10)
                        ✅ <strong>Baja variabilidad</strong> - Parcela homogénea
                    @elseif($areaStats['coefficient_variation'] < 20)
                        ⚠️ <strong>Variabilidad moderada</strong> - Revisar zonas bajas
                    @else
                        🚨 <strong>Alta variabilidad</strong> - Problemas localizados detectados
                    @endif
                </div>
                
                {{-- Percentiles --}}
                <div class="text-xs text-zinc-600 space-y-1">
                    <div>P25: {{ number_format($areaStats['percentile_25'], 3) }}</div>
                    <div>P50: {{ number_format($areaStats['percentile_50'], 3) }} (Mediana)</div>
                    <div>P75: {{ number_format($areaStats['percentile_75'], 3) }}</div>
                </div>
            </div>
        </div>

        {{-- Vigor Zones — improvement #5: show real pixel percentages from Sentinel-2 --}}
        @if($vigorZones)
            <div>
                <h4 class="text-lg font-semibold text-zinc-800 mb-3">Zonas de Vigor</h4>
                <div class="space-y-3">
                    @foreach($vigorZones as $key => $zone)
                        <div class="border border-{{ $zone['color'] }}-300 bg-{{ $zone['color'] }}-50 rounded-lg p-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center flex-1">
                                    <span class="text-2xl mr-3">{{ $zone['icon'] }}</span>
                                    <div class="flex-1">
                                        <div class="text-sm font-semibold text-{{ $zone['color'] }}-900">{{ $zone['label'] }}</div>
                                        <div class="text-xs text-{{ $zone['color'] }}-700">{{ $zone['description'] }}</div>
                                        @if(isset($zone['pct']) && $zone['pct'] !== null)
                                            <div class="mt-1.5 h-1.5 bg-{{ $zone['color'] }}-100 rounded-full overflow-hidden">
                                                <div class="h-full bg-{{ $zone['color'] }}-400 rounded-full"
                                                     style="width: {{ min(100, $zone['pct']) }}%"></div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right ml-3">
                                    @if(isset($zone['pct']) && $zone['pct'] !== null)
                                        <div class="text-xl font-bold text-{{ $zone['color'] }}-900">{{ number_format($zone['pct'], 1) }}%</div>
                                        <div class="text-xs text-{{ $zone['color'] }}-600">del recinto</div>
                                    @else
                                        <div class="text-xs text-{{ $zone['color'] }}-600">Rango NDVI</div>
                                        <div class="text-sm font-mono text-{{ $zone['color'] }}-900">
                                            {{ number_format($zone['range']['min'], 2) }} – {{ number_format($zone['range']['max'], 2) }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Pixel Info --}}
        @if(isset($areaStats['pixel_count']))
            <div class="mt-4 text-xs text-zinc-500 text-center">
                📊 Análisis basado en {{ number_format($areaStats['pixel_count']) }} píxeles
            </div>
        @endif
    @endif
</div>
