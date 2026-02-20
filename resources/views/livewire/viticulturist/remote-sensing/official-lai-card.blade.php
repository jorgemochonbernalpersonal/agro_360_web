@php
    $laiData = $laiData ?? null;
    $error = $error ?? null;
    $yieldEstimate = $yieldEstimate ?? null;
    $fparData = $fparData ?? null;
    $availableDates = $availableDates ?? [];
@endphp
<div class="bg-white rounded-lg shadow-md p-6">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex-1">
            <h3 class="text-xl font-bold text-zinc-900">🌿 LAI Oficial NASA</h3>
            <p class="text-sm text-zinc-500 mt-1">
                Leaf Area Index + Eficiencia Fotosintética
                @if($laiData)
                    - {{ $laiData['date'] }}
                @endif
            </p>
        </div>
        
        <div class="flex items-center gap-2">
            {{-- Date Selector --}}
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
                wire:click="refresh" 
                wire:loading.attr="disabled"
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50"
            >
                <svg wire:loading.remove wire:target="refresh" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <svg wire:loading wire:target="refresh" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
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
    @if($laiData)
        {{-- LAI Principal --}}
        <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-6 mb-6 border border-green-200">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <div class="flex items-center gap-3">
                        <span class="text-4xl">{{ $laiData['icon'] }}</span>
                        <div>
                            <div class="text-sm text-zinc-600">LAI Oficial</div>
                            <div class="text-4xl font-bold text-{{ $laiData['color'] }}-700">
                                {{ number_format($laiData['value'], 2) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <span class="px-3 py-1 bg-{{ $laiData['color'] }}-100 text-{{ $laiData['color'] }}-800 rounded-full text-sm font-semibold">
                        {{ $laiData['label'] }}
                    </span>
                </div>
            </div>
            
            <p class="text-sm text-zinc-700 mb-3">{{ $laiData['description'] }}</p>
            
            <div class="bg-white rounded p-3">
                <div class="text-xs text-zinc-600 mb-1">Recomendación:</div>
                <div class="text-sm font-medium text-zinc-900">{{ $laiData['recommendation'] }}</div>
            </div>
        </div>

        {{-- Yield Estimate --}}
        @if($yieldEstimate)
            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-6">
                <h4 class="text-lg font-semibold text-purple-900 mb-3 flex items-center gap-2">
                    <span>🍇</span>
                    <span>Estimación de Rendimiento</span>
                </h4>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-white rounded-lg p-3">
                        <div class="text-xs text-zinc-600">Por Hectárea</div>
                        <div class="text-2xl font-bold text-purple-900">
                            {{ number_format($yieldEstimate['yield_per_ha'], 2) }} <span class="text-sm">t/ha</span>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg p-3">
                        <div class="text-xs text-zinc-600">Total Parcela</div>
                        <div class="text-2xl font-bold text-purple-900">
                            {{ number_format($yieldEstimate['total_yield_kg'], 0) }} <span class="text-sm">kg</span>
                        </div>
                    </div>
                </div>
                
                <div class="mt-3 text-xs text-purple-700">
                    <span class="font-semibold">Confianza:</span>
                    @if($yieldEstimate['confidence'] === 'high')
                        <span class="text-green-700">Alta ✓</span>
                    @elseif($yieldEstimate['confidence'] === 'medium')
                        <span class="text-yellow-700">Media</span>
                    @else
                        <span class="text-orange-700">Baja</span>
                    @endif
                </div>
            </div>
        @endif

        {{-- FPAR --}}
        @if($fparData)
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <h4 class="text-lg font-semibold text-yellow-900 mb-3">☀️ FPAR - Eficiencia Fotosintética</h4>
                
                <div class="flex items-center justify-between mb-3">
                    <div class="flex-1">
                        <div class="text-sm text-yellow-700">{{ $fparData['label'] }}</div>
                        <p class="text-xs text-yellow-600 mt-1">{{ $fparData['description'] }}</p>
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-bold text-yellow-900">{{ number_format($fparData['fpar'], 3) }}</div>
                        <div class="text-xs text-yellow-600">{{ $fparData['photosynthetic_efficiency'] }}</div>
                    </div>
                </div>
                
                {{-- FPAR Bar --}}
                <div class="relative h-3 bg-zinc-200 rounded-full overflow-hidden">
                    <div class="absolute top-0 left-0 h-full bg-gradient-to-r from-orange-400 via-yellow-400 to-green-500" 
                         style="width: {{ $fparData['fpar'] * 100 }}%"></div>
                </div>
                <div class="flex justify-between text-xs text-zinc-600 mt-1">
                    <span>0 (Baja)</span>
                    <span>1 (Máxima)</span>
                </div>
            </div>
        @endif

        {{-- Info --}}
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-3">
            <div class="flex items-center">
                <span class="text-lg mr-2">🛰️</span>
                <div class="text-xs text-blue-700">
                    <strong>Fuente:</strong> {{ $laiData['source'] }} - Algoritmo calibrado específicamente para vegetación.
                    <strong>+15% más preciso</strong> que cálculos desde NDVI.
                </div>
            </div>
        </div>
    @endif
</div>
