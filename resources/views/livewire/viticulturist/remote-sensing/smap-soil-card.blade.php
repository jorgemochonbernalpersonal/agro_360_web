@php
    $smapData = $smapData ?? null;
    $error = $error ?? null;
    $comparison = $comparison ?? null;
    $availableDates = $availableDates ?? [];
@endphp
<div class="bg-white rounded-lg shadow-md p-6">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex-1">
            <h3 class="text-xl font-bold text-zinc-900">🛰️ SMAP - Humedad Suelo Satelital</h3>
            <p class="text-sm text-zinc-500 mt-1">
                Medición directa desde satélite (9km resolución)
                @if($smapData)
                    - {{ $smapData['date'] }}
                @endif
            </p>
        </div>
        
        <div class="flex items-center gap-2">
            @if(count($availableDates) > 0)
                <flux:select wire:model.live="selectedDate" 
                        >
                    <option value="">Último dato</option>
                    @foreach($availableDates as $date)
                        <option value="{{ $date }}">{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</option>
                    @endforeach
                </flux:select>
            @endif
            
            <button 
                wire:click="loadData" 
                wire:loading.attr="disabled"
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50"
            >
                <svg wire:loading.remove wire:target="loadData" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <svg wire:loading wire:target="loadData" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Actualizar
            </button>
        </div>
    </div>

    {{-- Error --}}
    @if($error)
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <p class="text-sm text-yellow-800">{{ $error }}</p>
        </div>
    @endif

    {{-- Content --}}
    @if($smapData)
        {{-- Soil Moisture Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            {{-- Surface --}}
            <div class="bg-gradient-to-br from-{{ $smapData['surface_status']['color'] }}-50 to-{{ $smapData['surface_status']['color'] }}-100 rounded-lg p-5 border border-{{ $smapData['surface_status']['color'] }}-200">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <div class="text-sm text-zinc-600">Superficie (0-5cm)</div>
                        <div class="text-4xl font-bold text-{{ $smapData['surface_status']['color'] }}-900">
                            {{ number_format($smapData['surface'], 1) }}%
                        </div>
                    </div>
                    <span class="text-4xl">{{ $smapData['surface_status']['icon'] }}</span>
                </div>
                
                <div class="bg-white rounded p-2">
                    <div class="text-xs text-zinc-600">Estado:</div>
                    <div class="font-semibold text-{{ $smapData['surface_status']['color'] }}-900">
                        {{ $smapData['surface_status']['label'] }}
                    </div>
                    <div class="text-xs text-zinc-600 mt-1">
                        {{ $smapData['surface_status']['description'] }}
                    </div>
                </div>
                
                {{-- Progress bar --}}
                <div class="mt-3 relative h-3 bg-zinc-200 rounded-full overflow-hidden">
                    <div class="absolute top-0 left-0 h-full bg-{{ $smapData['surface_status']['color'] }}-500" 
                         style="width: {{ min(100, $smapData['surface']) }}%"></div>
                </div>
            </div>

            {{-- Rootzone --}}
            <div class="bg-gradient-to-br from-{{ $smapData['rootzone_status']['color'] }}-50 to-{{ $smapData['rootzone_status']['color'] }}-100 rounded-lg p-5 border border-{{ $smapData['rootzone_status']['color'] }}-200">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <div class="text-sm text-zinc-600">Zona Radicular</div>
                        <div class="text-4xl font-bold text-{{ $smapData['rootzone_status']['color'] }}-900">
                            {{ number_format($smapData['rootzone'], 1) }}%
                        </div>
                    </div>
                    <span class="text-4xl">{{ $smapData['rootzone_status']['icon'] }}</span>
                </div>
                
                <div class="bg-white rounded p-2">
                    <div class="text-xs text-zinc-600">Estado:</div>
                    <div class="font-semibold text-{{ $smapData['rootzone_status']['color'] }}-900">
                        {{ $smapData['rootzone_status']['label'] }}
                    </div>
                    <div class="text-xs text-zinc-600 mt-1">
                        {{ $smapData['rootzone_status']['description'] }}
                    </div>
                </div>
                
                {{-- Progress bar --}}
                <div class="mt-3 relative h-3 bg-zinc-200 rounded-full overflow-hidden">
                    <div class="absolute top-0 left-0 h-full bg-{{ $smapData['rootzone_status']['color'] }}-500" 
                         style="width: {{ min(100, $smapData['rootzone']) }}%"></div>
                </div>
            </div>
        </div>

        {{-- Recommendation --}}
        <div class="bg-{{ $smapData['surface_status']['color'] }}-50 border border-{{ $smapData['surface_status']['color'] }}-200 rounded-lg p-4 mb-6">
            <h4 class="text-sm font-semibold text-{{ $smapData['surface_status']['color'] }}-900 mb-2">
                💡 Recomendación de Riego
            </h4>
            <p class="text-sm text-{{ $smapData['surface_status']['color'] }}-800">
                {{ $smapData['surface_status']['recommendation'] }}
            </p>
        </div>

        {{-- Comparison with Model --}}
        @if($comparison)
            <div class="bg-zinc-50 border border-zinc-200 rounded-lg p-4">
                <h4 class="text-sm font-semibold text-zinc-900 mb-3">
                    📊 Comparación: Satélite vs Modelo
                </h4>
                
                <div class="grid grid-cols-2 gap-4 mb-3">
                    <div class="text-center">
                        <div class="text-xs text-zinc-600">SMAP (Satélite)</div>
                        <div class="text-2xl font-bold text-blue-900">{{ number_format($comparison['smap_value'], 1) }}%</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xs text-zinc-600">Open-Meteo (Modelo)</div>
                        <div class="text-2xl font-bold text-zinc-700">{{ number_format($comparison['model_value'], 1) }}%</div>
                    </div>
                </div>
                
                <div class="bg-white rounded p-3">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs text-zinc-600">Diferencia:</span>
                        <span class="font-semibold text-zinc-900">{{ number_format($comparison['difference'], 1) }}%</span>
                    </div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs text-zinc-600">Estado:</span>
                        <span class="px-2 py-0.5 text-xs font-semibold rounded 
                            @if($comparison['reliability'] === 'high') bg-green-100 text-green-800
                            @elseif($comparison['reliability'] === 'medium') bg-yellow-100 text-yellow-800
                            @else bg-red-100 text-red-800
                            @endif">
                            {{ $comparison['message'] }}
                        </span>
                    </div>
                    <div class="text-xs text-zinc-600 mt-2 pt-2 border-t">
                        <strong>Recomendación:</strong> {{ $comparison['recommendation'] }}
                    </div>
                </div>
            </div>
        @endif

        {{-- Info --}}
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-3">
            <div class="flex items-start">
                <span class="text-lg mr-2">ℹ️</span>
                <div class="text-xs text-blue-700">
                    <strong>SMAP (Soil Moisture Active Passive)</strong> - Satélite NASA dedicado a medir humedad del suelo.
                    Resolución 9km (promedio regional). <strong>±5-10% precisión</strong> vs ±15% modelos meteorológicos.
                </div>
            </div>
        </div>
    @endif
</div>
