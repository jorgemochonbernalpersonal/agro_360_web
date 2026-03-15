@php
    $lstData = $lstData ?? null;
    $cwsiData = $cwsiData ?? null;
    $heatStress = $heatStress ?? null;
    $frostRisk = $frostRisk ?? null;
    $loading = $loading ?? false;
    $error = $error ?? null;
    $availableDates = $availableDates ?? [];
@endphp
<div class="bg-white rounded-lg shadow-md p-6">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex-1">
            <h3 class="text-xl font-bold text-zinc-900">🌡️ Análisis Térmico</h3>
            <p class="text-sm text-zinc-500 mt-1">
                Temperatura de superficie y estrés
                @if($lstData)
                    - {{ $lstData['date'] }}
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

    {{-- Loading --}}
    @if($loading && !$lstData)
        <div class="flex items-center justify-center py-12">
            <div class="text-center">
                <svg class="animate-spin h-12 w-12 text-blue-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-zinc-600">Cargando datos térmicos...</p>
            </div>
        </div>
    @endif

    {{-- Error --}}
    @if($error)
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-yellow-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"/>
                </svg>
                <p class="text-sm text-yellow-800">{{ $error }}</p>
            </div>
        </div>
    @endif

    {{-- Content --}}
    @if($lstData)
        {{-- LST Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            {{-- Day Temperature --}}
            <div class="bg-gradient-to-br from-orange-50 to-red-50 rounded-lg p-4 border border-orange-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-zinc-700">Temp. Día</span>
                    <span class="text-2xl">☀️</span>
                </div>
                <div class="text-3xl font-bold text-zinc-900">{{ number_format($lstData['day'], 1) }}°C</div>
                <p class="text-xs text-zinc-600 mt-1">Temperatura de superficie</p>
            </div>

            {{-- Night Temperature --}}
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg p-4 border border-blue-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-zinc-700">Temp. Noche</span>
                    <span class="text-2xl">🌙</span>
                </div>
                <div class="text-3xl font-bold text-zinc-900">{{ number_format($lstData['night'], 1) }}°C</div>
                <p class="text-xs text-zinc-600 mt-1">Mínima nocturna</p>
            </div>

            {{-- Temperature Difference --}}
            <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-lg p-4 border border-purple-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-zinc-700">Amplitud Térmica</span>
                    <span class="text-2xl">📊</span>
                </div>
                <div class="text-3xl font-bold text-zinc-900">{{ number_format($lstData['diff'], 1) }}°C</div>
                <p class="text-xs text-zinc-600 mt-1">Diferencia día-noche</p>
            </div>
        </div>

        {{-- CWSI Section --}}
        @if($cwsiData)
            <div class="mb-6">
                <h4 class="text-lg font-semibold text-zinc-800 mb-3">Índice de Estrés Hídrico (CWSI)</h4>
                <div class="bg-{{ $cwsiData['color'] }}-50 border border-{{ $cwsiData['color'] }}-200 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center">
                            <span class="text-3xl mr-3">{{ $cwsiData['icon'] }}</span>
                            <div>
                                <div class="text-lg font-bold text-{{ $cwsiData['color'] }}-900">{{ $cwsiData['label'] }}</div>
                                <div class="text-sm text-{{ $cwsiData['color'] }}-700">{{ $cwsiData['description'] }}</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-3xl font-bold text-{{ $cwsiData['color'] }}-900">{{ number_format($cwsiData['value'], 2) }}</div>
                            <div class="text-xs text-{{ $cwsiData['color'] }}-600">CWSI</div>
                        </div>
                    </div>
                    
                    {{-- CWSI Bar --}}
                    <div class="relative h-3 bg-zinc-200 rounded-full overflow-hidden">
                        <div class="absolute top-0 left-0 h-full bg-gradient-to-r from-green-500 via-yellow-500 via-orange-500 to-red-500" 
                             style="width: {{ min(100, $cwsiData['value'] * 100) }}%"></div>
                    </div>
                    <div class="flex justify-between text-xs text-zinc-600 mt-1">
                        <span>0 (Sin estrés)</span>
                        <span>1 (Estrés máximo)</span>
                    </div>
                </div>
            </div>
        @endif

        {{-- Heat Stress Alert --}}
        @if($heatStress && $heatStress['detected'])
            <div class="bg-red-50 border border-red-300 rounded-lg p-4 mb-4">
                <div class="flex items-start">
                    <span class="text-3xl mr-3">🔥</span>
                    <div class="flex-1">
                        <div class="flex items-center mb-2">
                            <h4 class="text-lg font-bold text-red-900">Estrés Térmico Detectado</h4>
                            <span class="ml-2 px-2 py-0.5 text-xs font-medium rounded bg-red-200 text-red-900">
                                {{ strtoupper($heatStress['severity']) }}
                            </span>
                        </div>
                        <p class="text-sm text-red-800 mb-2">
                            Temperatura de superficie: <strong>{{ number_format($heatStress['lst_day'], 1) }}°C</strong>
                            ({{ number_format($heatStress['excess'], 1) }}°C sobre umbral de {{ $heatStress['threshold'] }}°C)
                        </p>
                        <div class="bg-red-100 rounded p-2 text-sm text-red-900">
                            <strong>Recomendación:</strong> {{ $heatStress['recommendation'] }}
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Frost Risk Alert --}}
        @if($frostRisk && $frostRisk['detected'])
            <div class="bg-blue-50 border border-blue-300 rounded-lg p-4 mb-4">
                <div class="flex items-start">
                    <span class="text-3xl mr-3">❄️</span>
                    <div class="flex-1">
                        <div class="flex items-center mb-2">
                            <h4 class="text-lg font-bold text-blue-900">Riesgo de Helada</h4>
                            <span class="ml-2 px-2 py-0.5 text-xs font-medium rounded bg-blue-200 text-blue-900">
                                {{ strtoupper($frostRisk['severity']) }}
                            </span>
                        </div>
                        <p class="text-sm text-blue-800 mb-2">
                            Temperatura nocturna: <strong>{{ number_format($frostRisk['lst_night'], 1) }}°C</strong>
                        </p>
                        <p class="text-sm text-blue-800 mb-2">
                            <strong>{{ $frostRisk['risk_level'] }}</strong> - {{ $frostRisk['phenological_risk'] }}
                        </p>
                        <div class="bg-blue-100 rounded p-2 text-sm text-blue-900">
                            <strong>Recomendación:</strong> {{ $frostRisk['recommendation'] }}
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Info Box --}}
        @if(!$heatStress && !$frostRisk && $lstData)
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center">
                    <span class="text-2xl mr-3">✅</span>
                    <div>
                        <h4 class="text-sm font-semibold text-green-900">Condiciones Térmicas Normales</h4>
                        <p class="text-xs text-green-700 mt-1">No se detectaron riesgos térmicos ni estrés por temperatura</p>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
