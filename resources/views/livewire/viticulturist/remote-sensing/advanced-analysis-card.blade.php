<div class="bg-white rounded-lg shadow-md p-6">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-xl font-bold text-zinc-900">Análisis Avanzado</h3>
            <p class="text-sm text-zinc-500 mt-1">
                @if($analysis && isset($analysis['data_date']))
                    Datos del {{ \Carbon\Carbon::parse($analysis['data_date'])->format('d/m/Y') }}
                @else
                    Sin datos disponibles
                @endif
            </p>
        </div>
        
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

    {{-- Loading State --}}
    @if($loading && !$analysis)
        <div class="flex items-center justify-center py-12">
            <div class="text-center">
                <svg class="animate-spin h-12 w-12 text-blue-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-zinc-600">Analizando datos...</p>
            </div>
        </div>
    @endif

    {{-- Error State --}}
    @if($error)
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-red-600 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/>
                </svg>
                <div>
                    <h4 class="text-sm font-medium text-red-800">Error</h4>
                    <p class="text-sm text-red-600 mt-1">{{ $error }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Content --}}
    @if($analysis && !isset($analysis['error']))
        {{-- Executive Summary --}}
        @if(isset($analysis['summary']) && count($analysis['summary']) > 0)
            <div class="mb-6">
                <h4 class="text-lg font-semibold text-zinc-800 mb-4">Resumen Ejecutivo</h4>
                <div class="grid grid-cols-2 gap-4">
                    @foreach($analysis['summary'] as $item)
                        <div class="bg-zinc-50 rounded-lg p-4">
                            <div class="flex items-center mb-2">
                                <span class="text-2xl mr-2">{{ $item['icon'] }}</span>
                                <span class="text-xs font-medium text-zinc-500 uppercase">{{ $item['category'] }}</span>
                            </div>
                            <div class="text-sm font-semibold text-zinc-900 mb-1">{{ $item['status'] }}</div>
                            <p class="text-xs text-zinc-600">{{ $item['text'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Priority Actions --}}
        @if(isset($analysis['priority_actions']) && count($analysis['priority_actions']) > 0)
            <div class="mb-6">
                <h4 class="text-lg font-semibold text-zinc-800 mb-4">Acciones Prioritarias</h4>
                <div class="space-y-3">
                    @foreach($analysis['priority_actions'] as $action)
                        <div class="border border-zinc-200 rounded-lg p-4 hover:bg-zinc-50 transition-colors">
                            <div class="flex items-start">
                                <span class="text-2xl mr-3 flex-shrink-0">{{ $action['icon'] }}</span>
                                <div class="flex-1">
                                    <div class="flex items-center mb-1">
                                        <span class="text-sm font-semibold text-zinc-900">{{ $action['title'] }}</span>
                                        <span class="ml-2 px-2 py-0.5 text-xs font-medium rounded bg-{{ $action['priority'] == 1 ? 'red' : ($action['priority'] == 2 ? 'orange' : 'yellow') }}-100 text-{{ $action['priority'] == 1 ? 'red' : ($action['priority'] == 2 ? 'orange' : 'yellow') }}-800">
                                            P{{ $action['priority'] }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-zinc-600 mb-2">{{ $action['description'] }}</p>
                                    <div class="flex items-center text-xs text-blue-600 font-medium">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                        </svg>
                                        {{ $action['action'] }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Detailed Sections (Collapsible) --}}
        <div class="space-y-4">
            {{-- LAI Section --}}
            @if(isset($analysis['lai']))
                <details class="border border-zinc-200 rounded-lg">
                    <summary class="cursor-pointer px-4 py-3 bg-zinc-50 hover:bg-zinc-100 transition-colors font-medium text-zinc-900">
                        🌿 LAI - Predicción de Rendimiento
                    </summary>
                    <div class="p-4 space-y-3">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="text-xs text-zinc-500">LAI Actual</span>
                                <div class="text-2xl font-bold text-zinc-900">{{ number_format($analysis['lai']['value'], 2) }}</div>
                                <span class="text-xs text-{{ $analysis['lai']['classification']['color'] }}-600">{{ $analysis['lai']['classification']['label'] }}</span>
                            </div>
                            <div>
                                <span class="text-xs text-zinc-500">Rendimiento Estimado</span>
                                <div class="text-2xl font-bold text-zinc-900">{{ number_format($analysis['lai']['yield_estimation']['yield_per_ha']) }} <span class="text-sm">kg/ha</span></div>
                                <span class="text-xs text-zinc-600">{{ number_format($analysis['lai']['yield_estimation']['total_yield_tons'], 2) }} toneladas totales</span>
                            </div>
                        </div>
                        
                        @if(isset($analysis['lai']['year_comparison']) && $analysis['lai']['year_comparison'])
                            <div class="bg-blue-50 border border-blue-200 rounded p-3">
                                <div class="text-sm font-medium text-blue-900 mb-1">Comparación Interanual</div>
                                <div class="text-xs text-blue-700">
                                    Cambio: {{ $analysis['lai']['year_comparison']['change'] > 0 ? '+' : '' }}{{ number_format($analysis['lai']['year_comparison']['change'], 2) }} 
                                    ({{ $analysis['lai']['year_comparison']['change_percent'] > 0 ? '+' : '' }}{{ number_format($analysis['lai']['year_comparison']['change_percent'], 1) }}%)
                                    - Tendencia: <strong>{{ ucfirst($analysis['lai']['year_comparison']['trend']) }}</strong>
                                </div>
                            </div>
                        @endif
                    </div>
                </details>
            @endif

            {{-- Chlorophyll/Nitrogen Section --}}
            @if(isset($analysis['chlorophyll']))
                <details class="border border-zinc-200 rounded-lg">
                    <summary class="cursor-pointer px-4 py-3 bg-zinc-50 hover:bg-zinc-100 transition-colors font-medium text-zinc-900">
                        🌱 Estado Nutricional (N)
                    </summary>
                    <div class="p-4 space-y-3">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="text-xs text-zinc-500">Contenido de Clorofila</span>
                                <div class="text-2xl font-bold text-zinc-900">{{ number_format($analysis['chlorophyll']['chlorophyll_percent'], 1) }}%</div>
                                <span class="text-xs text-{{ $analysis['chlorophyll']['diagnosis']['color'] }}-600">{{ $analysis['chlorophyll']['diagnosis']['label'] }}</span>
                            </div>
                            <div>
                                <span class="text-xs text-zinc-500">GNDVI</span>
                                <div class="text-2xl font-bold text-zinc-900">{{ number_format($analysis['chlorophyll']['gndvi'], 3) }}</div>
                            </div>
                        </div>
                        
                        @if($analysis['chlorophyll']['nitrogen_need']['nitrogen_kg_ha'] > 0)
                            <div class="bg-yellow-50 border border-yellow-200 rounded p-3">
                                <div class="text-sm font-medium text-yellow-900 mb-1">Necesidad de Nitrógeno</div>
                                <div class="text-xs text-yellow-700 mb-2">{{ $analysis['chlorophyll']['nitrogen_need']['recommendation'] }}</div>
                                <div class="text-sm font-bold text-yellow-900">
                                    {{ number_format($analysis['chlorophyll']['nitrogen_need']['nitrogen_kg_ha'], 1) }} kg N/ha
                                    ({{ number_format($analysis['chlorophyll']['nitrogen_need']['total_nitrogen_kg'], 1) }} kg total)
                                </div>
                            </div>
                        @endif
                    </div>
                </details>
            @endif

            {{-- Maturity Section --}}
            @if(isset($analysis['maturity']))
                <details class="border border-zinc-200 rounded-lg">
                    <summary class="cursor-pointer px-4 py-3 bg-zinc-50 hover:bg-zinc-100 transition-colors font-medium text-zinc-900">
                        🍇 Maduración y Vendimia
                    </summary>
                    <div class="p-4 space-y-3">
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <span class="text-xs text-zinc-500">Índice de Madurez</span>
                                <div class="text-2xl font-bold text-zinc-900">{{ number_format($analysis['maturity']['maturity_index'], 0) }}%</div>
                                <span class="text-xs text-{{ $analysis['maturity']['classification']['color'] }}-600">{{ $analysis['maturity']['classification']['label'] }}</span>
                            </div>
                            <div>
                                <span class="text-xs text-zinc-500">°Brix Estimado</span>
                                <div class="text-2xl font-bold text-zinc-900">{{ number_format($analysis['maturity']['predicted_brix']['value'], 1) }}</div>
                                <span class="text-xs text-zinc-600">±{{ number_format($analysis['maturity']['predicted_brix']['max'] - $analysis['maturity']['predicted_brix']['value'], 1) }}</span>
                            </div>
                            <div>
                                <span class="text-xs text-zinc-500">Días para Vendimia</span>
                                <div class="text-2xl font-bold text-zinc-900">{{ $analysis['maturity']['estimated_days_to_harvest'] ?? 'N/A' }}</div>
                                @if(isset($analysis['maturity']['optimal_harvest_date']))
                                    <span class="text-xs text-zinc-600">{{ \Carbon\Carbon::parse($analysis['maturity']['optimal_harvest_date'])->format('d/m/Y') }}</span>
                                @endif
                            </div>
                        </div>

                        @if(isset($analysis['maturity']['quality_potential']))
                            <div class="bg-purple-50 border border-purple-200 rounded p-3">
                                <div class="text-sm font-medium text-purple-900 mb-1">Potencial de Calidad</div>
                                <div class="flex items-center">
                                    <div class="flex-1">
                                        <div class="text-xs text-purple-700">{{ $analysis['maturity']['quality_potential']['label'] }}</div>
                                    </div>
                                    <div class="text-2xl font-bold text-purple-900">{{ number_format($analysis['maturity']['quality_potential']['quality_score'], 0) }}/100</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </details>
            @endif

            {{-- Anomalies Section --}}
            @if(isset($analysis['anomalies']) && $analysis['anomalies']['has_anomalies'])
                <details class="border border-red-300 rounded-lg bg-red-50" open>
                    <summary class="cursor-pointer px-4 py-3 bg-red-100 hover:bg-red-200 transition-colors font-medium text-red-900">
                        🚨 Anomalías Detectadas ({{ $analysis['anomalies']['count'] }})
                    </summary>
                    <div class="p-4 space-y-3">
                        @foreach($analysis['anomalies']['anomalies'] as $anomaly)
                            <div class="bg-white border border-{{ $anomaly['color'] }}-300 rounded p-3">
                                <div class="flex items-start mb-2">
                                    <span class="text-2xl mr-2">{{ $anomaly['icon'] }}</span>
                                    <div class="flex-1">
                                        <div class="flex items-center mb-1">
                                            <span class="text-sm font-semibold text-zinc-900">{{ $anomaly['title'] }}</span>
                                            <span class="ml-2 px-2 py-0.5 text-xs font-medium rounded bg-{{ $anomaly['color'] }}-100 text-{{ $anomaly['color'] }}-800">
                                                {{ strtoupper($anomaly['severity']) }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-zinc-700 mb-2">{{ $anomaly['description'] }}</p>
                                    </div>
                                </div>
                                
                                @if(isset($anomaly['recommended_actions']))
                                    <div class="mt-2 pl-9">
                                        <div class="text-xs font-medium text-zinc-700 mb-1">Acciones recomendadas:</div>
                                        <ul class="text-xs text-zinc-600 space-y-1">
                                            @foreach($anomaly['recommended_actions'] as $action)
                                                <li>• {{ $action }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </details>
            @endif
        </div>
    @elseif(isset($analysis['error']))
        <div class="text-center py-8 text-zinc-500">
            <svg class="w-16 h-16 mx-auto mb-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <p>{{ $analysis['error'] }}</p>
        </div>
    @endif
</div>
