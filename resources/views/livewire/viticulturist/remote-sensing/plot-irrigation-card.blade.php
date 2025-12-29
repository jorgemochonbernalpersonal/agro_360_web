<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
            💧 Recomendación de Riego
        </h3>
        <button wire:click="refresh" class="text-gray-400 hover:text-gray-600 transition-colors" title="Actualizar">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
        </button>
    </div>

    @if(!empty($recommendation))
        {{-- Main Recommendation --}}
        <div class="rounded-xl p-6 mb-4 
            @if($recommendation['level_color'] === 'red') bg-red-50 border-2 border-red-200
            @elseif($recommendation['level_color'] === 'orange') bg-orange-50 border-2 border-orange-200
            @elseif($recommendation['level_color'] === 'yellow') bg-yellow-50 border-2 border-yellow-200
            @elseif($recommendation['level_color'] === 'blue') bg-blue-50 border-2 border-blue-200
            @elseif($recommendation['level_color'] === 'green') bg-green-50 border-2 border-green-200
            @else bg-gray-50 border-2 border-gray-200
            @endif
        ">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-3xl mb-2">{{ $recommendation['level_icon'] }}</div>
                    <div class="text-xl font-bold 
                        @if($recommendation['level_color'] === 'red') text-red-700
                        @elseif($recommendation['level_color'] === 'orange') text-orange-700
                        @elseif($recommendation['level_color'] === 'yellow') text-yellow-700
                        @elseif($recommendation['level_color'] === 'blue') text-blue-700
                        @elseif($recommendation['level_color'] === 'green') text-green-700
                        @else text-gray-700
                        @endif
                    ">
                        {{ $recommendation['level_text'] }}
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-500">Cantidad estimada</div>
                    <div class="text-2xl font-bold text-gray-900">
                        {{ $recommendation['water_amount_text'] }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Stress Factors --}}
        @if(count($recommendation['stress_factors']) > 0)
            <div class="mb-4">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Factores de estrés detectados:</h4>
                <ul class="space-y-1">
                    @foreach($recommendation['stress_factors'] as $factor)
                        <li class="flex items-center gap-2 text-sm text-gray-600">
                            <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            {{ $factor }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @else
            <div class="bg-green-50 rounded-lg p-3 mb-4">
                <p class="text-sm text-green-700 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    No se detectan signos de estrés hídrico
                </p>
            </div>
        @endif

        {{-- Data Grid --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
            <div class="bg-gray-50 rounded-lg p-3 text-center">
                <div class="text-gray-500 text-xs">NDVI</div>
                <div class="font-semibold {{ $recommendation['ndvi'] !== null && $recommendation['ndvi'] < 0.4 ? 'text-amber-600' : 'text-gray-900' }}">
                    {{ $recommendation['ndvi'] !== null ? number_format($recommendation['ndvi'], 3) : 'N/A' }}
                </div>
            </div>
            <div class="bg-gray-50 rounded-lg p-3 text-center">
                <div class="text-gray-500 text-xs">NDWI</div>
                <div class="font-semibold {{ $recommendation['ndwi'] !== null && $recommendation['ndwi'] < -0.1 ? 'text-amber-600' : 'text-gray-900' }}">
                    {{ $recommendation['ndwi'] !== null ? number_format($recommendation['ndwi'], 3) : 'N/A' }}
                </div>
            </div>
            <div class="bg-gray-50 rounded-lg p-3 text-center">
                <div class="text-gray-500 text-xs">Humedad Suelo</div>
                <div class="font-semibold {{ $recommendation['soil_moisture'] !== null && $recommendation['soil_moisture'] < 20 ? 'text-amber-600' : 'text-gray-900' }}">
                    {{ $recommendation['soil_moisture'] !== null ? number_format($recommendation['soil_moisture'], 0) . '%' : 'N/A' }}
                </div>
            </div>
            <div class="bg-gray-50 rounded-lg p-3 text-center">
                <div class="text-gray-500 text-xs">ET₀</div>
                <div class="font-semibold {{ $recommendation['et0'] !== null && $recommendation['et0'] > 6 ? 'text-amber-600' : 'text-gray-900' }}">
                    {{ $recommendation['et0'] !== null ? number_format($recommendation['et0'], 1) . ' mm' : 'N/A' }}
                </div>
            </div>
        </div>

        @if($recommendation['last_updated'])
            <div class="mt-4 text-xs text-gray-400 text-center">
                Datos del {{ $recommendation['last_updated'] }}
            </div>
        @endif
    @else
        <div class="text-center py-8 text-gray-500">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <p>Sin datos de teledetección</p>
        </div>
    @endif
</div>
