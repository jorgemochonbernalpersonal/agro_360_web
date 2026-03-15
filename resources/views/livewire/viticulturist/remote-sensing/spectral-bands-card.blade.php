@php
    $spectralData = $spectralData ?? null;
    $indices = $indices ?? null;
    $loading = $loading ?? false;
    $error = $error ?? null;
    $availableDates = $availableDates ?? [];
@endphp
<div class="bg-white rounded-lg shadow-md p-6">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex-1">
            <h3 class="text-xl font-bold text-zinc-900">🌈 Bandas Espectrales</h3>
            <p class="text-sm text-zinc-500 mt-1">
                Reflectancias reales del satélite
                @if($spectralData)
                    - {{ $spectralData['date'] }} ({{ $spectralData['satellite'] }})
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
    @if($loading && !$spectralData)
        <div class="flex items-center justify-center py-12">
            <div class="text-center">
                <svg class="animate-spin h-12 w-12 text-blue-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-zinc-600">Cargando bandas espectrales...</p>
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
    @if($spectralData)
        {{-- Bandas Crudas --}}
        <div class="mb-6">
            <h4 class="text-lg font-semibold text-zinc-800 mb-3">📊 Reflectancias por Banda</h4>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
                    <div class="text-xs text-red-600 font-medium mb-1">RED</div>
                    <div class="text-2xl font-bold text-red-900">{{ number_format($spectralData['red'], 4) }}</div>
                    <div class="text-xs text-red-600 mt-1">640nm</div>
                </div>

                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 text-center">
                    <div class="text-xs text-purple-600 font-medium mb-1">NIR</div>
                    <div class="text-2xl font-bold text-purple-900">{{ number_format($spectralData['nir'], 4) }}</div>
                    <div class="text-xs text-purple-600 mt-1">865nm</div>
                </div>

                <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
                    <div class="text-xs text-green-600 font-medium mb-1">GREEN</div>
                    <div class="text-2xl font-bold text-green-900">{{ number_format($spectralData['green'], 4) }}</div>
                    <div class="text-xs text-green-600 mt-1">555nm</div>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
                    <div class="text-xs text-blue-600 font-medium mb-1">BLUE</div>
                    <div class="text-2xl font-bold text-blue-900">{{ number_format($spectralData['blue'], 4) }}</div>
                    <div class="text-xs text-blue-600 mt-1">488nm</div>
                </div>
            </div>
        </div>

        {{-- Índices Vegetación --}}
        <div>
            <h4 class="text-lg font-semibold text-zinc-800 mb-3">📈 Índices de Vegetación</h4>
            <div class="space-y-3">
                @foreach($indices as $key => $index)
                    @if($index['value'])
                        <div class="bg-{{ $index['color'] }}-50 border border-{{ $index['color'] }}-200 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-lg font-bold text-{{ $index['color'] }}-900">{{ $index['label'] }}</span>
                                        @if(isset($index['is_real']) && $index['is_real'])
                                            <span class="px-2 py-0.5 bg-green-100 text-green-800 text-xs font-semibold rounded">
                                                ✓ REAL
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-{{ $index['color'] }}-700 mt-1">{{ $index['description'] }}</p>
                                </div>
                                <div class="text-right">
                                    <div class="text-3xl font-bold text-{{ $index['color'] }}-900">
                                        {{ number_format($index['value'], 3) }}
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Progress bar --}}
                            <div class="mt-3 relative h-2 bg-zinc-200 rounded-full overflow-hidden">
                                <div class="absolute top-0 left-0 h-full bg-{{ $index['color'] }}-500" 
                                     style="width: {{ min(100, max(0, (($index['value'] + 1) / 2) * 100)) }}%"></div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Info Box --}}
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start">
                <span class="text-2xl mr-3">ℹ️</span>
                <div class="flex-1">
                    <h4 class="text-sm font-semibold text-blue-900">Datos 100% Reales</h4>
                    <p class="text-xs text-blue-700 mt-1">
                        Las bandas espectrales provienen directamente del satélite {{ $spectralData['satellite'] }}.
                        Los índices marcados como "REAL" se calculan desde bandas satelitales (no estimaciones).
                    </p>
                </div>
            </div>
        </div>
    @endif
</div>
